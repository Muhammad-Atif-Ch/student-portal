<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\PoolRule\CreatePoolRuleRequest;
use App\Http\Requests\Exam\PoolRule\UpdatePoolRuleRequest;
use App\Models\ExamPoolRule;
use App\Models\ExamType;
use App\Models\Quiz;
use App\Services\Exam\PoolRule\ExamPoolRuleService;
use Illuminate\Support\Facades\Response;

class ExamPoolRuleController extends Controller
{
    public function __construct(private ExamPoolRuleService $service)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $poolRules = $this->service->listExamPoolRules();

        return view('backend.exam.pool-rule.index', compact('poolRules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $examTypes = ExamType::orderBy('name')->get();
        $quizzes = Quiz::orderBy('title')->get();

        return view('backend.exam.pool-rule.create', compact('examTypes', 'quizzes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePoolRuleRequest $request)
    {
        $response = $this->service->createExamPoolRule($request);

        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.exam.pool-rule.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $response = $this->service->showExamPoolRule($id);
        $examTypes = ExamType::orderBy('name')->get();
        $quizzes = Quiz::orderBy('title')->get();

        return view('backend.exam.pool-rule.edit', compact('response', 'examTypes', 'quizzes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePoolRuleRequest $request, $id)
    {
        $response = $this->service->updateExamPoolRule($request, $id);

        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.exam.pool-rule.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExamPoolRule $pool_rule)
    {
        $response = $this->service->destroy($pool_rule);

        return Response::sendResponse($response?->getResponeType(), $response?->code(), $response?->message(), redirect: 'admin.exam.pool-rule.index');
    }
}
