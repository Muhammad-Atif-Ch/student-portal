<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SampleCpcQuestionExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\CpcQuestion\CreateCpcQuestionRequest;
use App\Http\Requests\CpcQuestion\UpdateCpcQuestionRequest;
use App\Imports\CpcQuestionImport;
use App\Models\CpcCaseStudy;
use App\Models\CpcQuestion;
use App\Services\CpcQuestion\CpcQuestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;

class CpcQuestionController extends Controller
{
    public function __construct(
        private CpcQuestionService $service,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cpcQuestions = $this->service->listCpcQuestions();

        return view('backend.cpc.question.index', compact('cpcQuestions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $caseStudies = CpcCaseStudy::orderBy('title')->get();

        return view('backend.cpc.question.create', compact('caseStudies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCpcQuestionRequest $request)
    {
        $response = $this->service->createCpcQuestion($request);

        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.cpc.question.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $response = $this->service->showCpcQuestion($id);
        $caseStudies = CpcCaseStudy::orderBy('title')->get();

        return view('backend.cpc.question.edit', compact('response', 'caseStudies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCpcQuestionRequest $request, $id)
    {
        $response = $this->service->updateCpcQuestion($request, $id);

        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.cpc.question.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CpcQuestion $question)
    {
        $response = $this->service->destroy($question);

        return Response::sendResponse($response?->getResponeType(), $response?->code(), $response?->message(), redirect: 'admin.cpc.question.index');
    }

    /**
     * Download the sample import template.
     */
    public function downloadSample()
    {
        return Excel::download(new SampleCpcQuestionExport, 'cpc_question_import_sample.xlsx');
    }

    /**
     * Import CPC questions from an Excel/CSV file.
     */
    public function importQuestion(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:2048',
        ]);

        $import = new CpcQuestionImport;
        Excel::import($import, $request->file('file'));

        $failures = $import->failures();

        if ($failures->isNotEmpty()) {
            return back()
                ->with('error', sprintf('%d row(s) failed validation.', $failures->count()))
                ->with('import_failures', $failures->map(fn ($f) => [
                    'row' => $f->row(),
                    'attribute' => $f->attribute(),
                    'errors' => $f->errors(),
                ])->toArray());
        }

        return back()->with('success', 'CPC questions imported successfully!');
    }
}
