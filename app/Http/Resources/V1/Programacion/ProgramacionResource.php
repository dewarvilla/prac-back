<?php

namespace App\Http\Resources\V1\Programacion;

use Illuminate\Http\Resources\Json\JsonResource;

class ProgramacionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                 => (string) $this->id,
            'nombrePractica'     => $this->nombre_practica,
            'descripcion'        => $this->descripcion,
            'lugarDeRealizacion' => $this->lugar_de_realizacion,
            'justificacion'      => $this->justificacion,
            'recursosNecesarios' => $this->recursos_necesarios,

            'estadoPractica'     => $this->estado_practica,
            'estadoDepart'       => $this->estado_depart,
            'estadoPostg'        => $this->estado_postg,
            'estadoDecano'       => $this->estado_decano,
            'estadoJefePostg'    => $this->estado_jefe_postg,
            'estadoVice'         => $this->estado_vice,

            'fechaInicio'        => optional($this->fecha_inicio)->format('Y-m-d'),
            'fechaFinalizacion'  => optional($this->fecha_finalizacion)->format('Y-m-d'),

            'creacionId'         => (string) $this->creacion_id,

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
