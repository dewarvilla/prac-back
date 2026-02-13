<?php

namespace App\Http\Resources\V1\Programacion;

use Illuminate\Http\Resources\Json\JsonResource;

class ProgramacionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                 => (string) $this->id,
            'creacionId'         => (string) $this->creacion_id,

            'nombrePractica'     => $this->nombre_practica,
            'descripcion'        => $this->descripcion,
            'lugarDeRealizacion' => $this->lugar_de_realizacion,
            'justificacion'      => $this->justificacion,
            'recursosNecesarios' => $this->recursos_necesarios,

            'nivelFormacion'     => $this->nivel_formacion,
            'estadoPractica'     => $this->estado_practica,

            'fechaInicio'        => optional($this->fecha_inicio)->format('Y-m-d'),
            'fechaFinalizacion'  => optional($this->fecha_finalizacion)->format('Y-m-d'),

            'requiereTransporte' => (bool) $this->requiere_transporte,
            'numeroEstudiantes'  => (int) $this->numero_estudiantes,

            'fechacreacion'       => optional($this->fechacreacion)->toIso8601String(),
            'fechamodificacion'   => optional($this->fechamodificacion)->toIso8601String(),
            'usuariocreacion'     => $this->usuariocreacion,
            'usuariomodificacion' => $this->usuariomodificacion,
            'ipcreacion'          => $this->ipcreacion,
            'ipmodificacion'      => $this->ipmodificacion,
        ];
    }
}
