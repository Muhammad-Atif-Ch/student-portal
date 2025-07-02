<?php

namespace App\Services\Language;

use App\Helpers\ResponseCode;
use App\Responses\UserResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Responses\LanguageResponse;
use App\Core\Services\AbstractService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use App\Repositories\LanguageRepository;
use App\Http\Requests\Language\UpdateLanguageRequest;
use App\Core\Contracts\Responses\AbstractResponseInterface;

class LanguageService extends AbstractService
{
    protected $roleRepository;

    public function __construct(LanguageRepository $repository, LanguageResponse $response, Request $request)
    {
        $this->repository = $repository;
        $this->response = $response;
        $this->request = $request;
    }

    public function listLanguage(): Collection
    {
        $users = $this->repository->getListWithoutPagination();
        return $users;
    }

    public function showLanguage($id): Model
    {
        return $this->getWhere(['id' => $id]);
    }

    public function updateLanguage(UpdateLanguageRequest $request, $id): AbstractResponseInterface
    {
        try {
            DB::beginTransaction();
            $requestData = $request->validated();

            // Update the language status
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

    public function updateLanguageStatus($data): AbstractResponseInterface
    {
        try {
            DB::beginTransaction();

            // Update the language status
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
