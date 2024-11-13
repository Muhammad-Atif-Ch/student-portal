<?php

namespace App\Http\Controllers\Admin;

use App\Models\Test;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\Test\TestService;
use App\Http\Controllers\Controller;
use App\Repositories\CategoryRepository;
use Illuminate\Support\Facades\Response;
use App\Services\Category\CategoryService;
use App\Http\Requests\Test\CreateTestRequest;
use App\Http\Requests\Category\CreateCategoryRequest;
use App\Http\Requests\Test\UpdateTestRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class TestController extends Controller
{
    public function __construct(private TestService $service)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tests = $this->service->listTest();
        return view('backend.test.index', compact('tests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tests = $this->service->listTest();
        return view('backend.test.create', compact('tests'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTestRequest $request)
    {
        $response = $this->service->createTest($request);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.test.index');
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
        $test = $this->service->showTest($id);
        return view('backend.test.edit', compact('test'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTestRequest $request, string $id)
    {
        $response = $this->service->updateTest($request, $id);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.test.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Test $test)
    {
        $response = $this->service->destroy($test);
        return Response::sendResponse($response->getResponeType(), $response->code(), $response->message(), redirect: 'admin.test.index');
    }
}
