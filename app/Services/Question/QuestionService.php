<?php

namespace App\Services\Question;

use App\Models\Question;
use Illuminate\Http\Request;
use App\Helpers\ResponseCode;
use Illuminate\Support\Collection;
use App\Responses\QuestionResponse;
use App\Core\Services\AbstractService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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
        $data = $request->validated();
        if ($request->hasFile('visual_explanation')) {
            $path = $this->request->file('visual_explanation')->store('images', 'public');
            $data['visual_explanation'] = $path;
        }

        if ($request->hasFile('image')) {
            $path = $this->request->file('image')->store('images', 'public');
            $data['image'] = $path;
        }
        $this->create($data);
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
        $question = Question::findOrFail($id);

        if ($request->hasFile('visual_explanation')) {
            if ($question->visual_explanation) {
                Storage::disk('public')->delete($question->visual_explanation);
            }

            $path = $this->request->file('visual_explanation')->store('images', 'public');
            $data['visual_explanation'] = $path;
        }

        if ($request->hasFile('image')) {
            if ($question->image) {
                Storage::disk('public')->delete($question->image);
            }

            $path = $this->request->file('image')->store('images', 'public');
            $data['image'] = $path;
        }

        $this->update($data, $id);
        $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
        return $this->response;
    }

    public function destroyAll($quiz_id)
    {
        $question = $this->listQuestion($quiz_id);
        foreach ($question as $q) {
            $this->destroy($q);
        }

        $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getDeleteResponseMessage());
        return $this->response;
    }

}
