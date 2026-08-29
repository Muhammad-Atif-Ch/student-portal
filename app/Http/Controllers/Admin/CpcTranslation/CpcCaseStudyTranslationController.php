<?php

namespace App\Http\Controllers\Admin\CpcTranslation;

use App\Helpers\ResponseCode;
use App\Http\Controllers\Controller;
use App\Models\CpcCaseStudy;
use App\Models\CpcCaseStudyBlock;
use App\Models\Language;
use App\Services\AzureTextToSpeech\AzureTTSService;
use App\Services\AzureTranslation\AzureTranslatorService;
use App\Services\CpcCaseStudy\CpcCaseStudyService;
use App\Services\CpcTranslation\CpcCaseStudyTranslationService;

class CpcCaseStudyTranslationController extends Controller
{
    public function __construct(
        private CpcCaseStudyService $service,
        private CpcCaseStudyTranslationService $translationService,
    ) {
    }

    /**
     * Display a listing of case studies with their translation status.
     */
    public function translationIndex()
    {
        $caseStudies = $this->service->listCaseStudiesForTranslation();
        $languages = Language::where('status', 1)->get();

        return view('backend.cpc.translation.index', compact('caseStudies', 'languages'));
    }

    /**
     * List the questions that belong to a case study, with their translation status.
     */
    public function translationQuestions(CpcCaseStudy $caseStudy)
    {
        $caseStudy->load(['cpcQuestions.options.translations', 'cpcQuestions.translations']);
        $languages = Language::where('status', 1)->get();

        return view('backend.cpc.translation.questions', compact('caseStudy', 'languages'));
    }

    /**
     * Translate only the case study title into the given language.
     */
    public function translateTitle(CpcCaseStudy $caseStudy, Language $language, AzureTranslatorService $translator)
    {
        $response = $this->translationService->translateTitle($caseStudy, $language, $translator);

        return $response->getResponeType() === ResponseCode::ERROR
            ? response()->json(['error' => $response->message()], $response->code())
            : response()->json(['success' => true] + $response->getData());
    }

    /**
     * Translate a single block into the given language.
     */
    public function translateBlock(CpcCaseStudy $caseStudy, CpcCaseStudyBlock $block, Language $language, AzureTranslatorService $translator)
    {
        $response = $this->translationService->translateBlock($block, $language, $translator);

        return $response->getResponeType() === ResponseCode::ERROR
            ? response()->json(['error' => $response->message()], $response->code())
            : response()->json(['success' => true] + $response->getData());
    }

    /**
     * Generate audio for a single block in the given language.
     */
    public function generateAudioForBlock(CpcCaseStudy $caseStudy, CpcCaseStudyBlock $block, Language $language, AzureTTSService $tts)
    {
        $response = $this->translationService->generateAudioForBlock($block, $language, $tts);

        return $response->getResponeType() === ResponseCode::ERROR
            ? response()->json(['error' => $response->message()], $response->code())
            : response()->json(['success' => true] + $response->getData());
    }

    /**
     * Translate the case study into every active language and generate the audio for each.
     */
    public function translateAll(CpcCaseStudy $caseStudy, AzureTranslatorService $translator, AzureTTSService $tts)
    {
        $message = $this->runForActiveLanguages(function (Language $language) use ($caseStudy, $translator, $tts) {
            $translateResponse = $this->translationService->translate($caseStudy, $language, $translator);

            if ($translateResponse->getResponeType() === ResponseCode::ERROR) {
                return $translateResponse->message();
            }

            $audioResponse = $this->translationService->generateAudio($caseStudy, $language, $tts);

            return "{$translateResponse->message()} / {$audioResponse->message()}";
        });

        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * Run the given action for every active language and combine the resulting messages.
     */
    private function runForActiveLanguages(\Closure $action): string
    {
        $messages = [];

        foreach (Language::where('status', 1)->get() as $language) {
            $messages[] = "{$language->name}: {$action($language)}";
        }

        return implode(' | ', $messages);
    }
}
