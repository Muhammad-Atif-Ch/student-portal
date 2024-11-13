<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaginationResource extends JsonResource
{

    public $resource;
    public $data;


    public function __construct($resource, $data)
    {
        $this->resource = $resource;
        $this->data = $data;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'current_page'                =>     $this->data['current_page'],
            'data'                        =>     $this->resource,
            'first_page_url'              =>     $this->data['first_page_url'],
            'from'                        =>     $this->data['from'],
            'last_page'                   =>     $this->data['last_page'],
            'last_page_url'               =>     $this->data['last_page_url'],
            'next_page_url'               =>     $this->data['next_page_url'],
            'path'                        =>     $this->data['path'],
            'per_page'                    =>     $this->data['per_page'],
            'prev_page_url'               =>     $this->data['prev_page_url'],
            'to'                          =>     $this->data['to'],
            'total'                       =>     $this->data['total'],
        ];
    }
}
