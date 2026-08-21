<?php

namespace App\Repositories;

use App\Core\Repositories\AbstractRepository;
use App\Models\ExamTypeTargetType;

class ExamTypeTargetTypeRepository extends AbstractRepository
{
    public function __construct(ExamTypeTargetType $model)
    {
        $this->model = $model;
    }
}
