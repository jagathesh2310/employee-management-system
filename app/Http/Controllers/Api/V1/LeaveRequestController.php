<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTO\LeaveRequestData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveLeaveRequestRequest;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Http\Requests\UpdateLeaveRequestRequest;
use App\Http\Resources\LeaveRequestCollection;
use App\Http\Resources\LeaveRequestResource;
use App\Models\LeaveRequest;
use App\Services\LeaveRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class LeaveRequestController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly LeaveRequestService $leaveRequestService,
    ) {}

    public static function middleware(): array
    {
        return [];
    }

    public function index(Request $request): LeaveRequestCollection
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $filters = $request->only(['employee_id', 'status', 'leave_type', 'date_from', 'date_to']);
        
        // If regular employee, only show their own requests
        if (! $request->user()?->isManager() && ! $request->user()?->isAdmin()) {
            // Find employee record for this user (in a real app, user <-> employee relation exists)
            // For this demo, we assume the user email matches employee email
            $employee = \App\Models\Employee::where('email', $request->user()?->email)->first();
            if ($employee) {
                $filters['employee_id'] = $employee->id;
            } else {
                // If no linked employee, return empty result
                return new LeaveRequestCollection(collect());
            }
        }

        $leaveRequests = $this->leaveRequestService->getPaginated($filters);

        return new LeaveRequestCollection($leaveRequests);
    }

    public function store(StoreLeaveRequestRequest $request): JsonResponse
    {
        $data = LeaveRequestData::fromArray($request->validated());
        $leaveRequest = $this->leaveRequestService->create($data);

        return response()->json(new LeaveRequestResource($leaveRequest), 201);
    }

    public function show(LeaveRequest $leaveRequest): LeaveRequestResource
    {
        $this->authorize('view', $leaveRequest);
        
        $leaveRequest = $this->leaveRequestService->getById($leaveRequest->id);

        return new LeaveRequestResource($leaveRequest);
    }

    public function update(UpdateLeaveRequestRequest $request, LeaveRequest $leaveRequest): LeaveRequestResource
    {
        $updated = $this->leaveRequestService->update($leaveRequest, $request->validated());

        return new LeaveRequestResource($updated);
    }

    public function destroy(LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('delete', $leaveRequest);
        
        $this->leaveRequestService->delete($leaveRequest);

        return response()->json(null, 204);
    }

    /**
     * Approve a leave request.
     */
    public function approve(ApproveLeaveRequestRequest $request, LeaveRequest $leaveRequest): LeaveRequestResource
    {
        $approved = $this->leaveRequestService->approve($leaveRequest, $request->user());

        return new LeaveRequestResource($approved);
    }

    /**
     * Reject a leave request.
     */
    public function reject(ApproveLeaveRequestRequest $request, LeaveRequest $leaveRequest): LeaveRequestResource
    {
        // We reuse ApproveLeaveRequestRequest because the authorization rules are the same
        $rejected = $this->leaveRequestService->reject($leaveRequest, $request->user());

        return new LeaveRequestResource($rejected);
    }
}
