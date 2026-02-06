<?php

namespace App\Http\Resources\V1\Fecha;

use Illuminate\Http\Resources\Json\JsonResource;

class FechaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,
            'estado' => (bool) $this->estado,
            'periodo' => $this->periodo,

            'fechaAperturaPreg' => optional($this->fecha_apertura_preg)->format('Y-m-d'),
            'fechaCierreDocentePreg' => optional($this->fecha_cierre_docente_preg)->format('Y-m-d'),
            'fechaCierreJefeDepart' => optional($this->fecha_cierre_jefe_depart)->format('Y-m-d'),
            'fechaCierreDecano' => optional($this->fecha_cierre_decano)->format('Y-m-d'),

            'fechaAperturaPostg' => optional($this->fecha_apertura_postg)->format('Y-m-d'),
            'fechaCierreDocentePostg' => optional($this->fecha_cierre_docente_postg)->format('Y-m-d'),
            'fechaCierreCoordinadorPostg' => optional($this->fecha_cierre_coordinador_postg)->format('Y-m-d'),
            'fechaCierreJefePostg' => optional($this->fecha_cierre_jefe_postg)->format('Y-m-d'),

            'fechacreacion' => optional($this->fechacreacion)->toIso8601String(),
            'fechamodificacion' => optional($this->fechamodificacion)->toIso8601String(),
            'usuariocreacion' => $this->usuariocreacion,
            'usuariomodificacion' => $this->usuariomodificacion,
            'ipcreacion' => $this->ipcreacion,
            'ipmodificacion' => $this->ipmodificacion,
        ];
    }
}
