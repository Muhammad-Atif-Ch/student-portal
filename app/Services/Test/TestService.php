<?php

namespace App\Services\Test;

use Illuminate\Http\Request;
use App\Helpers\ResponseCode;
use App\Responses\TestResponse;
use Illuminate\Support\Collection;
use App\Repositories\TestRepository;
use App\Core\Services\AbstractService;
use App\Http\Requests\Test\CreateTestRequest;
use App\Http\Requests\Test\UpdateTestRequest;
use App\Core\Contracts\Responses\AbstractResponseInterface;
use Illuminate\Database\Eloquent\Model;

class TestService extends AbstractService
{

    public function __construct(TestRepository $repository, TestResponse $response, Request $request)
    {
        $this->repository = $repository;
        $this->response = $response;
        $this->request = $request;
    }

    public function createTest(CreateTestRequest $request): AbstractResponseInterface
    {
        $test = $this->create($request->validated());
        $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getCreateResponseMessage());
        return $this->response;
    }

    public function listTest(): Collection
    {
        $tests = $this->repository->getListWithoutPagination();
        return $tests;
    }

    public function showTest($id): Model
    {
        return $this->getById($id);
    }

/*************  ✨ Codeium Command ⭐  *************/
/******  71fa46c5-05d6-4155-bb37-6274d6b64da1  *******/
    public function updateTest(UpdateTestRequest $request, $id): AbstractResponseInterface
    {
        $this->update($request->validated(), $id);
        $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
        return $this->response;
    }

}
