<?php

namespace App\Exceptions\Approvals;

use App\Exceptions\ConflictException;

class ApprovalAlreadyFinalizedException extends ConflictException
{
    public function __construct(string $status)
    {
        parent::__construct(
            'La aprobación ya fue finalizada y no admite más decisiones.',
            'APPROVAL_ALREADY_FINALIZED',
            ['status' => $status]
        );
    }
}
