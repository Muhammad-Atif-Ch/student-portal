<?php

namespace App\Services\Question;

use Illuminate\Http\Request;
use App\Helpers\ResponseCode;
use Illuminate\Support\Collection;
use App\Responses\QuestionResponse;
use App\Core\Services\AbstractService;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\QuestionRepository;
use App\Http\Requests\Question\CreateQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;
use App\Core\Contracts\Responses\AbstractResponseInterface;

class QuestionService extends AbstractService
{

    public function __construct(QuestionRepository $repository, QuestionResponse $response, Request $request)
    {
        $this->repository = $repository;
        $this->response = $response;
        $this->request = $request;
    }

    public function createQuestion(CreateQuestionRequest $request): AbstractResponseInterface
    {
        $this->create($request->validated());
        $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getCreateResponseMessage());
        return $this->response;
    }

    public function listQuestion($quiz_id): Collection
    {
        $questions = $this->repository->getListWithoutPagination(conditions: ['quiz_id' => $quiz_id], orderBy: 'DESC');
        return $questions;
    }

    public function showQuestion($quiz_id, $id): Model
    {
        return $this->getWhere(['quiz_id' => $quiz_id, 'id' => $id]);
    }

    public function updateQuestion(UpdateQuestionRequest $request, $id): AbstractResponseInterface
    {
        $data = $request->validated();
        if ($request->hasFile('audio_file')) {
            $path = $this->request->file('audio_file')->store('audios', 'public');
            $data['audio_file'] = $path;
        }

        if ($request->hasFile('image')) {
            $path = $this->request->file('image')->store('images', 'public');
            $data['image'] = $path;
        }

        $this->update($data, $id);
        $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
        return $this->response;
    }

}
