<?php

namespace App\Http\Resources\V1\Creacion;

use Illuminate\Http\Resources\Json\JsonResource;

class CreacionResource extends JsonResource
{
    public function toArray($request): array
    {
        $labels = [
            'comite_acreditacion' => 'Comité de Acreditación',
            'consejo_facultad'    => 'Consejo de Facultad',
            'consejo_academico'   => 'Consejo Académico',
        ];

        $ar = $this->relationLoaded('currentApprovalRequest')
            ? $this->currentApprovalRequest
            : null;

        return [
            'id'         => (string) $this->id,
            'catalogoId' => (string) $this->catalogo_id,

            'nombrePractica'     => $this->nombre_practica,
            'recursosNecesarios' => $this->recursos_necesarios,
            'justificacion'      => $this->justificacion,

            'estadoCreacion' => $this->estado_creacion,
            'estado'         => (bool) $this->estado,

            'estadoFlujo' => $ar ? ($labels[$ar->current_role_key] ?? $ar->current_role_key) : null,

            'approval' => $ar ? [
                'id'               => (string) $ar->id,
                'status'           => $ar->status,
                'currentRoleKey'   => $ar->current_role_key,
                'currentRoleLabel' => $labels[$ar->current_role_key] ?? $ar->current_role_key,
            ] : null,

            'isEditable'  => $this->estado_creacion === 'rechazada',
            'isDeletable' => $this->estado_creacion === 'rechazada',

            'programaAcademico' => $this->whenLoaded(
                'catalogo',
                fn () => $this->catalogo?->programa_academico
            ),

            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}