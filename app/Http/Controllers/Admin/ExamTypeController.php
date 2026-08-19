<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\Type\CreateTypeRequest;
use App\Http\Requests\Exam\Type\UpdateTypeRequest;
use App\Models\ExamType;
use App\Services\Exam\Type\ExamTypeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ExamTypeController extends Controller
{
    public function __construct(private ExamTypeService $service)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $examTypes = $this->service->listExamTypes();

        return view('backend.exam.type.index', compact('examTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.exam.type.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTypeRequest $request)
    {
        $response = $this->service->createExamType($request);

        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.exam.type.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        dd("here show", $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $response = $this->service->showExamType($id);

        return view('backend.exam.type.edit', compact('response'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTypeRequest $request, $id)
    {
        $response = $this->service->updateExamType($request, $id);
        
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.exam.type.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExamType $type)
    {
        $response = $this->service->destroy($type);

        return Response::sendResponse($response?->getResponeType(), $response?->code(), $response?->message(), redirect: 'admin.exam.type.index');
    }
}
