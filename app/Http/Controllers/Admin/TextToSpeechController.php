<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseCode;
use App\Http\Controllers\Controller;
use App\Models\QuestionTranslation;
use App\Services\AzureTextToSpeech\AzureTTSService;
use App\Services\Tts\TtsActionService;
use App\Services\Tts\TtsProgressService;
use App\Services\Tts\TtsReportService;
use Illuminate\Http\Request;

class TextToSpeechController extends Controller
{
    public function __construct(
        private TtsActionService $actionService,
        private TtsProgressService $progressService,
        private TtsReportService $reportService,
    ) {}

    public function reconvertField(Request $request, QuestionTranslation $translation, AzureTTSService $tts)
    {
        $request->validate([
            'field' => 'required|in:'.implode(',', TtsActionService::FIELDS),
        ]);

        $response = $this->actionService->reconvertField($translation, $request->input('field'), $tts);

        return $response->getResponeType() === ResponseCode::ERROR
            ? response()->json(['error' => $response->message()], $response->code())
            : response()->json(array_merge(['success' => true], $response->getData()));
    }
}
