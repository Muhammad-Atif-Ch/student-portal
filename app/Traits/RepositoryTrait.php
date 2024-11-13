<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait RepositoryTrait {

    public function setPagination(bool $paginate){
        $this->pagination = $paginate;
    }

    public function setOrder(string $order){
        $this->order = $order;
    }

    public function setLimit(int $limit){
        $this->limit = $limit;
    }

    public function startTransaction(){
        DB::startTransaction();
    }

    public function endTransaction(){
        DB::commit();
    }

    public function revertTransaction(){
        DB::rollBack();
    }

    public function beginTransaction(){
        DB::beginTransaction();
    }
}
