<?php

namespace App\Exceptions\Approvals;

use App\Exceptions\ConflictException;

final class ApprovalNotActionableException extends ConflictException
{
    public function __construct(string $approvalRequestId, string $status, $activeKey)
    {
        parent::__construct(
            'La solicitud no está pendiente o no está activa.',
            'APPROVAL_NOT_ACTIONABLE',
            [
                'approval_request_id' => $approvalRequestId,
                'status'              => $status,
                'active_key'          => $activeKey,
            ]
        );
    }
}
