<?php

namespace App\Http\Resources\V1\Catalogo;

use Illuminate\Http\Resources\Json\JsonResource;

class CatalogoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => (string) $this->id,

            'nivelAcademico'    => $this->nivel_academico,
            'facultad'          => $this->facultad,
            'programaAcademico' => $this->programa_academico,

            'estado' => isset($this->estado) ? (bool) $this->estado : null,

            'fechacreacion'       => $this->fechacreacion,
            'fechamodificacion'   => $this->fechamodificacion,
            'usuariocreacion'     => $this->usuariocreacion,
            'usuariomodificacion' => $this->usuariomodificacion,
            'ipcreacion'          => $this->ipcreacion,
            'ipmodificacion'      => $this->ipmodificacion,
        ];
    }
}
