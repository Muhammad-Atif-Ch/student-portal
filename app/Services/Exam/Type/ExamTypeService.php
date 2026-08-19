<?php

namespace App\Services\Exam\Type;

use App\Core\Contracts\Responses\AbstractResponseInterface;
use App\Core\Services\AbstractService;
use App\Helpers\ResponseCode;
use App\Http\Requests\Exam\Type\CreateTypeRequest;
use App\Http\Requests\Exam\Type\UpdateTypeRequest;
use App\Repositories\ExamTypeRepository;
use App\Responses\ExamTypeResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ExamTypeService extends AbstractService
{
    public function __construct(
        ExamTypeRepository $repository,
        ExamTypeResponse $response,
        Request $request,
    ) {
        $this->repository = $repository;
        $this->response = $response;
        $this->request = $request;
    }

    public function createExamType(CreateTypeRequest $request): AbstractResponseInterface
    {
        $data = $request->validated();

        try {
            $this->create($data);

            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getCreateResponseMessage());
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
        }

        return $this->response;
    }

    public function listExamTypes(): LengthAwarePaginator
    {
        $this->setLimit(50);

        return $this->repository->getList();
    }

    public function showExamType($id): Model
    {
        return $this->getWhere(['id' => $id]);
    }

    public function updateExamType(UpdateTypeRequest $request, $id): AbstractResponseInterface
    {
        $data = $request->validated();

        try {
            $this->update($data, $id);

            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
        }

        return $this->response;
    }
}
