<?php

namespace App\Services\Question;

use Illuminate\Http\Request;
use App\Helpers\ResponseCode;
use GuzzleHttp\Promise\Create;
use Illuminate\Support\Collection;
use App\Responses\QuestionResponse;
use App\Core\Services\AbstractService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Repositories\QuestionRepository;
use App\Responses\QuestionLenguageResponse;
use App\Repositories\QuestionLenguageRepository;
use App\Core\Contracts\Responses\AbstractResponseInterface;
use App\Http\Requests\QuestionLenguage\CreateLenguageRequest;
use App\Http\Requests\QuestionLenguage\UpdateLenguageRequest;
use App\Models\QuestionTranslation;

class QuestionLenguageService extends AbstractService
{

    public function __construct(QuestionLenguageRepository $repository, QuestionLenguageResponse $response, Request $request)
    {
        $this->repository = $repository;
        $this->response = $response;
        $this->request = $request;
    }

    public function createQuestion(CreateLenguageRequest $request, $quiz_id, $question_id): AbstractResponseInterface
    {
        $data = $request->validated();
        $data['quiz_id'] = $quiz_id;
        $data['question_id'] = $question_id;
        if ($request->hasFile('title_audio_file')) {
            $path = $this->request->file('title_audio_file')->store('audios', 'public');
            $data['title_audio_file'] = $path;
        }
        if ($request->hasFile('a_audio_file')) {
            $path = $this->request->file('a_audio_file')->store('audios', 'public');
            $data['a_audio_file'] = $path;
        }
        if ($request->hasFile('b_audio_file')) {
            $path = $this->request->file('b_audio_file')->store('audios', 'public');
            $data['b_audio_file'] = $path;
        }
        if ($request->hasFile('c_audio_file')) {
            $path = $this->request->file('c_audio_file')->store('audios', 'public');
            $data['c_audio_file'] = $path;
        }
        if ($request->hasFile('d_audio_file')) {
            $path = $this->request->file('d_audio_file')->store('audios', 'public');
            $data['d_audio_file'] = $path;
        }
        $this->create($data);
        $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getCreateResponseMessage());
        return $this->response;
    }

    public function listQuestion($quiz_id, $question_id): Collection
    {
        $questions = $this->repository->getListWithoutPagination(conditions: ['quiz_id' => $quiz_id, 'question_id' => $question_id], with: ['quiz', 'question', 'language'], orderBy: 'DESC');
        return $questions;
    }

    public function showQuestion($quiz_id, $question_id, $id): Model
    {
        return $this->getWhere(['quiz_id' => $quiz_id, 'question_id' => $question_id, 'id' => $id]);
    }

    public function updateQuestion(UpdateLenguageRequest $request, $id): AbstractResponseInterface
    {
        $data = $request->validated();
        $translation = QuestionTranslation::findOrFail($id);
        if ($request->hasFile('title_audio_file')) {
            if ($translation->title_audio_file) {
                Storage::disk('public')->delete($translation->title_audio_file);
            }

            $path = $this->request->file('title_audio_file')->store('audios', 'public');
            $data['title_audio_file'] = $path;
        }
        if ($request->hasFile('a_audio_file')) {
            $path = $this->request->file('a_audio_file')->store('audios', 'public');
            $data['a_audio_file'] = $path;
        }
        if ($request->hasFile('b_audio_file')) {
            $path = $this->request->file('b_audio_file')->store('audios', 'public');
            $data['b_audio_file'] = $path;
        }
        if ($request->hasFile('c_audio_file')) {
            $path = $this->request->file('c_audio_file')->store('audios', 'public');
            $data['c_audio_file'] = $path;
        }
        if ($request->hasFile('d_audio_file')) {
            $path = $this->request->file('d_audio_file')->store('audios', 'public');
            $data['d_audio_file'] = $path;
        }
        $this->update($data, $id);
        $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
        return $this->response;
    }

}
