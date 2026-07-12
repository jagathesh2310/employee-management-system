<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\LeaveRequestData;
use App\Http\Requests\ApproveLeaveRequestRequest;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Http\Requests\UpdateLeaveRequestRequest;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Services\LeaveRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function __construct(
        private readonly LeaveRequestService $leaveRequestService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $filters = $request->only(['employee_id', 'status', 'leave_type', 'date_from', 'date_to']);

        if (! $request->user()?->isManager() && ! $request->user()?->isAdmin()) {
            $employee = Employee::where('email', $request->user()?->email)->first();
            if ($employee) {
                $filters['employee_id'] = $employee->id;
            } else {
                $leaveRequests = collect();

                return view('leave-requests.index', compact('leaveRequests', 'filters'));
            }
        }

        $leaveRequests = $this->leaveRequestService->getPaginated($filters);
        $employees = Employee::all();

        return view('leave-requests.index', compact('leaveRequests', 'filters', 'employees'));
    }

    public function create(): View
    {
        $this->authorize('create', LeaveRequest::class);
        $employees = Employee::all();

        return view('leave-requests.create', compact('employees'));
    }

    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $data = LeaveRequestData::fromArray($request->validated());
        $this->leaveRequestService->create($data);

        return redirect()->route('leave-requests.index')->with('success', 'Leave request created successfully.');
    }

    public function show(LeaveRequest $leaveRequest): View
    {
        $this->authorize('view', $leaveRequest);
        $leaveRequest = $this->leaveRequestService->getById($leaveRequest->id);

        return view('leave-requests.show', compact('leaveRequest'));
    }

    public function edit(LeaveRequest $leaveRequest): View
    {
        $this->authorize('update', $leaveRequest);
        $leaveRequest = $this->leaveRequestService->getById($leaveRequest->id);
        $employees = Employee::all();

        return view('leave-requests.edit', compact('leaveRequest', 'employees'));
    }

    public function update(UpdateLeaveRequestRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->leaveRequestService->update($leaveRequest, $request->validated());

        return redirect()->route('leave-requests.index')->with('success', 'Leave request updated successfully.');
    }

    public function destroy(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('delete', $leaveRequest);
        $this->leaveRequestService->delete($leaveRequest);

        return redirect()->route('leave-requests.index')->with('success', 'Leave request deleted successfully.');
    }

    public function approve(ApproveLeaveRequestRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->leaveRequestService->approve($leaveRequest, $request->user());

        return redirect()->route('leave-requests.index')->with('success', 'Leave request approved successfully.');
    }

    public function reject(ApproveLeaveRequestRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->leaveRequestService->reject($leaveRequest, $request->user());

        return redirect()->route('leave-requests.index')->with('success', 'Leave request rejected successfully.');
    }
}
