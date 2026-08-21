<?php

namespace App\Services\Exam\PoolRule;

use App\Core\Contracts\Responses\AbstractResponseInterface;
use App\Core\Services\AbstractService;
use App\Helpers\ResponseCode;
use App\Http\Requests\Exam\PoolRule\CreatePoolRuleRequest;
use App\Http\Requests\Exam\PoolRule\UpdatePoolRuleRequest;
use App\Repositories\ExamPoolRuleRepository;
use App\Responses\ExamPoolRuleResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ExamPoolRuleService extends AbstractService
{
    public function __construct(
        ExamPoolRuleRepository $repository,
        ExamPoolRuleResponse $response,
        Request $request,
    ) {
        $this->repository = $repository;
        $this->response = $response;
        $this->request = $request;
    }

    public function createExamPoolRule(CreatePoolRuleRequest $request): AbstractResponseInterface
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

    public function listExamPoolRules(): LengthAwarePaginator
    {
        $this->setLimit(50);

        return $this->repository->getList(['examType', 'quiz']);
    }

    public function showExamPoolRule($id): Model
    {
        return $this->getWhere(['id' => $id]);
    }

    public function updateExamPoolRule(UpdatePoolRuleRequest $request, $id): AbstractResponseInterface
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
