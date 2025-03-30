<?php

namespace App\Services\Question;

use App\Helpers\UploadFile;
use Illuminate\Http\Request;
use App\Helpers\ResponseCode;
use Illuminate\Support\Collection;
use App\Models\QuestionTranslation;
use App\Core\Services\AbstractService;
use Illuminate\Database\Eloquent\Model;
use App\Responses\QuestionLenguageResponse;
use App\Repositories\QuestionLenguageRepository;
use App\Core\Contracts\Responses\AbstractResponseInterface;
use App\Http\Requests\QuestionLenguage\CreateLenguageRequest;
use App\Http\Requests\QuestionLenguage\UpdateLenguageRequest;

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
        try {
            $data = $request->validated();
            $data['quiz_id'] = $quiz_id;
            $data['question_id'] = $question_id;
            if ($request->hasFile('title_audio_file')) {
                $uploadFile = new UploadFile();
                $data['title_audio_file'] = $uploadFile->upload('audios', $request->file('title_audio_file'));
            }
            if ($request->hasFile('a_audio_file')) {
                $uploadFile = new UploadFile();
                $data['a_audio_file'] = $uploadFile->upload('audios', $request->file('a_audio_file'));
            }
            if ($request->hasFile('b_audio_file')) {
                $uploadFile = new UploadFile();
                $data['b_audio_file'] = $uploadFile->upload('audios', $request->file('b_audio_file'));
            }
            if ($request->hasFile('c_audio_file')) {
                $uploadFile = new UploadFile();
                $data['c_audio_file'] = $uploadFile->upload('audios', $request->file('c_audio_file'));
            }
            if ($request->hasFile('d_audio_file')) {
                $uploadFile = new UploadFile();
                $data['d_audio_file'] = $uploadFile->upload('audios', $request->file('d_audio_file'));
            }
            $this->create($data);
            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getCreateResponseMessage());
            return $this->response;
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, ResponseCode::REGULAR, $e->getMessage());
            return $this->response;
        }
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
                $filePath = public_path("audios/$translation->title_audio_file");
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $path = $this->request->file('title_audio_file')->store('audios', 'public');
            $data['title_audio_file'] = $path;
        }
        if ($request->hasFile('a_audio_file')) {
            if ($translation->a_audio_file) {
                $filePath = public_path("audios/$translation->a_audio_file");
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $path = $this->request->file('a_audio_file')->store('audios', 'public');
            $data['a_audio_file'] = $path;
        }
        if ($request->hasFile('b_audio_file')) {
            if ($translation->b_audio_file) {
                $filePath = public_path("audios/$translation->b_audio_file");
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $path = $this->request->file('b_audio_file')->store('audios', 'public');
            $data['b_audio_file'] = $path;
        }
        if ($request->hasFile('c_audio_file')) {
            if ($translation->c_audio_file) {
                $filePath = public_path("audios/$translation->c_audio_file");
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $path = $this->request->file('c_audio_file')->store('audios', 'public');
            $data['c_audio_file'] = $path;
        }
        if ($request->hasFile('d_audio_file')) {
            if ($translation->d_audio_file) {
                $filePath = public_path("audios/$translation->d_audio_file");
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $path = $this->request->file('d_audio_file')->store('audios', 'public');
            $data['d_audio_file'] = $path;
        }
        $this->update($data, $id);
        $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
        return $this->response;
    }

}
