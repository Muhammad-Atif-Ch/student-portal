<?php

namespace App\Repositories;

use App\Core\Repositories\AbstractRepository;
use App\Models\CpcExam;

class CpcExamRepository extends AbstractRepository
{
    public function __construct(CpcExam $model)
    {
        $this->model = $model;
    }
}
