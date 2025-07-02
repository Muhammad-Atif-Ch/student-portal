<?php

namespace App\Repositories;

use App\Models\Language;
use App\Core\Repositories\AbstractRepository;

class LanguageRepository extends AbstractRepository
{
    public function __construct(Language $model)
    {
        $this->model = $model;
    }
}
