<?php

namespace App\Exceptions\Approvals;

use App\Exceptions\ConflictException;

class ApprovalWrongStepException extends ConflictException
{
    public function __construct(int $expected, int $given)
    {
        parent::__construct(
            'La solicitud no está en el paso indicado.',
            'APPROVAL_WRONG_STEP',
            ['expected_step' => $expected, 'given_step' => $given]
        );
    }
}
