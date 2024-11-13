<?php

namespace App\Repositories;

use App\Core\Repositories\AbstractRepository;
use App\Models\StudentReport;

class ReportRepository extends AbstractRepository
{
    public function __construct(StudentReport $model)
    {
        $this->model = $model;
    }
}
