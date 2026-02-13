<?php

namespace App\Http\Resources\V1\Approval;

use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,
            'status' => $this->status,
            'currentStepOrder' => (int) $this->current_step_order,
            'isActive' => ((int) $this->active_key === 1),

            'definition' => $this->whenLoaded('definition', function () {
                return [
                    'id' => (string) $this->definition->id,
                    'code' => $this->definition->code,
                    'name' => $this->definition->name,
                ];
            }),

            'approvable' => [
                'type' => $this->approvable_type,
                'id' => (string) $this->approvable_id,
            ],

            'steps' => $this->whenLoaded('steps', function () {
                return $this->steps->map(fn($s) => [
                    'stepOrder' => (int) $s->step_order,
                    'roleKey' => $s->role_key,
                    'status' => $s->status,
                    'actedBy' => $s->acted_by,
                    'actedAt' => optional($s->acted_at)->toIso8601String(),
                    'comment' => $s->comment,
                ])->values();
            }),

            'fechacreacion' => optional($this->fechacreacion)->toIso8601String(),
            'fechamodificacion' => optional($this->fechamodificacion)->toIso8601String(),
        ];
    }
}
