<?php

namespace App\Repositories;

use App\Core\Repositories\AbstractRepository;
use App\Models\TranslationGlossary;

class TranslationGlossaryRepository extends AbstractRepository
{
    public function __construct(TranslationGlossary $model)
    {
        $this->model = $model;
    }
}
