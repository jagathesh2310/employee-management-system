<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enums\EmployeeStatus;

/**
 * EmployeeData DTO – represents all writable employee fields.
 *
 * PHP 8 FEATURES:
 *   - Constructor property promotion (all properties declared in __construct)
 *   - Readonly properties (immutable after creation)
 *   - Named arguments (used in fromArray)
 *   - Nullable types for optional fields
 */
final class EmployeeData
{
    public function __construct(
        public readonly string $employeeId,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly ?string $phone,
        public readonly ?string $gender,
        public readonly ?string $dateOfBirth,
        public readonly string $joiningDate,
        public readonly float $salary,
        public readonly EmployeeStatus $status,
        public readonly string $departmentId,
        public readonly string $positionId,
        public readonly ?string $managerId,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            employeeId: $validated['employee_id'],
            firstName: $validated['first_name'],
            lastName: $validated['last_name'],
            email: $validated['email'],
            phone: $validated['phone'] ?? null,
            gender: $validated['gender'] ?? null,
            dateOfBirth: $validated['date_of_birth'] ?? null,
            joiningDate: $validated['joining_date'],
            salary: (float) $validated['salary'],
            status: EmployeeStatus::from($validated['status'] ?? EmployeeStatus::Active->value),
            departmentId: $validated['department_id'],
            positionId: $validated['position_id'],
            managerId: $validated['manager_id'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'employee_id' => $this->employeeId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'date_of_birth' => $this->dateOfBirth,
            'joining_date' => $this->joiningDate,
            'salary' => $this->salary,
            'status' => $this->status->value,
            'department_id' => $this->departmentId,
            'position_id' => $this->positionId,
            'manager_id' => $this->managerId,
        ];
    }
}
