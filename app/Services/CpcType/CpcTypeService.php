<?php

namespace App\Services\CpcType;

use App\Core\Contracts\Responses\AbstractResponseInterface;
use App\Core\Services\AbstractService;
use App\Helpers\ResponseCode;
use App\Http\Requests\CpcType\CreateCpcTypeRequest;
use App\Http\Requests\CpcType\UpdateCpcTypeRequest;
use App\Repositories\CpcTypeRepository;
use App\Responses\CpcTypeResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CpcTypeService extends AbstractService
{
    public function __construct(
        CpcTypeRepository $repository,
        CpcTypeResponse $response,
        Request $request,
    ) {
        $this->repository = $repository;
        $this->response = $response;
        $this->request = $request;
    }

    public function createCpcType(CreateCpcTypeRequest $request): AbstractResponseInterface
    {
        $data = $request->validated();

        try {
            $this->create([
                'title' => $data['title'],
            ]);

            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getCreateResponseMessage());
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
        }

        return $this->response;
    }

    public function listCpcTypes(): LengthAwarePaginator
    {
        $this->setLimit(50);

        return $this->repository->getList();
    }

    public function showCpcType($id): Model
    {
        return $this->getById($id);
    }

    public function updateCpcType(UpdateCpcTypeRequest $request, $id): AbstractResponseInterface
    {
        $data = $request->validated();

        try {
            $this->update([
                'title' => $data['title'],
            ], $id);

            $this->response->setResponse(ResponseCode::SUCCESS, ResponseCode::REGULAR, $this->response->getUpdateResponseMessage());
        } catch (\Exception $e) {
            $this->response->setResponse(ResponseCode::ERROR, $e->getCode(), $e->getMessage());
        }

        return $this->response;
    }
}
