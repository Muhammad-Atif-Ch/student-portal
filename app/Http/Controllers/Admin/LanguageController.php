<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use App\Services\Language\LanguageService;
use App\Http\Requests\Language\StoreLanguageRequest;
use App\Http\Requests\Language\UpdateLanguageRequest;
use App\Models\Language;

class LanguageController extends Controller
{
    public function __construct(private LanguageService $service)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $languages = $this->service->listLanguage();
        return view('backend.language.index', compact('languages'));
    }

    public function create()
    {
        return view('backend.language.create');
    }

    public function store(StoreLanguageRequest $request)
    {
        $response = $this->service->storeLanguage($request);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.language.index');
    }

    public function edit(string $id)
    {
        $language = $this->service->showLanguage($id);
        return view('backend.language.edit', compact('language'));
    }

    public function update(UpdateLanguageRequest $request, $id)
    {
        $response = $this->service->updateLanguage($request, $id);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.language.index');
    }

    public function status(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:languages,id',
            'status' => 'required|boolean',
        ]);

        try {
            $response = $this->service->updateLanguageStatus($data);
            return response()->json([
                'status' => 'success',
                'message' => 'Status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Language $language)
    {
        $response = $this->service->destroy($language);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.language.index');
    }

}
