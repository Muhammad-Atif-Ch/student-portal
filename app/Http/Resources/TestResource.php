<?php

namespace App\Http\Resources;

use App\Http\Resources\people\SupplierResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomResource extends JsonResource
{
    //  protected $resource;

    protected $resourceClass;

    public function __construct(array $resource,  $resourceClass)
    {
        $this->resource = $resource;
        $this->resourceClass = $resourceClass;
    }

    public function toArray($request): array
    {
        return [
            'current_page' => $this['current_page'],
            'data' => $this->resourceClass::collection($this['data']),
            'first_page_url' => $this['first_page_url'],
            'from' => $this['from'],
            'last_page' => $this['last_page'],
            'last_page_url' => $this['last_page_url'],
            'next_page_url' => $this['next_page_url'],
            'path' => $this['path'],
            'per_page' => $this['per_page'],
            'prev_page_url' => $this['prev_page_url'],
            'to' => $this['to'],
            'total' => $this['total'],
        ];
    }
}
