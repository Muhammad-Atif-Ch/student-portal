<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;

class ReportController extends Controller
{
    public function __construct(private ReportService $service)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = $this->service->listUser();
        return view('backend.report.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = $this->service->createUserData();
        return view('backend.report.create', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateUserRequest $request)
    {
        $response = $this->service->createUser($request);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.report.index');
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
        $user = $this->service->showUser($id);
        $data = $this->service->createUserData();
        return view('backend.user.edit', compact('user', 'data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        $response = $this->service->updateUser($request, $id);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.report.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $response = $this->service->destroy($user);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.report.index');
    }
}
