<?php

namespace App\Services\CpcTranslation;

use App\Helpers\ResponseCode;
use App\Models\CpcCaseStudy;
use App\Models\CpcCaseStudyBlock;
use App\Models\CpcCaseStudyBlockTranslation;
use App\Models\CpcCaseStudyTranslation;
use App\Models\Language;
use App\Models\Setting;
use App\Responses\CpcCaseStudyResponse;
use App\Services\AzureTextToSpeech\AzureTTSService;
use App\Services\AzureTranslation\AzureTranslatorService;
use Illuminate\Support\Facades\File;

class CpcCaseStudyTranslationService
{
    public function __construct(private CpcCaseStudyResponse $response) {}

    public function translate(CpcCaseStudy $caseStudy, Language $language, AzureTranslatorService $translator): CpcCaseStudyResponse
    {
        if (empty(Setting::translationApiKey())) {
            $this->response->setResponse(ResponseCode::ERROR, 500, 'Azure Translator API key is not configured. Set it in Admin > App Settings > API Settings.');

            return $this->response;
        }

        $caseStudy->loadMissing('blocks');

        $translation = CpcCaseStudyTranslation::firstOrCreate([
            'cpc_case_study_id' => $caseStudy->id,
            'language_id' => $language->id,
        ]);

        $allTranslated = true;
        $lastError = null;

        if (blank($translation->title_translation)) {
            try {
                $translatedTitle = $translator->translateOne($caseStudy->title, $language->code);
                $translation->update(['title_translation' => $translatedTitle]);
            } catch (\Throwable $e) {
                $allTranslated = false;
                $lastError = $e->getMessage();
            }
        }

        foreach ($caseStudy->blocks as $block) {
            $blockTranslation = CpcCaseStudyBlockTranslation::firstOrCreate([
                'cpc_case_study_block_id' => $block->id,
                'language_id' => $language->id,
            ]);

            if ($block->type === 'text' && filled($block->content)) {
                if (filled($blockTranslation->content_translation)) {
                    continue;
                }

                try {
                    $translated = $translator->translateOne($this->plainText($block->content), $language->code);
                    $blockTranslation->update(['content_translation' => $translated]);
                } catch (\Throwable $e) {
                    $allTranslated = false;
                    $lastError = $e->getMessage();
                }
            } elseif ($block->type === 'list' && filled($block->items)) {
                if (filled($blockTranslation->items_translation)) {
                    continue;
                }

                $fields = [];
                foreach ($block->items as $itemIndex => $item) {
                    $fields["item_{$itemIndex}"] = $item;
                }

                try {
                    $result = $translator->translateBatch($fields, $language->code);
                    $items = [];
                    foreach ($block->items as $itemIndex => $item) {
                        $items[] = $result["item_{$itemIndex}"] ?? $item;
                    }
                    $blockTranslation->update(['items_translation' => $items]);
                } catch (\Throwable $e) {
                    $allTranslated = false;
                    $lastError = $e->getMessage();
                }
            }
        }

        $translation->update(['status' => $allTranslated ? 'completed' : 'partial']);

        $this->response->setResponse(
            ResponseCode::SUCCESS,
            200,
            $allTranslated ? 'Case study translated successfully.' : "Case study partially translated: {$lastError}"
        );

        return $this->response;
    }

    public function translateTitle(CpcCaseStudy $caseStudy, Language $language, AzureTranslatorService $translator): CpcCaseStudyResponse
    {
        if (empty(Setting::translationApiKey())) {
            $this->response->setResponse(ResponseCode::ERROR, 500, 'Azure Translator API key is not configured. Set it in Admin > App Settings > API Settings.');

            return $this->response;
        }

        $translation = CpcCaseStudyTranslation::firstOrCreate([
            'cpc_case_study_id' => $caseStudy->id,
            'language_id' => $language->id,
        ]);

        try {
            $translatedTitle = $translator->translateOne($caseStudy->title, $language->code);
            $translation->update(['title_translation' => $translatedTitle]);

            $this->response->setResponse(ResponseCode::SUCCESS, 200, 'Title translated successfully.', ['translation' => $translatedTitle]);
        } catch (\Throwable $e) {
            $this->response->setResponse(ResponseCode::ERROR, 500, $e->getMessage());
        }

        return $this->response;
    }

    public function translateBlock(CpcCaseStudyBlock $block, Language $language, AzureTranslatorService $translator): CpcCaseStudyResponse
    {
        if (empty(Setting::translationApiKey())) {
            $this->response->setResponse(ResponseCode::ERROR, 500, 'Azure Translator API key is not configured. Set it in Admin > App Settings > API Settings.');

            return $this->response;
        }

        if (! in_array($block->type, ['text', 'list'], true)) {
            $this->response->setResponse(ResponseCode::ERROR, 422, 'This block type cannot be translated.');

            return $this->response;
        }

        $blockTranslation = CpcCaseStudyBlockTranslation::firstOrCreate([
            'cpc_case_study_block_id' => $block->id,
            'language_id' => $language->id,
        ]);

        try {
            if ($block->type === 'text') {
                $translated = $translator->translateOne($this->plainText($block->content ?? ''), $language->code);
                $blockTranslation->update(['content_translation' => $translated]);
                $displayTranslation = $translated;
            } else {
                $fields = [];
                foreach ($block->items ?? [] as $itemIndex => $item) {
                    $fields["item_{$itemIndex}"] = $item;
                }

                $result = $translator->translateBatch($fields, $language->code);
                $items = [];
                foreach ($block->items ?? [] as $itemIndex => $item) {
                    $items[] = $result["item_{$itemIndex}"] ?? $item;
                }
                $blockTranslation->update(['items_translation' => $items]);
                $displayTranslation = implode("\n", $items);
            }

            $this->response->setResponse(ResponseCode::SUCCESS, 200, 'Block translated successfully.', ['translation' => $displayTranslation]);
        } catch (\Throwable $e) {
            $this->response->setResponse(ResponseCode::ERROR, 500, $e->getMessage());
        }

        return $this->response;
    }

    public function generateAudioForBlock(CpcCaseStudyBlock $block, Language $language, AzureTTSService $tts): CpcCaseStudyResponse
    {
        if (! in_array($block->type, ['text', 'list'], true)) {
            $this->response->setResponse(ResponseCode::ERROR, 422, 'This block type has no audio.');

            return $this->response;
        }

        $language->loadMissing('voices');

        $blockTranslation = CpcCaseStudyBlockTranslation::firstOrCreate([
            'cpc_case_study_block_id' => $block->id,
            'language_id' => $language->id,
        ]);

        $text = $block->type === 'text'
            ? ($blockTranslation->content_translation ?: $this->plainText($block->content ?? ''))
            : implode('. ', $blockTranslation->items_translation ?: ($block->items ?? []));

        if (blank($text)) {
            $this->response->setResponse(ResponseCode::ERROR, 422, 'Nothing to convert. Translate this block first.');

            return $this->response;
        }

        $audioContent = $tts->convertToSpeech($text, $language);

        if (is_array($audioContent)) {
            $this->response->setResponse(ResponseCode::ERROR, 500, 'Failed to generate audio.');

            return $this->response;
        }

        $directory = public_path('audios/cpc-case-studies');
        File::ensureDirectoryExists($directory);

        if ($blockTranslation->content_audio) {
            $oldPath = "{$directory}/{$blockTranslation->content_audio}";
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $fileName = "case_{$block->cpc_case_study_id}_block_{$block->id}_{$language->code}_".time().'.mp3';
        file_put_contents("{$directory}/{$fileName}", $audioContent);

        $blockTranslation->update(['content_audio' => $fileName]);

        $this->response->setResponse(ResponseCode::SUCCESS, 200, 'Audio generated successfully.', [
            'audio' => $fileName,
            'audio_url' => asset("audios/cpc-case-studies/{$fileName}"),
        ]);

        return $this->response;
    }

    public function generateAudio(CpcCaseStudy $caseStudy, Language $language, AzureTTSService $tts): CpcCaseStudyResponse
    {
        $language->loadMissing('voices');
        $caseStudy->loadMissing('blocks');

        $directory = public_path('audios/cpc-case-studies');
        File::ensureDirectoryExists($directory);

        $allGenerated = true;
        $hasTarget = false;

        foreach ($caseStudy->blocks as $block) {
            if (! in_array($block->type, ['text', 'list'], true)) {
                continue;
            }

            $blockTranslation = CpcCaseStudyBlockTranslation::firstOrCreate([
                'cpc_case_study_block_id' => $block->id,
                'language_id' => $language->id,
            ]);

            $text = $block->type === 'text'
                ? ($blockTranslation->content_translation ?: $this->plainText($block->content))
                : implode('. ', $blockTranslation->items_translation ?: ($block->items ?? []));

            if (blank($text)) {
                continue;
            }

            $hasTarget = true;

            if ($this->hasAudioFile($directory, $blockTranslation->content_audio)) {
                continue;
            }

            $audioContent = $tts->convertToSpeech($text, $language);

            if (is_array($audioContent)) {
                $allGenerated = false;

                continue;
            }

            $fileName = "case_{$caseStudy->id}_block_{$block->id}_{$language->code}_".time().'.mp3';
            file_put_contents("{$directory}/{$fileName}", $audioContent);

            $blockTranslation->update(['content_audio' => $fileName]);
        }

        if (! $hasTarget) {
            $this->response->setResponse(ResponseCode::ERROR, 422, 'Nothing to convert. Translate the case study first.');

            return $this->response;
        }

        $this->response->setResponse(ResponseCode::SUCCESS, 200, $allGenerated ? 'Audio generated successfully.' : 'Audio partially generated. Check logs for details.');

        return $this->response;
    }

    private function hasAudioFile(string $directory, ?string $fileName): bool
    {
        return filled($fileName) && is_file("{$directory}/{$fileName}");
    }

    /**
     * Text blocks are authored as rich HTML (headings, bold, lists) via Summernote.
     * Azure Translator and the TTS engine both need plain, spoken-friendly text.
     */
    private function plainText(string $html): string
    {
        $text = strip_tags(str_replace(['</p>', '</h1>', '</h2>', '</h3>', '</h4>', '</h5>', '</h6>', '</li>', '<br>', '<br/>', '<br />'], "\n", $html));

        return trim(preg_replace('/\n{2,}/', "\n", html_entity_decode($text, ENT_QUOTES)));
    }
}
