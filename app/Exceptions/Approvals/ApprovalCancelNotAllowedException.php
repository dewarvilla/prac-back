<?php

namespace App\Exceptions\Approvals;

use App\Exceptions\ForbiddenException;

final class ApprovalCancelNotAllowedException extends ForbiddenException
{
    public function __construct(string $approvalRequestId, int $userId, ?int $creatorId)
    {
        parent::__construct(
            'No tienes permisos para cancelar esta solicitud.',
            'APPROVAL_CANCEL_NOT_ALLOWED',
            [
                'approval_request_id' => $approvalRequestId,
                'user_id'             => $userId,
                'creator_id'          => $creatorId,
            ]
        );
    }
}
