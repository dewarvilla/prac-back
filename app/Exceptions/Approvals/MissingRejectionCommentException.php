<?php

namespace App\Exceptions\Approvals;

use App\Exceptions\UnprocessableEntityException;

final class MissingRejectionCommentException extends UnprocessableEntityException
{
    public function __construct(int $stepOrder, string $roleKey)
    {
        parent::__construct(
            'Debes indicar la justificación del rechazo.',
            'APPROVAL_REJECT_COMMENT_REQUIRED',
            ['step_order' => $stepOrder, 'role_key' => $roleKey]
        );
    }
}
