<?php

namespace App\Http\Resources\V1\Salario;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SalarioCollection extends ResourceCollection
{
    public $collects = SalarioResource::class;

    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
