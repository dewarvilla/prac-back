<?php

namespace App\Http\Resources\V1\Approval;

use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,
            'status' => (string) $this->status,
            'currentStepOrder' => (int) $this->current_step_order,
            'isCurrent' => (bool) $this->is_current,

            'requestedBy' => $this->requested_by ? (int) $this->requested_by : null,
            'closedAt' => optional($this->closed_at)->toIso8601String(),

            'createdAt' => optional($this->created_at)->toIso8601String(),
            'updatedAt' => optional($this->updated_at)->toIso8601String(),

            'definition' => $this->whenLoaded('definition', function () {
                return [
                    'id' => (string) $this->definition->id,
                    'code' => (string) $this->definition->code,
                    'name' => (string) $this->definition->name,
                    'isActive' => (bool) $this->definition->is_active,
                ];
            }),

            'approvable' => [
                'type' => (string) $this->approvable_type,
                'id' => (string) $this->approvable_id,
            ],

            'steps' => $this->whenLoaded('steps', function () {
                return $this->steps->map(fn ($s) => [
                    'id' => (string) $s->id,
                    'stepOrder' => (int) $s->step_order,
                    'roleKey' => (string) $s->role_key,
                    'status' => (string) $s->status,
                    'actedBy' => $s->acted_by ? (int) $s->acted_by : null,
                    'actedAt' => optional($s->acted_at)->toIso8601String(),
                    'comment' => $s->comment,
                    'createdAt' => optional($s->created_at)->toIso8601String(),
                    'updatedAt' => optional($s->updated_at)->toIso8601String(),
                ])->values();
            }),
        ];
    }
}