<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CpcExam\CreateCpcExamRequest;
use App\Http\Requests\CpcExam\UpdateCpcExamRequest;
use App\Models\CpcExam;
use App\Models\CpcType;
use App\Services\CpcExam\CpcExamService;
use Illuminate\Support\Facades\Response;

class CpcExamController extends Controller
{
    public function __construct(private CpcExamService $service)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cpcExams = $this->service->listCpcExams();

        return view('backend.cpc.exam.index', compact('cpcExams'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $cpcTypes = CpcType::orderBy('title')->get();

        return view('backend.cpc.exam.create', compact('cpcTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCpcExamRequest $request)
    {
        $response = $this->service->createCpcExam($request);

        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.cpc.exam.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $response = $this->service->showCpcExam($id);
        $cpcTypes = CpcType::orderBy('title')->get();

        return view('backend.cpc.exam.edit', compact('response', 'cpcTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCpcExamRequest $request, $id)
    {
        $response = $this->service->updateCpcExam($request, $id);

        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.cpc.exam.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CpcExam $exam)
    {
        $response = $this->service->destroy($exam);

        return Response::sendResponse($response?->getResponeType(), $response?->code(), $response?->message(), redirect: 'admin.cpc.exam.index');
    }
}
