<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Notification\IndexNotificationRequest;
use App\Http\Resources\V1\Notification\NotificationCollection;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $service)
    {
    }

    public function index(IndexNotificationRequest $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $filters = $request->validated();

        $result = $this->service->list($request->user(), $filters, $perPage, $request->query());

        return new NotificationCollection($result);
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'ok'    => true,
            'count' => $this->service->unreadCount($request->user()),
        ]);
    }

    public function markRead(Request $request, string $id)
    {
        $this->service->markRead($request->user(), $id);

        return response()->json(['ok' => true, 'message' => 'Notificación marcada como leída.']);
    }

    public function markAllRead(Request $request)
    {
        $count = $this->service->markAllRead($request->user());

        return response()->json(['ok' => true, 'message' => 'Todas marcadas como leídas.', 'count' => $count]);
    }
}
