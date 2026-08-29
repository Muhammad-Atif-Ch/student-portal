<?php

namespace App\Services\TechnicalDictionary;

use App\Core\Contracts\Responses\AbstractResponseInterface;
use App\Core\Services\AbstractService;
use App\Helpers\ResponseCode;
use App\Helpers\UploadFile;
use App\Http\Requests\TechnicalDictionary\CreateTechnicalDictionaryRequest;
use App\Http\Requests\TechnicalDictionary\UpdateTechnicalDictionaryRequest;
use App\Repositories\TechnicalDictionaryRepository;
use App\Responses\TechnicalDictionaryResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TechnicalDictionaryService extends AbstractService
{
    public function __construct(
        TechnicalDictionaryRepository $repository,
        TechnicalDictionaryResponse $response,
        Request $request,
        private UploadFile $uploadFile,
    ) {
        $this->repository = $repository;
        $this->response = $response;
        $this->request = $request;
    }

    public function createTechnicalDictionary(CreateTechnicalDictionaryRequest $request): AbstractResponseInterface
    {
        $data = $request->validated();

        try {
            $image = null;

            if ($request->hasFile('image')) {
                $image = $this->uploadFile->upload('technical-dictionary', $request->file('image'));
            }

            $this->create([
                'term' => $data['term'],
                'explanation' => $data['explanation'],
                'image' => $image,
            ]);

            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getCreateResponseMessage());
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
        }

        return $this->response;
    }

    public function listTechnicalDictionaries(): LengthAwarePaginator
    {
        $this->setLimit(50);

        return $this->repository->getList(['translations.language']);
    }

    public function showTechnicalDictionary($id): Model
    {
        return $this->getById($id, ['translations.language']);
    }

    public function updateTechnicalDictionary(UpdateTechnicalDictionaryRequest $request, $id): AbstractResponseInterface
    {
        $data = $request->validated();

        try {
            $dictionary = $this->getById($id);
            $image = $dictionary->image;

            if ($request->hasFile('image')) {
                $image = $this->uploadFile->upload('technical-dictionary', $request->file('image'));

                if ($dictionary->image) {
                    $oldPath = public_path("technical-dictionary/{$dictionary->image}");

                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            }

            $this->update([
                'term' => $data['term'],
                'explanation' => $data['explanation'],
                'image' => $image,
            ], $id);

            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
        }

        return $this->response;
    }
}
