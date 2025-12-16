<?php

namespace App\Http\Resources\V1\Catalogo;

use Illuminate\Http\Resources\Json\JsonResource;

class CatalogoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'nivel_academico'     => $this->nivel_academico,
            'facultad'            => $this->facultad,
            'programa_academico'  => $this->programa_academico,
            'fechacreacion'       => $this->fechacreacion ?? null,
            'fechamodificacion'   => $this->fechamodificacion ?? null,
            'usuariocreacion'     => $this->usuariocreacion ?? null,
            'usuariomodificacion' => $this->usuariomodificacion ?? null,
            'ipcreacion'          => $this->ipcreacion ?? null,
            'ipmodificacion'      => $this->ipmodificacion ?? null,
        ];
    }
}
