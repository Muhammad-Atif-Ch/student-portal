<?php

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use Illuminate\Http\Request;
use App\Models\LanguageVoice;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use App\Services\Language\LanguageService;
use App\Services\LanguageVoice\LanguageVoiceService;
use App\Http\Requests\LanguageVoice\StoreLanguageVoiceRequest;
use App\Http\Requests\LanguageVoice\UpdateLanguageVoiceRequest;

class LanguageVoiceController extends Controller
{
    public function __construct(private LanguageVoiceService $languageVoiceService, private LanguageService $languageService)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index($languageId)
    {
        $language = Language::findOrFail($languageId);
        $languageVoices = $this->languageVoiceService->listLanguageVoice($languageId);
        return view('backend.language-voice.index', compact('languageVoices', 'language'));
    }

    public function create($languageId)
    {
        $language = $this->languageService->getWhere(['id' => $languageId]);
        return view('backend.language-voice.create', compact('language', 'languageId'));
    }

    public function store(StoreLanguageVoiceRequest $request, $languageId)
    {
        $response = $this->languageVoiceService->storeLanguageVoice($request);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.language.voice.index', route_params: ['language' => $languageId]);
    }

    public function edit(string $languageId, string $id)
    {
        $language = $this->languageService->getWhere(['id' => $languageId]);
        $languageVoice = $this->languageVoiceService->showLanguageVoice($languageId, $id);
        return view('backend.language-voice.edit', compact('language', 'languageVoice', 'languageId'));
    }

    public function update(UpdateLanguageVoiceRequest $request, $languageId, $id)
    {
        $response = $this->languageVoiceService->updateLanguageVoice($request, $languageId, $id);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.language.voice.index', route_params: ['language' => $languageId]);
    }

    public function destroy($languageId, LanguageVoice $languageVoice)
    {
        $response = $this->languageVoiceService->destroy($languageVoice);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.language.voice.index', route_params: ['language' => $languageId]);
    }
}
