<?php

namespace App\Repositories\ApprovalInbox;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ApprovalInboxInterface
{
    public function inbox(User $user, array $filters, int $perPage, array $appends = []): LengthAwarePaginator;
}
