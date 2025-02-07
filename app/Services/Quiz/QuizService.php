<?php

namespace App\Services\Quiz;

use Illuminate\Http\Request;
use App\Helpers\ResponseCode;
use App\Responses\TestResponse;
use Illuminate\Support\Collection;
use App\Repositories\QuizRepository;
use App\Core\Services\AbstractService;
use Illuminate\Database\Eloquent\Model;
use App\Http\Requests\Test\CreateTestRequest;
use App\Http\Requests\Test\UpdateTestRequest;
use App\Core\Contracts\Responses\AbstractResponseInterface;

class QuizService extends AbstractService
{
    public function __construct(QuizRepository $repository, TestResponse $response, Request $request)
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

    public function updateTest(UpdateTestRequest $request, $id): AbstractResponseInterface
    {
        $this->update($request->validated(), $id);
        $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
        return $this->response;
    }

}
