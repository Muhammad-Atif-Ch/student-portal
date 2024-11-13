<?php

namespace App\Core\Contracts\Services;

use Illuminate\Database\Eloquent\Model;

interface AbstractServiceInterface
{

    public function create(array $request);
    public function list(array $with = [], $limit = null);
    public function destroy(Model $model);

    public function setPagination(bool $paginate): void;
    public function setOrder(string $order): void;
    public function setLimit(int $limit): void;
}
