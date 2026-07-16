<?php

namespace App\Repositories;

use App\Core\Repositories\AbstractRepository;
use App\Models\Quiz;
use Illuminate\Database\QueryException;

class QuizRepository extends AbstractRepository
{
    public function __construct(Quiz $model)
    {
        $this->model = $model;
    }

    public function listWithQuestionCount()
    {
        return $this->model
            ->withCount('questions')
            ->orderBy('id', "ASC")
            ->get();
    }

    public function search(array $request, array $with = [], $limit = null)
    {
        try {
            if (! $limit) {
                $limit = $this->limit;
            }

            $sortBy = isset($request['sort_by']) ? $request['sort_by'] : 'id';
            $sortDirection = isset($request['sort_direction']) ? $request['sort_direction'] : $this->order;

            $query = $this->model->with($with)
                ->when(isset($request['name']) && $request['name'] != '', function ($query) use ($request) {
                    $query->where('name', 'ilike', '%'.$request['name'].'%');
                })->when(isset($request['description']) && $request['description'] != '', function ($query) use ($request) {
                    $query->where('description', 'ilike', '%'.$request['description'].'%');
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
