<?php

namespace App\Http\Resources\V1\Fecha;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FechaCollection extends ResourceCollection
{
    /**
     * El tipo de recurso individual.
     */
    public $collects = FechaResource::class;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
