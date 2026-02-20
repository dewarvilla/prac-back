<?php

namespace App\Notifications\Approval;

use App\Models\ApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApprovalActionRequiredNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly ApprovalRequest $req) {}

    public function via($notifiable): array
    {
        return config('approvals.notifications.channels', ['database']);
    }

    public function toDatabase($notifiable): array
    {
        $approvalId   = (string) $this->req->id;
        $approvableId = (string) $this->req->approvable_id;

        $url = "/pages/auditoria/aprobaciones?approval={$approvalId}&focus={$approvableId}";

        return [
            'kind' => 'approval_action_required',
            'title' => 'Solicitud pendiente',
            'message' => 'Tienes una solicitud pendiente para aprobar o rechazar.',
            'approval_request_id' => $approvalId,
            'approvable_type' => $this->req->approvable_type,
            'approvable_id' => $approvableId,
            'current_step_order' => (int) $this->req->current_step_order,
            'url' => $url,
        ];
    }
}