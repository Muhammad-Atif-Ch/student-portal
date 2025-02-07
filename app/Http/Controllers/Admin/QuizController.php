<?php

namespace App\Http\Controllers\Admin;

use App\Models\Quiz;
use App\Services\Quiz\QuizService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\Test\CreateTestRequest;
use App\Http\Requests\Test\UpdateTestRequest;

class QuizController extends Controller
{
    public function __construct(private QuizService $service)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tests = $this->service->listTest();
        return view('backend.quiz.index', compact('tests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tests = $this->service->listTest();
        return view('backend.quiz.create', compact('tests'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTestRequest $request)
    {
        $response = $this->service->createTest($request);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.quiz.index');
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
    public function edit(string $id)
    {
        $test = $this->service->showTest($id);
        return view('backend.quiz.edit', compact('test'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTestRequest $request, string $id)
    {
        $response = $this->service->updateTest($request, $id);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.quiz.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quiz $test)
    {
        $response = $this->service->destroy($test);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.quiz.index');
    }
}
