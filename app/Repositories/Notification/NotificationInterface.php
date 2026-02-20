<?php

namespace App\Repositories\Notification;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationInterface
{
    public function paginate(User $user, array $filters, int $perPage, array $appends = []): LengthAwarePaginator;
    public function unreadCount(User $user): int;
    public function markRead(User $user, string $notificationId): bool;
    public function markAllRead(User $user): int;
}
