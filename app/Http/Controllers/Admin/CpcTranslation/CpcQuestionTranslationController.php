<?php

namespace App\Http\Controllers\Admin\CpcTranslation;

use App\Helpers\ResponseCode;
use App\Http\Controllers\Controller;
use App\Models\CpcCaseStudy;
use App\Models\CpcQuestion;
use App\Models\CpcQuestionOption;
use App\Models\Language;
use App\Services\AzureTextToSpeech\AzureTTSService;
use App\Services\AzureTranslation\AzureTranslatorService;
use App\Services\CpcTranslation\CpcQuestionTranslationService;

class CpcQuestionTranslationController extends Controller
{
    public function __construct(
        private CpcQuestionTranslationService $translationService,
    ) {
    }

    /**
     * Translate the question (text + explanation + options) into the given language.
     */
    public function translate(CpcCaseStudy $caseStudy, CpcQuestion $question, Language $language, AzureTranslatorService $translator)
    {
        $response = $this->translationService->translate($question, $language, $translator);

        return $response->getResponeType() === ResponseCode::ERROR
            ? response()->json(['error' => $response->message()], $response->code())
            : response()->json(['success' => true, 'message' => $response->message()]);
    }

    /**
     * Generate audio (question + options) for the question in the given language.
     */
    public function generateAudio(CpcCaseStudy $caseStudy, CpcQuestion $question, Language $language, AzureTTSService $tts)
    {
        $response = $this->translationService->generateAudio($question, $language, $tts);

        return $response->getResponeType() === ResponseCode::ERROR
            ? response()->json(['error' => $response->message()], $response->code())
            : response()->json(['success' => true, 'message' => $response->message()]);
    }

    /**
     * Translate only the question text into the given language.
     */
    public function translateQuestionText(CpcCaseStudy $caseStudy, CpcQuestion $question, Language $language, AzureTranslatorService $translator)
    {
        $response = $this->translationService->translateQuestionText($question, $language, $translator);

        return $response->getResponeType() === ResponseCode::ERROR
            ? response()->json(['error' => $response->message()], $response->code())
            : response()->json(['success' => true] + $response->getData());
    }

    /**
     * Translate only the answer explanation into the given language.
     */
    public function translateExplanation(CpcCaseStudy $caseStudy, CpcQuestion $question, Language $language, AzureTranslatorService $translator)
    {
        $response = $this->translationService->translateExplanation($question, $language, $translator);

        return $response->getResponeType() === ResponseCode::ERROR
            ? response()->json(['error' => $response->message()], $response->code())
            : response()->json(['success' => true] + $response->getData());
    }

    /**
     * Generate audio for the answer explanation only, in the given language.
     */
    public function generateAudioForExplanation(CpcCaseStudy $caseStudy, CpcQuestion $question, Language $language, AzureTTSService $tts)
    {
        $response = $this->translationService->generateAudioForExplanation($question, $language, $tts);

        return $response->getResponeType() === ResponseCode::ERROR
            ? response()->json(['error' => $response->message()], $response->code())
            : response()->json(['success' => true] + $response->getData());
    }

    /**
     * Translate a single option into the given language.
     */
    public function translateOption(CpcCaseStudy $caseStudy, CpcQuestion $question, CpcQuestionOption $option, Language $language, AzureTranslatorService $translator)
    {
        $response = $this->translationService->translateOption($option, $language, $translator);

        return $response->getResponeType() === ResponseCode::ERROR
            ? response()->json(['error' => $response->message()], $response->code())
            : response()->json(['success' => true] + $response->getData());
    }

    /**
     * Generate audio for the question text only, in the given language.
     */
    public function generateAudioForQuestionText(CpcCaseStudy $caseStudy, CpcQuestion $question, Language $language, AzureTTSService $tts)
    {
        $response = $this->translationService->generateAudioForQuestionText($question, $language, $tts);

        return $response->getResponeType() === ResponseCode::ERROR
            ? response()->json(['error' => $response->message()], $response->code())
            : response()->json(['success' => true] + $response->getData());
    }

    /**
     * Generate audio for a single option in the given language.
     */
    public function generateAudioForOption(CpcCaseStudy $caseStudy, CpcQuestion $question, CpcQuestionOption $option, Language $language, AzureTTSService $tts)
    {
        $response = $this->translationService->generateAudioForOption($option, $language, $tts);

        return $response->getResponeType() === ResponseCode::ERROR
            ? response()->json(['error' => $response->message()], $response->code())
            : response()->json(['success' => true] + $response->getData());
    }

    /**
     * Translate every question of a case study into every available language.
     */
    public function translateAllForCaseStudy(CpcCaseStudy $caseStudy, AzureTranslatorService $translator)
    {
        $languages = Language::where('status', 1)->get();
        $questionCount = 0;
        $failCount = 0;
        $lastError = null;

        foreach ($caseStudy->cpcQuestions as $question) {
            foreach ($languages as $language) {
                $response = $this->translationService->translate($question, $language, $translator);

                if ($response->getResponeType() === ResponseCode::ERROR) {
                    $failCount++;
                    $lastError = $response->message();
                } elseif (str_contains($response->message(), 'partially translated')) {
                    $failCount++;
                    $lastError = $response->message();
                }
            }
            $questionCount++;
        }

        if ($failCount > 0) {
            return response()->json([
                'error' => "Translated {$questionCount} question(s) into {$languages->count()} language(s), but {$failCount} translation(s) failed. Last error: {$lastError}",
            ], 500);
        }

        return response()->json(['success' => true, 'message' => "Translated {$questionCount} question(s) into {$languages->count()} language(s)."]);
    }

    /**
     * Generate audio for every question of a case study in every available language.
     */
    public function generateAudioAllForCaseStudy(CpcCaseStudy $caseStudy, AzureTTSService $tts)
    {
        $languages = Language::where('status', 1)->get();
        $questionCount = 0;
        $failCount = 0;
        $lastError = null;

        foreach ($caseStudy->cpcQuestions as $question) {
            foreach ($languages as $language) {
                $response = $this->translationService->generateAudio($question, $language, $tts);

                if ($response->getResponeType() === ResponseCode::ERROR) {
                    $failCount++;
                    $lastError = $response->message();
                } elseif (str_contains($response->message(), 'partially generated')) {
                    $failCount++;
                    $lastError = $response->message();
                }
            }
            $questionCount++;
        }

        if ($failCount > 0) {
            return response()->json([
                'error' => "Generated audio for {$questionCount} question(s) in {$languages->count()} language(s), but {$failCount} audio generation(s) failed. Last error: {$lastError}",
            ], 500);
        }

        return response()->json(['success' => true, 'message' => "Generated audio for {$questionCount} question(s) in {$languages->count()} language(s)."]);
    }
}
