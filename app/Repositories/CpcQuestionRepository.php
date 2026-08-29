<?php

namespace App\Repositories;

use App\Core\Repositories\AbstractRepository;
use App\Models\CpcQuestion;

class CpcQuestionRepository extends AbstractRepository
{
    public function __construct(CpcQuestion $model)
    {
        $this->model = $model;
    }
}
