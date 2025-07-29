<?php

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\QuestionTranslation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use App\Services\Language\LanguageService;
use App\Http\Requests\Language\StoreLanguageRequest;
use App\Http\Requests\Language\UpdateLanguageRequest;

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
        // $translation = QuestionTranslation::where('language_id', $language->id)

        try {
            DB::beginTransaction();

            // Delete all question translations for this language first
            $language->questionTranslations()->forceDelete();

            // Now delete the language
            $response = $this->service->destroy($language);

            DB::commit();

            return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.language.index');
        } catch (\Exception $e) {
            DB::rollback();

            return Response::sendResponse("ERROR", $e->getCode(), $e->getMessage(), redirect: 'admin.language.index');
        }

        // return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.language.index');
    }

}
