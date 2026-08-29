<?php

namespace App\Services\CpcTranslation;

use App\Helpers\ResponseCode;
use App\Models\CpcQuestion;
use App\Models\CpcQuestionOption;
use App\Models\CpcQuestionOptionTranslation;
use App\Models\CpcQuestionTranslation;
use App\Models\Language;
use App\Models\Setting;
use App\Responses\CpcQuestionResponse;
use App\Services\AzureTextToSpeech\AzureTTSService;
use App\Services\AzureTranslation\AzureTranslatorService;
use Illuminate\Support\Facades\File;

class CpcQuestionTranslationService
{
    public function __construct(private CpcQuestionResponse $response) {}

    public function translate(CpcQuestion $question, Language $language, AzureTranslatorService $translator): CpcQuestionResponse
    {
        if (empty(Setting::translationApiKey())) {
            $this->response->setResponse(ResponseCode::ERROR, 500, 'Azure Translator API key is not configured. Set it in Admin > App Settings > API Settings.');

            return $this->response;
        }

        $question->loadMissing('options');

        $translation = CpcQuestionTranslation::firstOrCreate([
            'cpc_question_id' => $question->id,
            'language_id' => $language->id,
        ]);

        $allTranslated = true;
        $lastError = null;

        if (blank($translation->question_translation)) {
            try {
                $translatedQuestion = $translator->translateOne($question->question, $language->code);
                $translation->update(['question_translation' => $translatedQuestion]);
            } catch (\Throwable $e) {
                $allTranslated = false;
                $lastError = $e->getMessage();
            }
        }

        if (filled($question->answer_explanation) && blank($translation->answer_explanation_translation)) {
            try {
                $translatedExplanation = $translator->translateOne($question->answer_explanation, $language->code);
                $translation->update(['answer_explanation_translation' => $translatedExplanation]);
            } catch (\Throwable $e) {
                $allTranslated = false;
                $lastError = $e->getMessage();
            }
        }

        foreach ($question->options as $option) {
            if ($option->type !== 'text' || blank($option->text_value)) {
                continue;
            }

            $optionTranslation = CpcQuestionOptionTranslation::firstOrCreate([
                'cpc_question_option_id' => $option->id,
                'language_id' => $language->id,
            ]);

            if (filled($optionTranslation->text_value_translation)) {
                continue;
            }

            try {
                $translatedOption = $translator->translateOne($option->text_value, $language->code);
                $optionTranslation->update(['text_value_translation' => $translatedOption]);
            } catch (\Throwable $e) {
                $allTranslated = false;
                $lastError = $e->getMessage();
            }
        }

        $translation->update(['status' => $allTranslated ? 'completed' : 'partial']);

        $this->response->setResponse(
            ResponseCode::SUCCESS,
            200,
            $allTranslated ? 'Question translated successfully.' : "Question partially translated: {$lastError}"
        );

        return $this->response;
    }

    public function translateQuestionText(CpcQuestion $question, Language $language, AzureTranslatorService $translator): CpcQuestionResponse
    {
        if (empty(Setting::translationApiKey())) {
            $this->response->setResponse(ResponseCode::ERROR, 500, 'Azure Translator API key is not configured. Set it in Admin > App Settings > API Settings.');

            return $this->response;
        }

        $translation = CpcQuestionTranslation::firstOrCreate([
            'cpc_question_id' => $question->id,
            'language_id' => $language->id,
        ]);

        try {
            $translated = $translator->translateOne($question->question, $language->code);
            $translation->update(['question_translation' => $translated]);

            $this->response->setResponse(ResponseCode::SUCCESS, 200, 'Question translated successfully.', ['translation' => $translated]);
        } catch (\Throwable $e) {
            $this->response->setResponse(ResponseCode::ERROR, 500, $e->getMessage());
        }

        return $this->response;
    }

    public function translateExplanation(CpcQuestion $question, Language $language, AzureTranslatorService $translator): CpcQuestionResponse
    {
        if (empty(Setting::translationApiKey())) {
            $this->response->setResponse(ResponseCode::ERROR, 500, 'Azure Translator API key is not configured. Set it in Admin > App Settings > API Settings.');

            return $this->response;
        }

        if (blank($question->answer_explanation)) {
            $this->response->setResponse(ResponseCode::ERROR, 422, 'This question has no answer explanation to translate.');

            return $this->response;
        }

        $translation = CpcQuestionTranslation::firstOrCreate([
            'cpc_question_id' => $question->id,
            'language_id' => $language->id,
        ]);

        try {
            $translated = $translator->translateOne($question->answer_explanation, $language->code);
            $translation->update(['answer_explanation_translation' => $translated]);

            $this->response->setResponse(ResponseCode::SUCCESS, 200, 'Explanation translated successfully.', ['translation' => $translated]);
        } catch (\Throwable $e) {
            $this->response->setResponse(ResponseCode::ERROR, 500, $e->getMessage());
        }

        return $this->response;
    }

    public function translateOption(CpcQuestionOption $option, Language $language, AzureTranslatorService $translator): CpcQuestionResponse
    {
        if (empty(Setting::translationApiKey())) {
            $this->response->setResponse(ResponseCode::ERROR, 500, 'Azure Translator API key is not configured. Set it in Admin > App Settings > API Settings.');

            return $this->response;
        }

        if ($option->type !== 'text' || blank($option->text_value)) {
            $this->response->setResponse(ResponseCode::ERROR, 422, 'This option cannot be translated.');

            return $this->response;
        }

        $optionTranslation = CpcQuestionOptionTranslation::firstOrCreate([
            'cpc_question_option_id' => $option->id,
            'language_id' => $language->id,
        ]);

        try {
            $translated = $translator->translateOne($option->text_value, $language->code);
            $optionTranslation->update(['text_value_translation' => $translated]);

            $this->response->setResponse(ResponseCode::SUCCESS, 200, 'Option translated successfully.', ['translation' => $translated]);
        } catch (\Throwable $e) {
            $this->response->setResponse(ResponseCode::ERROR, 500, $e->getMessage());
        }

        return $this->response;
    }

    public function generateAudioForQuestionText(CpcQuestion $question, Language $language, AzureTTSService $tts): CpcQuestionResponse
    {
        $language->loadMissing('voices');

        $translation = CpcQuestionTranslation::firstOrCreate([
            'cpc_question_id' => $question->id,
            'language_id' => $language->id,
        ]);

        $text = $translation->question_translation ?: $question->question;

        if (blank($text)) {
            $this->response->setResponse(ResponseCode::ERROR, 422, 'Nothing to convert. Translate this question first.');

            return $this->response;
        }

        $audioContent = $tts->convertToSpeech($text, $language);

        if (is_array($audioContent)) {
            $this->response->setResponse(ResponseCode::ERROR, 500, 'Failed to generate audio.');

            return $this->response;
        }

        $directory = public_path('audios/cpc-questions');
        File::ensureDirectoryExists($directory);

        if ($translation->question_audio) {
            $oldPath = "{$directory}/{$translation->question_audio}";
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $fileName = "question_{$question->id}_{$language->code}_".time().'.mp3';
        file_put_contents("{$directory}/{$fileName}", $audioContent);

        $translation->update(['question_audio' => $fileName]);

        $this->response->setResponse(ResponseCode::SUCCESS, 200, 'Audio generated successfully.', [
            'audio' => $fileName,
            'audio_url' => asset("audios/cpc-questions/{$fileName}"),
        ]);

        return $this->response;
    }

    public function generateAudioForExplanation(CpcQuestion $question, Language $language, AzureTTSService $tts): CpcQuestionResponse
    {
        $language->loadMissing('voices');

        $translation = CpcQuestionTranslation::firstOrCreate([
            'cpc_question_id' => $question->id,
            'language_id' => $language->id,
        ]);

        $text = $translation->answer_explanation_translation ?: $question->answer_explanation;

        if (blank($text)) {
            $this->response->setResponse(ResponseCode::ERROR, 422, 'Nothing to convert. Translate this explanation first.');

            return $this->response;
        }

        $audioContent = $tts->convertToSpeech($text, $language);

        if (is_array($audioContent)) {
            $this->response->setResponse(ResponseCode::ERROR, 500, 'Failed to generate audio.');

            return $this->response;
        }

        $directory = public_path('audios/cpc-questions');
        File::ensureDirectoryExists($directory);

        if ($translation->answer_explanation_audio) {
            $oldPath = "{$directory}/{$translation->answer_explanation_audio}";
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $fileName = "explanation_{$question->id}_{$language->code}_".time().'.mp3';
        file_put_contents("{$directory}/{$fileName}", $audioContent);

        $translation->update(['answer_explanation_audio' => $fileName]);

        $this->response->setResponse(ResponseCode::SUCCESS, 200, 'Audio generated successfully.', [
            'audio' => $fileName,
            'audio_url' => asset("audios/cpc-questions/{$fileName}"),
        ]);

        return $this->response;
    }

    public function generateAudioForOption(CpcQuestionOption $option, Language $language, AzureTTSService $tts): CpcQuestionResponse
    {
        if ($option->type !== 'text') {
            $this->response->setResponse(ResponseCode::ERROR, 422, 'This option has no audio.');

            return $this->response;
        }

        $language->loadMissing('voices');

        $optionTranslation = CpcQuestionOptionTranslation::firstOrCreate([
            'cpc_question_option_id' => $option->id,
            'language_id' => $language->id,
        ]);

        $text = $optionTranslation->text_value_translation ?: $option->text_value;

        if (blank($text)) {
            $this->response->setResponse(ResponseCode::ERROR, 422, 'Nothing to convert. Translate this option first.');

            return $this->response;
        }

        $audioContent = $tts->convertToSpeech($text, $language);

        if (is_array($audioContent)) {
            $this->response->setResponse(ResponseCode::ERROR, 500, 'Failed to generate audio.');

            return $this->response;
        }

        $directory = public_path('audios/cpc-questions');
        File::ensureDirectoryExists($directory);

        if ($optionTranslation->option_audio) {
            $oldPath = "{$directory}/{$optionTranslation->option_audio}";
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $fileName = "option_{$option->id}_{$language->code}_".time().'.mp3';
        file_put_contents("{$directory}/{$fileName}", $audioContent);

        $optionTranslation->update(['option_audio' => $fileName]);

        $this->response->setResponse(ResponseCode::SUCCESS, 200, 'Audio generated successfully.', [
            'audio' => $fileName,
            'audio_url' => asset("audios/cpc-questions/{$fileName}"),
        ]);

        return $this->response;
    }

    public function generateAudio(CpcQuestion $question, Language $language, AzureTTSService $tts): CpcQuestionResponse
    {
        $language->loadMissing('voices');
        $question->loadMissing('options');

        $directory = public_path('audios/cpc-questions');
        File::ensureDirectoryExists($directory);

        $allGenerated = true;
        $hasTarget = false;

        $translation = CpcQuestionTranslation::firstOrCreate([
            'cpc_question_id' => $question->id,
            'language_id' => $language->id,
        ]);

        $questionText = $translation->question_translation ?: $question->question;

        if (filled($questionText)) {
            $hasTarget = true;

            if (! $this->hasAudioFile($directory, $translation->question_audio)) {
                $audioContent = $tts->convertToSpeech($questionText, $language);

                if (! is_array($audioContent)) {
                    $fileName = "question_{$question->id}_{$language->code}_".time().'.mp3';
                    file_put_contents("{$directory}/{$fileName}", $audioContent);

                    $translation->update(['question_audio' => $fileName]);
                } else {
                    $allGenerated = false;
                }
            }
        }

        $explanationText = $translation->answer_explanation_translation ?: $question->answer_explanation;

        if (filled($explanationText)) {
            $hasTarget = true;

            if (! $this->hasAudioFile($directory, $translation->answer_explanation_audio)) {
                $audioContent = $tts->convertToSpeech($explanationText, $language);

                if (! is_array($audioContent)) {
                    $fileName = "explanation_{$question->id}_{$language->code}_".time().'.mp3';
                    file_put_contents("{$directory}/{$fileName}", $audioContent);

                    $translation->update(['answer_explanation_audio' => $fileName]);
                } else {
                    $allGenerated = false;
                }
            }
        }

        foreach ($question->options as $option) {
            if ($option->type !== 'text' || blank($option->text_value)) {
                continue;
            }

            $optionTranslation = CpcQuestionOptionTranslation::firstOrCreate([
                'cpc_question_option_id' => $option->id,
                'language_id' => $language->id,
            ]);

            $optionText = $optionTranslation->text_value_translation ?: $option->text_value;

            if (blank($optionText)) {
                continue;
            }

            $hasTarget = true;

            if ($this->hasAudioFile($directory, $optionTranslation->option_audio)) {
                continue;
            }

            $audioContent = $tts->convertToSpeech($optionText, $language);

            if (is_array($audioContent)) {
                $allGenerated = false;

                continue;
            }

            $fileName = "option_{$option->id}_{$language->code}_".time().'.mp3';
            file_put_contents("{$directory}/{$fileName}", $audioContent);

            $optionTranslation->update(['option_audio' => $fileName]);
        }

        if (! $hasTarget) {
            $this->response->setResponse(ResponseCode::ERROR, 422, 'Nothing to convert. Translate the question first.');

            return $this->response;
        }

        $this->response->setResponse(ResponseCode::SUCCESS, 200, $allGenerated ? 'Audio generated successfully.' : 'Audio partially generated. Check logs for details.');

        return $this->response;
    }

    private function hasAudioFile(string $directory, ?string $fileName): bool
    {
        return filled($fileName) && is_file("{$directory}/{$fileName}");
    }
}
