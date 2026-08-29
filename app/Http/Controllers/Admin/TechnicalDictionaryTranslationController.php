<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseCode;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\TechnicalDictionary;
use App\Services\AzureTextToSpeech\AzureTTSService;
use App\Services\AzureTranslation\AzureTranslatorService;
use App\Services\TechnicalDictionaryTranslation\TechnicalDictionaryTranslationService;

class TechnicalDictionaryTranslationController extends Controller
{
    public function __construct(private TechnicalDictionaryTranslationService $translationService)
    {
    }

    /**
     * Re-translate the explanation and regenerate its audio for a single language.
     */
    public function regenerate(TechnicalDictionary $technicalDictionary, Language $language, AzureTranslatorService $translator, AzureTTSService $tts)
    {
        $response = $this->translationService->regenerate($technicalDictionary, $language, $translator, $tts);

        return $response->getResponeType() === ResponseCode::ERROR
            ? response()->json(['error' => $response->message()], $response->code())
            : response()->json(['success' => true] + $response->getData());
    }
}
