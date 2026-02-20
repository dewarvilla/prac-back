<?php

namespace App\Services;

use App\Exceptions\Notifications\NotificationException;
use App\Models\ApprovalRequest;
use App\Models\User;
use App\Notifications\Approval\ApprovalActionRequiredNotification;
use Spatie\Permission\Models\Role;

class ApprovalNotificationService
{
    public function notifyCurrentApprovers(string $approvalRequestId): void
    {
        $req = ApprovalRequest::with(['steps', 'approvable'])->find($approvalRequestId);

        if (!$req) {
            throw new NotificationException(
                message: 'Solicitud no encontrada.',
                errorCode: 'NOTIF_REQUEST_NOT_FOUND',
                statusCode: 404,
                details: ['approval_request_id' => $approvalRequestId]
            );
        }

        if ($req->status !== 'pending' || !$req->is_current) return;

        $current = $req->steps->firstWhere('step_order', (int) $req->current_step_order);
        if (!$current) {
            throw new NotificationException(
                message: 'Paso actual no encontrado.',
                errorCode: 'NOTIF_CURRENT_STEP_MISSING',
                statusCode: 409,
                details: [
                    'approval_request_id' => (string) $req->id,
                    'current_step_order'  => (int) $req->current_step_order,
                ]
            );
        }

        $roleKey = (string) $current->role_key;

        $roles = config("approvals.role_map.$roleKey", []);
        $guard = config('auth.defaults.guard', 'web');

        $usersToNotify = collect();

        foreach ($roles as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', $guard)
                ->first()
                ?? Role::query()->where('name', $roleName)->first();

            if (!$role) continue;

            $usersToNotify = $usersToNotify->merge($role->users()->get());
        }

        if ($usersToNotify->isEmpty()) {
            $perm = "approvals.aprobar.{$roleKey}";
            $usersToNotify = User::permission($perm)->get();
        }

        $usersToNotify = $usersToNotify->unique('id');

        foreach ($usersToNotify as $u) {
            $u->notify(new ApprovalActionRequiredNotification($req));
        }
    }

    public function notifyCreatorStatus(string $approvalRequestId, string $status, ?string $comment = null): void
    {
        $req = ApprovalRequest::with('approvable')->find($approvalRequestId);
        if (!$req) return;

        $creatorId = (int) ($req->requested_by ?? 0);
        if ($creatorId <= 0) return;

        $creator = User::find($creatorId);
        if (!$creator) return;

        $creator->notify(new \App\Notifications\Approval\ApprovalStatusChangedNotification($req, $status, $comment));
    }
}