<?php

namespace App\Repositories\ApprovalInbox;

use App\Models\ApprovalRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Creacion;
use App\Models\Programacion;

class ApprovalInboxRepository implements ApprovalInboxInterface
{
    public function inbox(User $user, array $filters, int $perPage, array $appends = []): LengthAwarePaginator
    {
        $roleMap = config('approvals.role_map', []);

        $canRoleKeys = [];
        foreach ($roleMap as $roleKey => $roles) {
            foreach ($roles as $r) {
                if ($user->hasRole($r)) { $canRoleKeys[] = $roleKey; break; }
            }
        }
        $canRoleKeys = array_values(array_unique($canRoleKeys));

        if (empty($canRoleKeys)) {
            return ApprovalRequest::query()->whereRaw('1=0')->paginate($perPage)->appends($appends);
        }

        $q = ApprovalRequest::query()
            ->where('status', 'pending')
            ->where('is_current', true)
            ->whereHas('steps', function ($qs) use ($canRoleKeys) {
                $qs->whereColumn('approval_steps.step_order', 'approval_requests.current_step_order')
                    ->where('approval_steps.status', 'pending')
                    ->whereIn('approval_steps.role_key', $canRoleKeys);
            })
            ->with([
                'steps',
                'definition',
                'approvable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Creacion::class => ['catalogo'],       
                        Programacion::class => ['creacion.catalogo'], 
                    ]);
                },
            ]);

        return $q->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($appends);
    }
}