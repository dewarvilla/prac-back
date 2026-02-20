<?php

namespace App\Services;

use App\Exceptions\Notifications\NotificationException;
use App\Models\User;
use App\Repositories\Notification\NotificationInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationService
{
    public function __construct(private readonly NotificationInterface $repo) {}

    public function list(User $user, array $filters, int $perPage, array $appends = []): LengthAwarePaginator
    {
        return $this->repo->paginate($user, $filters, $perPage, $appends);
    }

    public function unreadCount(User $user): int
    {
        return $this->repo->unreadCount($user);
    }

    public function markRead(User $user, string $notificationId): void
    {
        if (!$this->repo->markRead($user, $notificationId)) {
            throw new NotificationException(
                message: 'Notificación no encontrada.',
                errorCode: 'NOTIFICATION_NOT_FOUND',
                statusCode: 404,
            );
        }
    }

    public function markAllRead(User $user): int
    {
        return $this->repo->markAllRead($user);
    }
}
