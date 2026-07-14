<?php

namespace App\Services\Translation;

use App\Core\Contracts\Responses\AbstractResponseInterface;
use App\Core\Services\AbstractService;
use App\Helpers\ResponseCode;
use App\Helpers\UploadFile;
use App\Repositories\TranslationRepository;
use App\Responses\TranslationResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TranslationService extends AbstractService
{
    public function __construct(
        TranslationRepository $repository,
        TranslationResponse $response,
        Request $request
    ) {
        $this->repository = $repository;
        $this->response = $response;
        $this->request = $request;
    }

    public function listTranslations(Request $request): LengthAwarePaginator
    {
        $where = [];
        $where = collect($request->only(['quiz_id', 'question_id', 'language_id', 'type']))
            ->filter(fn ($value) => ! is_null($value))
            ->toArray();

        $this->setLimit(50);

        return $this->repository->getByCondition(conditions: $where, with: ['quiz:id', 'question:id', 'language:id,name']);
    }

    public function createTranslation(CreateTranslationRequest $request): AbstractResponseInterface
    {
        $data = $request->validated();
        if ($request->hasFile('visual_explanation')) {
            $uploadFile = new UploadFile;
            $imageName = $uploadFile->upload('images', $request->file('visual_explanation'));
            $data['visual_explanation'] = $imageName;
        }

        if ($request->hasFile('image')) {
            $uploadFile = new UploadFile;
            $imageName = $uploadFile->upload('images', $request->file('image'));
            $data['image'] = $imageName;
        }
        $this->create($data);
        $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getCreateResponseMessage());

        return $this->response;
    }

    public function showTranslation($quiz_id, $id): Model
    {
        return $this->getWhere(['quiz_id' => $quiz_id, 'id' => $id]);
    }

    public function updateTranslation(UpdateTranslationRequest $request, $id): AbstractResponseInterface
    {
        $data = $request->validated();
        $translation = Translation::findOrFail($id);

        if ($request->hasFile('visual_explanation')) {
            if ($translation->visual_explanation) {
                $filePath = public_path("images/$translation->visual_explanation");
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $uploadFile = new UploadFile;
            $imageName = $uploadFile->upload('images', $request->file('visual_explanation'));
            $data['visual_explanation'] = $imageName;
        }

        if ($request->hasFile('image')) {
            if ($translation->image) {
                $filePath = public_path("images/$translation->image");
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
        $this->response->setData(['translation_id' => $translation->id]); // Add translation ID to response

        return $this->response;
    }

    public function destroyAll($quiz_id)
    {
        $translations = $this->repository->getListWithoutPagination(['quiz_id' => $quiz_id]);
        foreach ($translations as $t) {
            $this->destroy($t->id);
        }

        $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getDeleteResponseMessage());

        return $this->response;
    }
}
