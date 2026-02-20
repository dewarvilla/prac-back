<?php

namespace App\Exceptions\Approvals;

use App\Exceptions\NotFoundException;

final class ApprovalRequestIdNotFoundException extends NotFoundException
{
    public function __construct(string $approvalRequestId)
    {
        parent::__construct(
            'Solicitud de aprobación no encontrada.',
            'APPROVAL_REQUEST_NOT_FOUND',
            ['approval_request_id' => $approvalRequestId]
        );
    }
}
