<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseCode;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\QuestionTranslation;
use App\Services\AzureTranslation\AzureTranslatorService;
use App\Services\Translation\TranslationActionService;
use App\Services\Translation\TranslationProgressService;
use App\Services\Translation\TranslationReportService;
use App\Services\Translation\TranslationService;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    private const FIELDS = ['question', 'a', 'b', 'c', 'd', 'answer_explanation'];

    public function __construct(
        private TranslationService $translationService,
        private TranslationActionService $actionService,
        private TranslationProgressService $progressService,
        private TranslationReportService $reportService,
    ) {
    }

    public function index(Request $request)
    {
        $this->translationService->setLimit(50);
        $translations = $this->translationService->index($request->only(['quiz_id', 'question_id', 'language_id', 'type']));
        $languages = Language::get();

        return view('backend.translations.index', compact('translations', 'languages'));
    }

    public function create()
    {
        return view('backend.translations.create_translation');
    }

    public function store()
    {
        $response = $this->actionService->translateAll();

        return $response->getResponeType() === ResponseCode::ERROR
            ? response()->json(['error' => $response->message()], $response->code())
            : response()->json(['success' => true, 'message' => $response->message()]);
    }

    public function retranslateField(Request $request, QuestionTranslation $translation, AzureTranslatorService $translator)
    {
        $request->validate(['field' => 'required|in:'.implode(',', self::FIELDS)]);

        if (empty(config('services.azure_translator.key'))) {
            return response()->json(['error' => 'Azure Translator API key is not configured. Set AZURE_TRANSLATOR_KEY in .env.'], 500);
        }

        $response = $this->actionService->retranslateField($translation, $request->input('field'), $translator);

        return $response->getResponeType() === ResponseCode::ERROR
            ? response()->json(['error' => $response->message()], $response->code())
            : response()->json(array_merge(['success' => true], $response->getData()));
    }

    public function getProgress()
    {
        return response()->json($this->progressService->getProgress());
    }

    public function getReport()
    {
        return response()->json($this->reportService->getReport());
    }

    public function getQuestionProgress($question_id)
    {
        return response()->json($this->progressService->getQuestionProgress((int) $question_id));
    }

    public function stopTranslation()
    {
        $response = $this->actionService->stopTranslation();

        return $response->getResponeType() === ResponseCode::ERROR
            ? response()->json(['error' => $response->message()], $response->code())
            : response()->json(['success' => true, 'message' => $response->message()]);
    }
}