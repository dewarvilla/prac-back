<?php

namespace App\Exceptions\Approvals;

use App\Exceptions\UnprocessableEntityException;

class MissingRejectionCommentException extends UnprocessableEntityException
{
    public function __construct()
    {
        parent::__construct(
            'Debes indicar la justificación del rechazo.',
            'APPROVAL_REJECT_COMMENT_REQUIRED',
            []
        );
    }
}
