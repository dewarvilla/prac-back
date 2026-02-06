<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApprovalPendingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $aprobableType,
        public string $aprobableId,
        public string $etapa,
        public string $titulo,
        public array $extra = []
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'approval.pending',
            'aprobable_type' => $this->aprobableType,
            'aprobable_id' => $this->aprobableId,
            'etapa' => $this->etapa,
            'titulo' => $this->titulo,
            'mensaje' => 'Tienes una solicitud pendiente de aprobación.',
            'extra' => $this->extra,
        ];
    }
}
