<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CpcType\CreateCpcTypeRequest;
use App\Http\Requests\CpcType\UpdateCpcTypeRequest;
use App\Models\CpcType;
use App\Services\CpcType\CpcTypeService;
use Illuminate\Support\Facades\Response;

class CpcTypeController extends Controller
{
    public function __construct(private CpcTypeService $service)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cpcTypes = $this->service->listCpcTypes();

        return view('backend.cpc.type.index', compact('cpcTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.cpc.type.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCpcTypeRequest $request)
    {
        $response = $this->service->createCpcType($request);

        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.cpc.type.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $response = $this->service->showCpcType($id);

        return view('backend.cpc.type.edit', compact('response'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCpcTypeRequest $request, $id)
    {
        $response = $this->service->updateCpcType($request, $id);

        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.cpc.type.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CpcType $type)
    {
        $response = $this->service->destroy($type);

        return Response::sendResponse($response?->getResponeType(), $response?->code(), $response?->message(), redirect: 'admin.cpc.type.index');
    }
}
