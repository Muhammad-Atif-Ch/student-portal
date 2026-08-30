<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CpcCaseStudy\CreateCpcCaseStudyRequest;
use App\Http\Requests\CpcCaseStudy\UpdateCpcCaseStudyRequest;
use App\Models\CpcCaseStudy;
use App\Models\CpcType;
use App\Services\CpcCaseStudy\CpcCaseStudyService;
use Illuminate\Support\Facades\Response;

class CpcCaseStudyController extends Controller
{
    public function __construct(
        private CpcCaseStudyService $service,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $caseStudies = $this->service->listCaseStudies();

        return view('backend.cpc.case-study.index', compact('caseStudies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $cpcTypes = CpcType::orderBy('title')->get();

        return view('backend.cpc.case-study.create', compact('cpcTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCpcCaseStudyRequest $request)
    {
        $response = $this->service->createCaseStudy($request);

        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.cpc.case-study.index');
    }

    /**
     * Display the specified resource (rendered preview).
     */
    public function show(string $id)
    {
        $response = $this->service->showCaseStudy($id);

        return view('backend.cpc.case-study.show', compact('response'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $response = $this->service->showCaseStudy($id);
        $cpcTypes = CpcType::orderBy('title')->get();

        return view('backend.cpc.case-study.edit', compact('response', 'cpcTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCpcCaseStudyRequest $request, $id)
    {
        $response = $this->service->updateCaseStudy($request, $id);

        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.cpc.case-study.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CpcCaseStudy $caseStudy)
    {
        $response = $this->service->destroy($caseStudy);

        return Response::sendResponse($response?->getResponeType(), $response?->code(), $response?->message(), redirect: 'admin.cpc.case-study.index');
    }
}
