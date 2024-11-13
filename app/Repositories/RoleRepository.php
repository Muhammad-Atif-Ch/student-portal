<?php

namespace App\Repositories;

use App\Core\Repositories\AbstractRepository;
use Spatie\Permission\Models\Role;

class RoleRepository extends AbstractRepository
{
    public function __construct(Role $model)
    {
        $this->model = $model;
    }
}
