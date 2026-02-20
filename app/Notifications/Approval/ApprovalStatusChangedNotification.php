<?php

namespace App\Notifications\Approval;

use App\Models\ApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApprovalStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ApprovalRequest $req,
        private readonly string $status,
        private readonly ?string $comment = null,
    ) {}

    public function via($notifiable): array
    {
        return config('approvals.notifications.channels', ['database']);
    }

    public function toDatabase($notifiable): array
    {
        return [
            'kind' => 'approval_status_changed',
            'title' => 'Actualización de solicitud',
            'message' => "Tu solicitud cambió a estado: {$this->status}.",
            'approval_request_id' => (string) $this->req->id,
            'approvable_type' => $this->req->approvable_type,
            'approvable_id' => (string) $this->req->approvable_id,
            'status' => $this->status,
            'comment' => $this->comment,
        ];
    }
}
