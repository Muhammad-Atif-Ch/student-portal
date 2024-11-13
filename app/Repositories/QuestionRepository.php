<?php

namespace App\Repositories;

use App\Core\Repositories\AbstractRepository;
use App\Models\Question;

class QuestionRepository extends AbstractRepository
{
    public function __construct(Question $model)
    {
        $this->model = $model;
    }
}
