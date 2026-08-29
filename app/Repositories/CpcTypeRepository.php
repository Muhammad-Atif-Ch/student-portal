<?php

namespace App\Repositories;

use App\Core\Repositories\AbstractRepository;
use App\Models\CpcType;

class CpcTypeRepository extends AbstractRepository
{
    public function __construct(CpcType $model)
    {
        $this->model = $model;
    }
}
