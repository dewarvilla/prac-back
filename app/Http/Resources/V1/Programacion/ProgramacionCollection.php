<?php

namespace App\Http\Resources\V1\Programacion;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProgramacionCollection extends ResourceCollection
{
    public $collects = ProgramacionResource::class;

    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
