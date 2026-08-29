<?php

namespace App\Services\CpcExam;

use App\Core\Contracts\Responses\AbstractResponseInterface;
use App\Core\Services\AbstractService;
use App\Helpers\ResponseCode;
use App\Http\Requests\CpcExam\CreateCpcExamRequest;
use App\Http\Requests\CpcExam\UpdateCpcExamRequest;
use App\Repositories\CpcExamRepository;
use App\Responses\CpcExamResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CpcExamService extends AbstractService
{
    public function __construct(
        CpcExamRepository $repository,
        CpcExamResponse $response,
        Request $request,
    ) {
        $this->repository = $repository;
        $this->response = $response;
        $this->request = $request;
    }

    public function createCpcExam(CreateCpcExamRequest $request): AbstractResponseInterface
    {
        $data = $request->validated();

        try {
            $this->create([
                'cpc_type_id' => $data['cpc_type_id'],
                'title' => $data['title'],
                'mode' => $data['mode'],
                'total_time_minutes' => $data['total_time_minutes'],
                'total_questions' => $data['total_questions'],
                'passing_score' => $data['passing_score'],
                'min_marks_per_scenario' => $data['min_marks_per_scenario'] ?? null,
                'is_active' => $request->boolean('is_active'),
            ]);

            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getCreateResponseMessage());
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
        }

        return $this->response;
    }

    public function listCpcExams(): LengthAwarePaginator
    {
        $this->setLimit(50);

        return $this->repository->getList(['type']);
    }

    public function showCpcExam($id): Model
    {
        return $this->getById($id, ['type']);
    }

    public function updateCpcExam(UpdateCpcExamRequest $request, $id): AbstractResponseInterface
    {
        $data = $request->validated();

        try {
            $this->update([
                'cpc_type_id' => $data['cpc_type_id'],
                'title' => $data['title'],
                'mode' => $data['mode'],
                'total_time_minutes' => $data['total_time_minutes'],
                'total_questions' => $data['total_questions'],
                'passing_score' => $data['passing_score'],
                'min_marks_per_scenario' => $data['min_marks_per_scenario'] ?? null,
                'is_active' => $request->boolean('is_active'),
            ], $id);

            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
        }

        return $this->response;
    }
}
