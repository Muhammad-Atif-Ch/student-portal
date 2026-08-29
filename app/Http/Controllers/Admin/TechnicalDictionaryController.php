<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TechnicalDictionary\CreateTechnicalDictionaryRequest;
use App\Http\Requests\TechnicalDictionary\UpdateTechnicalDictionaryRequest;
use App\Models\Language;
use App\Models\TechnicalDictionary;
use App\Services\TechnicalDictionary\TechnicalDictionaryService;
use Illuminate\Support\Facades\Response;

class TechnicalDictionaryController extends Controller
{
    public function __construct(private TechnicalDictionaryService $service)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $technicalDictionaries = $this->service->listTechnicalDictionaries();
        $languages = Language::where('status', 1)->get();

        return view('backend.technical-dictionary.index', compact('technicalDictionaries', 'languages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.technical-dictionary.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTechnicalDictionaryRequest $request)
    {
        $response = $this->service->createTechnicalDictionary($request);

        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.technical-dictionary.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $response = $this->service->showTechnicalDictionary($id);

        return view('backend.technical-dictionary.edit', compact('response'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTechnicalDictionaryRequest $request, $id)
    {
        $response = $this->service->updateTechnicalDictionary($request, $id);

        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.technical-dictionary.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TechnicalDictionary $technicalDictionary)
    {
        $response = $this->service->destroy($technicalDictionary);

        return Response::sendResponse($response?->getResponeType(), $response?->code(), $response?->message(), redirect: 'admin.technical-dictionary.index');
    }
}
