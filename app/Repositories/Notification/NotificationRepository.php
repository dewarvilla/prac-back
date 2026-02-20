<?php

namespace App\Repositories\Notification;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationRepository implements NotificationInterface
{
    public function paginate(User $user, array $filters, int $perPage, array $appends = []): LengthAwarePaginator
    {
        $onlyUnread = ($filters['unread'] ?? false) === true;

        $q = $onlyUnread
            ? $user->unreadNotifications()
            : $user->notifications();

        $sort = $filters['sort'] ?? '-created_at';
        foreach (explode(',', (string)$sort) as $part) {
            $part = trim($part);
            if ($part === '') continue;

            $dir = str_starts_with($part, '-') ? 'desc' : 'asc';
            $col = ltrim($part, '-');

            if (!in_array($col, ['created_at','read_at'], true)) continue;
            $q->orderBy($col, $dir);
        }

        return $q->paginate($perPage)->appends($appends);
    }

    public function unreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function markRead(User $user, string $notificationId): bool
    {
        $n = $user->notifications()->where('id', $notificationId)->first();
        if (!$n) return false;
        $n->markAsRead();
        return true;
    }

    public function markAllRead(User $user): int
    {
        $unread = $user->unreadNotifications;
        $count = $unread->count();
        $unread->markAsRead();
        return $count;
    }
}
