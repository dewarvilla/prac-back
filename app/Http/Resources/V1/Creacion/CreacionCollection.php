<?php

namespace App\Http\Resources\V1\Creacion;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CreacionCollection extends ResourceCollection
{
    public $collects = CreacionResource::class;

    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
