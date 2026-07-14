<?php

namespace App\Services\User;

use App\Core\Contracts\Responses\AbstractResponseInterface;
use App\Core\Services\AbstractService;
use App\Helpers\ResponseCode;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Responses\UserResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

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

    public function listUser(): LengthAwarePaginator
    {
        $this->setLimit(1);
        return $this->repository->getByCondition([], ['roles', 'membership', 'iosMembership']);
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
