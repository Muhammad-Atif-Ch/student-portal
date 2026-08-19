<?php

namespace App\Repositories;

use App\Core\Repositories\AbstractRepository;
use App\Models\ExamType;

class ExamTypeRepository extends AbstractRepository
{
    public function __construct(ExamType $model)
    {
        $this->model = $model;
    }
}
