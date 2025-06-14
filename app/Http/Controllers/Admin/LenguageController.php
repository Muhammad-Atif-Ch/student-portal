<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use App\Services\Lenguage\LenguageService;
use App\Http\Requests\Lenguage\UpdateLenguageRequest;

class LenguageController extends Controller
{
    public function __construct(private LenguageService $service)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lenguages = $this->service->listLenguage();
        return view('backend.lenguage.index', compact('lenguages'));
    }

    public function update(UpdateLenguageRequest $request, $id)
    {
        $response = $this->service->updateLenguage($request, $id);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.lenguage.index');
    }

}
