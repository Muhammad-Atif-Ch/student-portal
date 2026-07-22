<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\TranslationGlossary\CreateTranslationGlossaryRequest;
use App\Http\Requests\TranslationGlossary\ImportTranslationGlossaryRequest;
use App\Http\Requests\TranslationGlossary\UpdateTranslationGlossaryRequest;
use App\Models\Language;
use App\Models\TranslationGlossary;
use App\Services\Translation\Glossary\TranslationGlossaryService;
use Illuminate\Support\Facades\Response;

class TranslationGlossaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(private TranslationGlossaryService $service) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $languages = Language::get();
        $translationGlossary = $this->service->listTranslationGlossary();

        return view('backend.translations.glossary.index', compact('translationGlossary', 'languages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $languages = Language::get();

        return view('backend.translations.glossary.create', compact('languages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTranslationGlossaryRequest $request)
    {
        $response = $this->service->createTranslationGlossary($request);

        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.translations.glossary.index');
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
        $languages = Language::get();
        $translationGlossary = $this->service->showTranslationGlossary($id);

        return view('backend.translations.glossary.edit', compact('languages', 'translationGlossary'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTranslationGlossaryRequest $request, $id)
    {
        $response = $this->service->updateTranslationGlossary($request, $id);

        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.translations.glossary.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TranslationGlossary $glossary)
    {
        $response = $this->service->destroy($glossary);

        return Response::sendResponse($response?->getResponeType(), $response?->code(), $response?->message(), redirect: 'admin.translations.glossary.index');
    }

    public function importTranslationGlossary(ImportTranslationGlossaryRequest $request)
    {
        $response = $this->service->importGlossary($request);

        if ($response->getResponeType() === ResponseCode::ERROR && $response->code() === 422) {
            // Row-level validation failures — send back with details for the view to render
            return redirect()
                ->route('admin.translations.glossary.index')
                ->with('error', $response->message())
                ->with('import_failures', $response->getData());
        }

        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.translations.glossary.index');
    }

    public function destroyAll()
    {
        $response = $this->service->destroyAll();

        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.translations.glossary.index');
    }
}
