<?php

namespace App\Repositories;

use App\Models\Test;
use Illuminate\Database\QueryException;
use App\Core\Repositories\AbstractRepository;

class TestRepository extends AbstractRepository
{
    public function __construct(Test $model)
    {
        $this->model = $model;
    }

    public function search(array $request, array $with = [], $limit = null)
    {
        try {
            if (!$limit) {
                $limit = $this->limit;
            }

            $sortBy = isset($request['sort_by']) ? $request['sort_by'] : 'id';
            $sortDirection = isset($request['sort_direction']) ? $request['sort_direction'] : $this->order;

            $query = $this->model->with($with)
                ->when(isset($request['name']) && $request['name'] != '', function ($query) use ($request) {
                    $query->where('name', 'ilike', '%' . $request['name'] . '%');
                })->when(isset($request['description']) && $request['description'] != '', function ($query) use ($request) {
                    $query->where('description', 'ilike', '%' . $request['description'] . '%');
                })->when(isset($request['sort_by']) && $request['sort_by'] != '', function ($query) use ($sortBy, $sortDirection) {
                    $query->orderBy($sortBy, $sortDirection);
                }, function ($query) {
                    $query->orderBy('id', $this->order);
                });

            return $query->paginate($limit);
        } catch (QueryException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
