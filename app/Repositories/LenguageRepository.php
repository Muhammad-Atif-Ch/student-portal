<?php

namespace App\Repositories;

use App\Models\Lenguage;
use App\Core\Repositories\AbstractRepository;

class LenguageRepository extends AbstractRepository
{
    public function __construct(Lenguage $model)
    {
        $this->model = $model;
    }
}
