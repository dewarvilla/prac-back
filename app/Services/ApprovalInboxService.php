<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\ApprovalInbox\ApprovalInboxInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApprovalInboxService
{
    public function __construct(private readonly ApprovalInboxInterface $repo) {}

    public function inbox(User $user, array $filters, int $perPage, array $appends = []): LengthAwarePaginator
    {
        return $this->repo->inbox($user, $filters, $perPage, $appends);
    }
}
