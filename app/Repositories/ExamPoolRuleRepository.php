<?php

namespace App\Repositories;

use App\Core\Repositories\AbstractRepository;
use App\Models\ExamPoolRule;

class ExamPoolRuleRepository extends AbstractRepository
{
    public function __construct(ExamPoolRule $model)
    {
        $this->model = $model;
    }
}
