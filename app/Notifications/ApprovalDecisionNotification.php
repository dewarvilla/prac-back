<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApprovalDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $aprobableType,
        public string $aprobableId,
        public string $etapa,
        public string $decision, // 'aprobada'|'rechazada'
        public ?string $justificacion = null,
        public string $titulo = '',
        public array $extra = []
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'approval.decision',
            'aprobable_type' => $this->aprobableType,
            'aprobable_id' => $this->aprobableId,
            'etapa' => $this->etapa,
            'decision' => $this->decision,
            'titulo' => $this->titulo ?: 'Decisión de aprobación',
            'mensaje' => $this->decision === 'aprobada'
                ? 'Tu solicitud fue aprobada.'
                : 'Tu solicitud fue rechazada.',
            'justificacion' => $this->justificacion,
            'extra' => $this->extra,
        ];
    }
}
