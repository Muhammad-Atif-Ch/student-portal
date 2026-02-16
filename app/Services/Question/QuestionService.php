<?php

namespace App\Services\Question;

use App\Models\Question;
use App\Helpers\UploadFile;
use Illuminate\Http\Request;
use App\Helpers\ResponseCode;
use Illuminate\Support\Collection;
use App\Responses\QuestionResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Core\Services\AbstractService;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\QuestionRepository;
use App\Jobs\SingleQuestionTranslationJob;
use App\Http\Requests\Question\CreateQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;
use App\Core\Contracts\Responses\AbstractResponseInterface;

class QuestionService extends AbstractService
{
    public function __construct(
        QuestionRepository $repository,
        QuestionResponse $response,
        Request $request
    ) {
        $this->repository = $repository;
        $this->response = $response;
        $this->request = $request;
    }

    public function createQuestion(CreateQuestionRequest $request): AbstractResponseInterface
    {
        $data = $request->validated();
        if ($request->hasFile('visual_explanation')) {
            $uploadFile = new UploadFile();
            $imageName = $uploadFile->upload('images', $request->file('visual_explanation'));
            $data['visual_explanation'] = $imageName;
        }

        if ($request->hasFile('image')) {
            $uploadFile = new UploadFile();
            $imageName = $uploadFile->upload('images', $request->file('image'));
            $data['image'] = $imageName;
        }
        $this->create($data);
        $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getCreateResponseMessage());
        return $this->response;
    }

    public function listQuestion($quiz_id): Collection
    {
        $questions = $this->repository->getListWithoutPagination(conditions: ['quiz_id' => $quiz_id]);
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

        if ($data['update_type'] === 'form_data_update') {
            if ($request->hasFile('visual_explanation')) {
                if ($question->visual_explanation) {
                    $filePath = public_path("images/$question->visual_explanation");
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }

                $uploadFile = new UploadFile();
                $imageName = $uploadFile->upload('images', $request->file('visual_explanation'));
                $data['visual_explanation'] = $imageName;
            }

            if ($request->hasFile('image')) {
                if ($question->image) {
                    $filePath = public_path("images/$question->image");
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }

                $uploadFile = new UploadFile();
                $imageName = $uploadFile->upload('images', $request->file('image'));
                $data['image'] = $imageName;
            }

            $this->update($data, $id);
        }

        if ($data['update_type'] === 'translation' || $data['update_type'] === 'audio' || $data['update_type'] === 'all_data_update') {
            Cache::forget('translation_stop_flag');
            Cache::forget('translation_force_stop');
            Cache::forget('translation_stopped_at');
            Cache::forget('translation_immediate_stop');
            Cache::forget('translation_progress');
            // After updating the question, dispatch the background job
            $question->refresh(); // Refresh the model to get updated data

            // Log::info('Job call here', ['question_id' => $question->id]);
            dispatch(new SingleQuestionTranslationJob($question, $data['update_type']));
        }

        $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
        $this->response->setData(['question_id' => $question->id]); // Add question ID to response
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
