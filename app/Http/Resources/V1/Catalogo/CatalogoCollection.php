<?php

namespace App\Http\Resources\V1\Catalogo;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CatalogoCollection extends ResourceCollection
{
    /**
     * El tipo de recurso individual.
     */
    public $collects = CatalogoResource::class;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
