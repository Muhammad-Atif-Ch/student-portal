<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\TargetType\CreateTargetTypeRequest;
use App\Http\Requests\Exam\TargetType\UpdateTargetTypeRequest;
use App\Models\ExamType;
use App\Models\ExamTypeTargetType;
use App\Services\Exam\TargetType\ExamTypeTargetTypeService;
use Illuminate\Support\Facades\Response;

class ExamTypeTargetTypeController extends Controller
{
    public function __construct(private ExamTypeTargetTypeService $service)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $targetTypes = $this->service->listExamTypeTargetTypes();

        return view('backend.exam.target-type.index', compact('targetTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $examTypes = ExamType::orderBy('name')->get();

        return view('backend.exam.target-type.create', compact('examTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTargetTypeRequest $request)
    {
        $response = $this->service->createExamTypeTargetType($request);

        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.exam.target-type.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $response = $this->service->showExamTypeTargetType($id);
        $examTypes = ExamType::orderBy('name')->get();

        return view('backend.exam.target-type.edit', compact('response', 'examTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTargetTypeRequest $request, $id)
    {
        $response = $this->service->updateExamTypeTargetType($request, $id);

        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.exam.target-type.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExamTypeTargetType $target_type)
    {
        $response = $this->service->destroy($target_type);

        return Response::sendResponse($response?->getResponeType(), $response?->code(), $response?->message(), redirect: 'admin.exam.target-type.index');
    }
}
