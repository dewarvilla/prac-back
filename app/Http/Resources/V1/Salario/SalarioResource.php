<?php

namespace App\Http\Resources\V1\Salario;

use Illuminate\Http\Resources\Json\JsonResource;

class SalarioResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'    => (string) $this->id,
            'anio'  => (int) $this->anio,
            'valor' => (string) $this->valor,

            'estado' => isset($this->estado) ? (bool) $this->estado : null,

            'fechacreacion'       => optional($this->fechacreacion)->toIso8601String(),
            'fechamodificacion'   => optional($this->fechamodificacion)->toIso8601String(),
            'usuariocreacion'     => $this->usuariocreacion,
            'usuariomodificacion' => $this->usuariomodificacion,
            'ipcreacion'          => $this->ipcreacion,
            'ipmodificacion'      => $this->ipmodificacion,
        ];
    }
}
