<?php

namespace App\Core\Repositories;

use App\Core\Contracts\Repositories\AbstractRepositoryInterface;
use App\Traits\RepositoryTrait;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Database\QueryException;

abstract class AbstractRepository implements AbstractRepositoryInterface
{

    use RepositoryTrait;

    protected $model;

    protected $limit = 10;

    protected $order = 'DESC';

    protected $pagination = true;

    public function create(array $request): Model
    {
        try {
            return $this->model->create($request);
        } catch (QueryException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function createMany(array $data)
    {
        try {
            return $this->model->insert($data);
        } catch (QueryException $e) {
            throw new \Exception($e->getMessage());
        }
    }


    public function update(int|string $id, array $data): bool
    {
        try {
            return $this->model->findOrFail($id)->update($data);
        } catch (QueryException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getById(int|string $id, array $with = []): Model
    {
        try {
            $data = $this->model->with($with)->findOrFail($id);
        } catch (RelationNotFoundException $e) {
            throw new RelationNotFoundException($e->getMessage());
        }
        if (!$data) {
            throw new ModelNotFoundException('Record not found');
        }

        return $data;
    }

    public function getByCondition(array $conditions, array $with = []): LengthAwarePaginator
    {
        try {
            $query = $this->model->where($conditions)->with($with)->limit($this->limit);
            if ($this->pagination) {
                $data = $query->paginate($this->limit);
            } else {
                $data = $query->get();
            }
        } catch (RelationNotFoundException $e) {
            throw new RelationNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        if (!$data) {
            throw new ModelNotFoundException('Record not found');
        }
        return $data;
    }

    public function getWhere(array $conditions, array $with = []): Model
    {
        try {
            $data = $this->model->where($conditions)->with($with)->first();
        } catch (RelationNotFoundException $e) {
            throw new RelationNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        if (!$data) {
            throw new ModelNotFoundException('Record not found');
        }
        return $data;
    }

    public function getListWithoutPagination(array $conditions = [], array $with = [], $orderBy = 'asc')
    {
        try {
            $query = $this->model->with($with)->where($conditions)->orderBy('id', $orderBy);
            $data = $query->get();
        } catch (RelationNotFoundException $e) {
            throw new RelationNotFoundException($e->getMessage());
        }

        return $data;
    }

    public function getList(array $with = []): LengthAwarePaginator
    {
        try {
            $query = $this->model->with($with)->orderBy('id', 'asc')->limit($this->limit);
            if ($this->pagination) {
                $data = $query->paginate($this->limit);
            } else {
                $data = $query->get();
            }
        } catch (RelationNotFoundException $e) {
            throw new RelationNotFoundException($e->getMessage());
        }

        return $data;
    }

    public function getListWithGroupBy(array $with = [], string $groupBy = null)
    {
        try {
            $query = $this->model->with($with)->orderBy('id', $this->order)->limit($this->limit);

            if ($this->pagination) {
                $data = $query->paginate($this->limit);
            } else {
                $data = $query->get();
            }
            if ($groupBy) {
                $data = $data->groupBy($groupBy);
            }
        } catch (RelationNotFoundException $e) {
            throw new RelationNotFoundException($e->getMessage());
        }

        return $data;
    }

    public function destroy(Model $model): bool
    {
        return $model->delete();
    }

    public function destroyMany($conditions): bool
    {
        return $this->model->where($conditions)->delete();
    }
}
