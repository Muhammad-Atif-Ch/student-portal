<?php

namespace App\Repositories;

use App\Core\Repositories\AbstractRepository;
use App\Models\TechnicalDictionary;

class TechnicalDictionaryRepository extends AbstractRepository
{
    public function __construct(TechnicalDictionary $model)
    {
        $this->model = $model;
    }
}
