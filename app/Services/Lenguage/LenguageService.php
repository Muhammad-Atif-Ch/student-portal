<?php

namespace App\Services\Lenguage;

use App\Helpers\ResponseCode;
use App\Responses\UserResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Core\Services\AbstractService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use App\Repositories\LenguageRepository;
use App\Http\Requests\Lenguage\UpdateLenguageRequest;
use App\Core\Contracts\Responses\AbstractResponseInterface;

class LenguageService extends AbstractService
{
    protected $roleRepository;

    public function __construct(LenguageRepository $repository, UserResponse $response, Request $request)
    {
        $this->repository = $repository;
        $this->response = $response;
        $this->request = $request;
    }

    public function listLenguage(): Collection
    {
        $users = $this->repository->getListWithoutPagination();
        return $users;
    }

    public function showUser($id): Model
    {
        return $this->getById($id);
    }

    public function updateLenguage(UpdateLenguageRequest $request, $id): AbstractResponseInterface
    {
        try {
            DB::beginTransaction();
            $requestData = $request->validated();
            $this->update($requestData, $id);

            DB::commit();
            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
            return $this->response;
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
            return $this->response;
        }
    }

}
