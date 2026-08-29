<?php

namespace App\Repositories;

use App\Core\Repositories\AbstractRepository;
use App\Models\CpcCaseStudy;

class CpcCaseStudyRepository extends AbstractRepository
{
    public function __construct(CpcCaseStudy $model)
    {
        $this->model = $model;
    }
}
