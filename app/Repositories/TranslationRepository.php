<?php

namespace App\Repositories;

use App\Core\Repositories\AbstractRepository;
use App\Models\QuestionTranslation;

class TranslationRepository extends AbstractRepository
{
    public function __construct(QuestionTranslation $model)
    {
        $this->model = $model;
    }
}
