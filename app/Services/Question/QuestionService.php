<?php

namespace App\Services\Question;

use App\Core\Contracts\Responses\AbstractResponseInterface;
use App\Core\Services\AbstractService;
use App\Helpers\ResponseCode;
use App\Helpers\UploadFile;
use App\Http\Requests\Question\CreateQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;
use App\Models\Question;
use App\Repositories\QuestionRepository;
use App\Responses\QuestionResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class QuestionService extends AbstractService
{
    public function __construct(
        QuestionRepository $repository,
        QuestionResponse $response,
        Request $request,
        private UploadFile $uploadFile
    ) {
        $this->repository = $repository;
        $this->response = $response;
        $this->request = $request;
    }

    public function createQuestion(CreateQuestionRequest $request): AbstractResponseInterface
    {
        $start = microtime(true);
        $data = $request->validated();
        Log::info('[CreateQuestion] Validation passed', [
        'elapsed_ms' => round((microtime(true) - $start) * 1000, 2),
    ]);
        try {
            if ($request->hasFile('visual_explanation')) {
                $data['visual_explanation'] = $this->uploadFile->upload('images', $request->file('visual_explanation'));
            }

            if ($request->hasFile('image')) {
                $data['image'] = $this->uploadFile->upload('images', $request->file('image'));
            }

            $this->create($data);

            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getCreateResponseMessage());
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
        }

        return $this->response;
    }

    public function listQuestion($quiz_id): LengthAwarePaginator
    {
        $this->setLimit(50);

        return $this->repository->getByCondition(['quiz_id' => $quiz_id]);
    }

    public function showQuestion($quiz_id, $id): Model
    {
        return $this->getWhere(['quiz_id' => $quiz_id, 'id' => $id]);
    }

    public function updateQuestion(UpdateQuestionRequest $request, $id): AbstractResponseInterface
    {
        $data = $request->validated();
        $question = Question::findOrFail($id);

        try {
            if ($request->hasFile('visual_explanation')) {
                $newFile = $this->uploadFile->upload('images', $request->file('visual_explanation'));
                $this->deleteImageFile($question->visual_explanation);
                $data['visual_explanation'] = $newFile;
            }

            if ($request->hasFile('image')) {
                $newFile = $this->uploadFile->upload('images', $request->file('image'));
                $this->deleteImageFile($question->image);
                $data['image'] = $newFile;
            }

            $this->update($data, $id);

            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
            $this->response->setData(['question_id' => $question->id]);
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
        }

        if ($request->hasFile('visual_explanation')) {
            if ($question->visual_explanation) {
                $filePath = public_path("images/$question->visual_explanation");
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $uploadFile = new UploadFile;
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

            $uploadFile = new UploadFile;
            $imageName = $uploadFile->upload('images', $request->file('image'));
            $data['image'] = $imageName;
        }

        $this->update($data, $id);

        $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
        $this->response->setData(['question_id' => $question->id]); // Add question ID to response

        return $this->response;
    }

    private function deleteImageFile(?string $fileName): void
    {
        if (! $fileName) {
            return;
        }

        $filePath = public_path("images/{$fileName}");

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function destroyAll($quiz_id)
    {
        $questions = $this->repository->getListWithoutPagination(['quiz_id' => $quiz_id]);
        foreach ($questions as $q) {
            $this->destroy($q);
        }

        $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getDeleteResponseMessage());

        return $this->response;
    }
}
