<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * LeaveRequestRepositoryInterface – contract for leave request data access.
 */
interface LeaveRequestRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(string $id): LeaveRequest;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): LeaveRequest;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(LeaveRequest $leaveRequest, array $data): LeaveRequest;

    public function approve(LeaveRequest $leaveRequest, User $approver): LeaveRequest;

    public function reject(LeaveRequest $leaveRequest, User $approver): LeaveRequest;

    public function delete(LeaveRequest $leaveRequest): bool;
}
