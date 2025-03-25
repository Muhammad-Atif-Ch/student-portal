<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\QuestionLenguage\UpdateLenguageRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use App\Services\Question\QuestionLenguageService;
use App\Http\Requests\QuestionLenguage\CreateLenguageRequest;
use App\Models\Language;
use App\Models\QuestionTranslation;

class QuestionLanguageController extends Controller
{

    public function __construct(private QuestionLenguageService $service)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index($quiz_id, $question_id)
    {
        $questions_lenguage = $this->service->listQuestion($quiz_id, $question_id);
        return view('backend.question_lenguage.index', compact('quiz_id', 'question_id', 'questions_lenguage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($quiz_id, $question_id)
    {
        $languages = Language::get();
        return view('backend.question_lenguage.create', compact('quiz_id', 'question_id', 'languages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateLenguageRequest $request, $quiz_id, $question_id)
    {
        $response = $this->service->createQuestion($request, $quiz_id, $question_id);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: "admin.quiz.question.lenguage.index", route_params: ['quiz' => $quiz_id, 'question' => $question_id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($quiz_id, $question_id, string $id)
    {
        $languages = Language::get();
        $question_language = $this->service->showQuestion($quiz_id, $question_id, $id);
        return view('backend.question_lenguage.edit', compact('quiz_id', 'question_id', 'question_language', 'languages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLenguageRequest $request, $quiz_id, $question_id, string $id)
    {
        $response = $this->service->updateQuestion($request, $id);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.quiz.question.language.index', route_params: ['quiz' => $quiz_id, 'question' => $question_id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($quiz_id, $question_id, QuestionTranslation $language)
    {
        $response = $this->service->destroy($language);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.quiz.question.language.index', route_params: ['quiz' => $quiz_id, 'question' => $question_id]);
    }
}
