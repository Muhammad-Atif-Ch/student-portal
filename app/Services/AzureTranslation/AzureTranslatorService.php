<?php

namespace App\Services\AzureTranslation;

use App\Jobs\BulkTranslateQuestionsJob;
use App\Models\Language;
use App\Models\Question;
use App\Models\QuestionTranslation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AzureTranslatorService
{
    /** This language always uses the original (untranslated) source fields. */
    public const ORIGINAL_ONLY_LANGUAGE_ID = 39;

    /**
     * Work out what text should be translated for each field, given a
     * question and the target language. Kept in one place so the bulk job
     * and the single-field "re-translate" action always agree on source of
     * truth.
     */
    public function resolveSourceFields(Question $question, Language $language): array
    {
        if ($language->id === self::ORIGINAL_ONLY_LANGUAGE_ID) {
            return [
                'question' => $question->question,
                'a' => $question->a,
                'b' => $question->b,
                'c' => $question->c,
                'd' => $question->d,
                'answer_explanation' => $question->answer_explanation,
            ];
        }

        return [
            'question' => $question->question_translation ?: $question->question,
            'a' => $question->a_translation ?: $question->a,
            'b' => $question->b_translation ?: $question->b,
            'c' => $question->c_translation ?: $question->c,
            'd' => $question->d_translation ?: $question->d,
            'answer_explanation' => $question->answer_explanation_translation ?: $question->answer_explanation,
        ];
    }

    /**
     * Translate one or more fields in a single Azure Translator API call.
     *
     * @param  array<string,string>  $fields  key => source text (e.g. ['a' => 'Yes', 'b' => 'No'])
     * @return array<string,string>|false key => translated text, or false on failure
     */
    public function translateBatch(array $fields, string $targetLanguage): array|false
    {
        if (empty($fields)) {
            return [];
        }

        $apiKey = config('services.azure_translator.key');

        if (empty($apiKey)) {
            Log::info('Azure Translate API key is not configured.', ['api_key' => $apiKey]);

            return false;
        }

        $keys = array_keys($fields);
        $texts = array_values($fields);

        $endpoint = rtrim(config('services.azure_translator.endpoint'), '/');
        $region = config('services.azure_translator.region');

        // Ocp-Apim-Subscription-Region is only required for regional /
        // multi-service resources - omitted entirely for a global resource.
        $headers = array_filter([
            'Ocp-Apim-Subscription-Key' => $apiKey,
            'Ocp-Apim-Subscription-Region' => $region ?: null,
            'Content-Type' => 'application/json',
        ]);

        try {
            $response = Http::timeout(15)
                ->withHeaders($headers)
                ->withQueryParameters([
                    'api-version' => '3.0',
                    'to' => $targetLanguage,
                ])
                ->post("{$endpoint}/translate", array_map(
                    fn ($text) => ['Text' => $text],
                    $texts
                ));

            if (! $response->successful()) {
                Log::error('Azure Translate API error: '.$response->body(), ['api_key' => $apiKey]);

                return false;
            }

            $results = $response->json();

            if (! is_array($results) || count($results) !== count($texts)) {
                Log::error('Azure Translate API returned an unexpected number of translations.', ['api_key' => $apiKey]);

                return false;
            }

            $translated = [];
            foreach ($keys as $index => $key) {
                $translated[$key] = $results[$index]['translations'][0]['text'] ?? $texts[$index];
            }

            Log::info('Azure Translate API returned translations.', ['result' => json_encode($results)]);

            return $translated;
        } catch (\Throwable $e) {
            Log::error('Translation API request failed: '.$e->getMessage(), ['api_key' => $apiKey]);

            return false;
        }
    }

    /**
     * Convenience wrapper for translating a single field.
     *
     * @return string|false
     */
    public function translateOne(string $text, string $targetLanguage)
    {
        $result = $this->translateBatch(['value' => $text], $targetLanguage);

        return $result === false ? false : $result['value'];
    }

    public function resolveStatus(Question $question, Language $language, QuestionTranslation $translation, bool $requireAudio = false): string
    {
        $sourceFields = $this->resolveSourceFields($question, $language);

        foreach (BulkTranslateQuestionsJob::FIELDS as $key) {
            if (empty($sourceFields[$key] ?? null)) {
                continue; // question has nothing for this field — not expected
            }

            if (empty($translation->{"{$key}_translation"})) {
                return 'partial';
            }

            if ($requireAudio && empty($translation->{"{$key}_audio"})) {
                return 'partial';
            }
        }

        return 'completed';
    }
}
