<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\LeaveRequestRepositoryInterface;
use App\DTO\LeaveRequestData;
use App\Enums\LeaveStatus;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LeaveRequestService
{
    public function __construct(
        private readonly LeaveRequestRepositoryInterface $leaveRequestRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function getPaginated(array $filters = []): LengthAwarePaginator
    {
        return $this->leaveRequestRepository->paginate($filters);
    }

    public function getById(string $id): LeaveRequest
    {
        return $this->leaveRequestRepository->findById($id);
    }

    public function create(LeaveRequestData $data): LeaveRequest
    {
        return $this->leaveRequestRepository->create($data->toArray());
    }

    public function update(LeaveRequest $leaveRequest, array $data): LeaveRequest
    {
        if ($leaveRequest->status !== LeaveStatus::Pending) {
            throw new \DomainException('Only pending leave requests can be updated.');
        }

        return $this->leaveRequestRepository->update($leaveRequest, $data);
    }

    public function approve(LeaveRequest $leaveRequest, User $approver): LeaveRequest
    {
        if ($leaveRequest->status !== LeaveStatus::Pending) {
            throw new \DomainException('Leave request is not pending.');
        }

        return $this->leaveRequestRepository->approve($leaveRequest, $approver);
    }

    public function reject(LeaveRequest $leaveRequest, User $approver): LeaveRequest
    {
        if ($leaveRequest->status !== LeaveStatus::Pending) {
            throw new \DomainException('Leave request is not pending.');
        }

        return $this->leaveRequestRepository->reject($leaveRequest, $approver);
    }

    public function delete(LeaveRequest $leaveRequest): bool
    {
        if ($leaveRequest->status !== LeaveStatus::Pending) {
            throw new \DomainException('Only pending leave requests can be deleted.');
        }

        return $this->leaveRequestRepository->delete($leaveRequest);
    }
}
