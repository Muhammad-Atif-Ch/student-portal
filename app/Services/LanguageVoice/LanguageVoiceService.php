<?php

namespace App\Services\LanguageVoice;

use App\Helpers\ResponseCode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Core\Services\AbstractService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use App\Responses\LanguageVoiceResponse;
use App\Repositories\LanguageVoiceRepository;
use App\Core\Contracts\Responses\AbstractResponseInterface;
use App\Http\Requests\LanguageVoice\StoreLanguageVoiceRequest;
use App\Http\Requests\LanguageVoice\UpdateLanguageVoiceRequest;

class LanguageVoiceService extends AbstractService
{
    protected $roleRepository;

    public function __construct(LanguageVoiceRepository $repository, LanguageVoiceResponse $response, Request $request)
    {
        $this->repository = $repository;
        $this->response = $response;
        $this->request = $request;
    }

    public function listLanguageVoice($languageId): Collection
    {
        $users = $this->repository->getListWithoutPagination(['language_id' => $languageId]);
        return $users;
    }

    public function showLanguageVoice($languageId, $id): Model
    {
        return $this->getWhere(['id' => $id, 'language_id' => $languageId]);
    }

    public function storeLanguageVoice(StoreLanguageVoiceRequest $request): AbstractResponseInterface
    {
        try {
            DB::beginTransaction();
            $requestData = $request->validated();

            // Careate the languageVoice status
            $this->create($requestData);

            DB::commit();
            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getCreateResponseMessage());
            return $this->response;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
            return $this->response;
        }
    }

    public function updateLanguageVoice(UpdateLanguageVoiceRequest $request, $languageId, $id): AbstractResponseInterface
    {
        try {
            DB::beginTransaction();
            $requestData = $request->validated();

            // Update the languageVoice status
            $this->update($requestData, $id);

            DB::commit();
            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
            return $this->response;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
            return $this->response;
        }
    }

    public function updateLanguageVoiceStatus($data): AbstractResponseInterface
    {
        try {
            DB::beginTransaction();

            // Update the languageVoice status
            $this->update($data, $data['id']);

            DB::commit();
            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
            return $this->response;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
            return $this->response;
        }
    }
}
