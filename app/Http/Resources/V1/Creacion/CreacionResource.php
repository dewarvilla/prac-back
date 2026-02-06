<?php

namespace App\Http\Resources\V1\Creacion;

use Illuminate\Http\Resources\Json\JsonResource;

class CreacionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,

            'catalogoId' => (string) $this->catalogo_id,

            'nombrePractica'     => $this->nombre_practica,
            'recursosNecesarios' => $this->recursos_necesarios,
            'justificacion'      => $this->justificacion,

            'estadoCreacion' => $this->estado_creacion,
            'estadoFlujo'    => $this->estado_flujo,
        
            'programaAcademico' => $this->whenLoaded('catalogo', fn () => $this->catalogo?->programa_academico),

            'fechacreacion'       => $this->fechacreacion,
            'fechamodificacion'   => $this->fechamodificacion,
            'usuariocreacion'     => $this->usuariocreacion,
            'usuariomodificacion' => $this->usuariomodificacion,
            'ipcreacion'          => $this->ipcreacion,
            'ipmodificacion'      => $this->ipmodificacion,
        ];
    }
}
