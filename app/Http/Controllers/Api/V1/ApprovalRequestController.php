<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Approval\ActApprovalRequest;
use App\Http\Resources\V1\Approval\ApprovalRequestResource;
use App\Models\ApprovalRequest;
use App\Services\ApprovalService;

class ApprovalRequestController extends Controller
{
    public function __construct(private readonly ApprovalService $service)
    {
        $this->middleware('permission:approvals.view')->only(['show']);
        $this->middleware('permission:approvals.act')->only(['approve','reject','cancel']);
    }

    public function show(ApprovalRequest $approvalRequest)
    {
        return new ApprovalRequestResource(
            $approvalRequest->load(['definition','steps'])
        );
    }

    public function approve(ActApprovalRequest $request, ApprovalRequest $approvalRequest)
    {
        $updated = $this->service->approve(
            $approvalRequest->id,
            $request->user(),
            $request->validated()['comment'] ?? null,
            $request->ip()
        );

        return new ApprovalRequestResource($updated);
    }

    public function reject(ActApprovalRequest $request, ApprovalRequest $approvalRequest)
    {
        $updated = $this->service->reject(
            $approvalRequest->id,
            $request->user(),
            $request->validated()['comment'] ?? null,
            $request->ip()
        );

        return new ApprovalRequestResource($updated);
    }

    public function cancel(ActApprovalRequest $request, ApprovalRequest $approvalRequest)
    {
        $updated = $this->service->cancel(
            $approvalRequest->id,
            $request->user(),
            $request->ip()
        );

        return new ApprovalRequestResource($updated);
    }
}
