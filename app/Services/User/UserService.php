<?php

namespace App\Services\User;

use App\Helpers\ResponseCode;
use App\Responses\UserResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Core\Services\AbstractService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Core\Contracts\Responses\AbstractResponseInterface;

class UserService extends AbstractService
{
    protected $roleRepository;

    public function __construct(UserRepository $repository, RoleRepository $roleRepository, UserResponse $response, Request $request)
    {
        $this->repository = $repository;
        $this->roleRepository = $roleRepository;
        $this->response = $response;
        $this->request = $request;
    }

    public function createUserData()
    {
        return [
            'roles' => $this->roleRepository->getListWithoutPagination(),
        ];
    }

    public function createUser(CreateUserRequest $request): AbstractResponseInterface
    {
        try {
            DB::beginTransaction();
            $requestData = $request->validated();
            $user = $this->create($request->validated());
            // Assign role to user
            if (isset($requestData['role_id'])) {
                $user->roles()->attach($requestData['role_id']);
            }
            DB::commit();
            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getCreateResponseMessage());
            return $this->response;
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
            return $this->response;
        }
    }

    public function listUser(): Collection
    {
        $users = $this->repository->getListWithoutPagination(with: ['roles']);
        return $users;
    }

    public function showUser($id): Model
    {
        return $this->getById($id);
    }

    public function updateUser(UpdateUserRequest $request, $id): AbstractResponseInterface
    {
        try {
            DB::beginTransaction();
            $requestData = $request->validated();
            $user = $this->update($requestData, $id);
            // Assign role to user
            if (isset($requestData['role_id'])) {
                $user->roles()->sync($requestData['role_id']);
            }
            DB::commit();
            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
            return $this->response;
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
            return $this->response;
        }
    }

}
