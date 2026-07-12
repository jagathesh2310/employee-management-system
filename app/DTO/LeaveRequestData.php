<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enums\LeaveType;

/**
 * LeaveRequestData DTO – type-safe leave request input.
 */
final class LeaveRequestData
{
    public function __construct(
        public readonly string $employeeId,
        public readonly LeaveType $leaveType,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly ?string $reason,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            employeeId: $validated['employee_id'],
            leaveType: LeaveType::from($validated['leave_type']),
            startDate: $validated['start_date'],
            endDate: $validated['end_date'],
            reason: $validated['reason'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'employee_id' => $this->employeeId,
            'leave_type' => $this->leaveType->value,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'reason' => $this->reason,
            'status' => 'pending', // Always starts as pending
        ];
    }
}
