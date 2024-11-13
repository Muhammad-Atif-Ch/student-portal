<?php

namespace App\Http\Controllers\Admin;

use App\Models\Question;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use App\Services\Question\QuestionService;
use App\Http\Requests\Question\CreateQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(private QuestionService $service)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index($test_id)
    {
        $questions = $this->service->listQuestion($test_id);
        return view('backend.question.index', compact('test_id','questions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($test_id)
    {
        return view('backend.question.create', compact('test_id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateQuestionRequest $request, $test_id)
    {
        $response = $this->service->createQuestion($request);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: "admin.test.question.index", route_params: ['test' => $test_id]);
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
    public function edit(string $test_id, string $id)
    {
        $question = $this->service->showQuestion($test_id, $id);
        return view('backend.question.edit', compact('test_id', 'question'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuestionRequest $request, string $test_id, string $id)
    {
        $response = $this->service->updateQuestion($request, $id);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.test.question.index', route_params: ['test' => $test_id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($test_id, Question $question)
    {
        $response = $this->service->destroy($question);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.test.question.index', route_params: ['test' => $test_id]);
    }
}
