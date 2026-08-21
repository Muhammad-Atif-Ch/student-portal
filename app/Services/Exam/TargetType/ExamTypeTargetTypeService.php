<?php

namespace App\Services\Exam\TargetType;

use App\Core\Contracts\Responses\AbstractResponseInterface;
use App\Core\Services\AbstractService;
use App\Helpers\ResponseCode;
use App\Http\Requests\Exam\TargetType\CreateTargetTypeRequest;
use App\Http\Requests\Exam\TargetType\UpdateTargetTypeRequest;
use App\Repositories\ExamTypeTargetTypeRepository;
use App\Responses\ExamTypeTargetTypeResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ExamTypeTargetTypeService extends AbstractService
{
    public function __construct(
        ExamTypeTargetTypeRepository $repository,
        ExamTypeTargetTypeResponse $response,
        Request $request,
    ) {
        $this->repository = $repository;
        $this->response = $response;
        $this->request = $request;
    }

    public function createExamTypeTargetType(CreateTargetTypeRequest $request): AbstractResponseInterface
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

    public function listExamTypeTargetTypes(): LengthAwarePaginator
    {
        $this->setLimit(50);

        return $this->repository->getList(['examType']);
    }

    public function showExamTypeTargetType($id): Model
    {
        return $this->getWhere(['id' => $id]);
    }

    public function updateExamTypeTargetType(UpdateTargetTypeRequest $request, $id): AbstractResponseInterface
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
