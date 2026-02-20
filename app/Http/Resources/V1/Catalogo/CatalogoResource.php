<?php

namespace App\Http\Resources\V1\Catalogo;

use Illuminate\Http\Resources\Json\JsonResource;

class CatalogoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,

            'nivelAcademico'    => $this->nivel_academico,
            'facultad'          => $this->facultad,
            'programaAcademico' => $this->programa_academico,

            'estado' => (bool) $this->estado,

            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}