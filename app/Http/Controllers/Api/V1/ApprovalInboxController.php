<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ApprovalInboxService;
use Illuminate\Http\Request;

class ApprovalInboxController extends Controller
{
    public function __construct(private readonly ApprovalInboxService $service) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);

        $result = $this->service->inbox(
            $request->user(),
            [],
            $perPage,
            $request->query()
        );

        if (is_array($result) && array_key_exists('data', $result)) {
            return $this->ok(
                data: $result['data'] ?? [],
                message: 'OK',
                status: 200,
                meta: $result['meta'] ?? []
            );
        }

        return $this->ok(
            data: $result,
            message: 'OK',
            status: 200
        );
    }
}
