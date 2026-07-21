<?php

namespace App\Services\Translation;

use App\Helpers\ResponseCode;
use App\Jobs\BulkTranslateQuestionsJob;
use App\Models\QuestionTranslation;
use App\Responses\TranslationResponse;
use App\Services\AzureTranslation\AzureTranslatorService;

class TranslationActionService
{
    public function __construct(private TranslationResponse $response) {}

    public function retranslateField(QuestionTranslation $translation, string $field, AzureTranslatorService $translator): TranslationResponse
    {
        $translation->loadMissing('question', 'language');
        $question = $translation->question;
        $language = $translation->language;

        if (! $question || ! $language) {
            $this->response->setResponse(ResponseCode::ERROR, 404, 'Related question or language not found.');

            return $this->response;
        }

        $sourceFields = $translator->resolveSourceFields($question, $language);
        $sourceText = $sourceFields[$field] ?? null;

        if (empty($sourceText)) {
            $this->response->setResponse(ResponseCode::ERROR, 422, 'Source text for this field is empty, nothing to translate.');

            return $this->response;
        }

        $translated = $translator->translateOne($sourceText, $language->code);

        if ($translated === false) {
            $this->response->setResponse(ResponseCode::ERROR, 500, 'Azure Translate request failed. Check logs for details.');

            return $this->response;
        }

        $translation->update(["{$field}_translation" => $translated]);
        $status = $this->resolveStatus($sourceFields, $translation);
        $translation->status = $status;
        $translation->error = $status === 'completed' ? null : $translation->error;
        $translation->save();

        $this->response->setResponse(ResponseCode::SUCCESS, 200, 'Field translated successfully', ['field' => $field, 'translation' => $translated]);

        return $this->response;
    }

    /**
     * A field only counts toward status if the question actually has source
     * content for it (empty c/d never block "completed") — mirrors the bulk
     * job's $expected logic so both code paths agree on what "done" means.
     */
    private function resolveStatus(array $sourceFields, QuestionTranslation $translation): string
    {
        foreach (BulkTranslateQuestionsJob::FIELDS as $key) {
            if (empty($sourceFields[$key] ?? null)) {
                continue;
            }

            if (empty($translation->{"{$key}_translation"})) {
                return 'partial';
            }
        }

        return 'completed';
    }
}
