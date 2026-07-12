<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EmployeeRepositoryInterface;
use App\DTO\EmployeeData;
use App\Jobs\GenerateEmployeeReportJob;
use App\Models\Employee;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * EmployeeService – orchestrates employee business logic.
 *
 * NOTE ON EVENTS:
 *   Notice that we DO NOT fire EmployeeCreated/Updated events here.
 *   Why? Because the EmployeeObserver handles that automatically.
 *   This keeps the service clean and ensures events fire even if
 *   an employee is created via Tinker or a background job.
 */
class EmployeeService
{
    public function __construct(
        private readonly EmployeeRepositoryInterface $employeeRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function getPaginated(array $filters = []): LengthAwarePaginator
    {
        return $this->employeeRepository->paginate($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function getCursorPaginated(array $filters = []): CursorPaginator
    {
        return $this->employeeRepository->cursorPaginate($filters);
    }

    public function getById(string $id): Employee
    {
        return $this->employeeRepository->findById($id);
    }

    public function create(EmployeeData $data): Employee
    {
        // Business logic check: cannot assign manager from a different department
        // (Simplified example; in reality you might need a cross-department manager flag)
        if ($data->managerId) {
            $manager = $this->employeeRepository->findById($data->managerId);
            if ($manager->department_id !== $data->departmentId) {
                // Warning only, or throw exception based on business rules
                \Log::warning("Assigning manager from different department for new employee {$data->employeeId}");
            }
        }

        return $this->employeeRepository->create($data->toArray());
    }

    public function update(Employee $employee, array $data): Employee
    {
        // DTOs for updates can be complex with PATCH semantics (optional fields).
        // For simplicity, we accept a validated array here, but a dedicated UpdateEmployeeData DTO
        // is best practice for larger apps.
        return $this->employeeRepository->update($employee, $data);
    }

    public function delete(Employee $employee): bool
    {
        // Business rule: Cannot delete an employee who manages others
        if ($employee->subordinates()->exists()) {
            throw new \DomainException('Cannot delete an employee who has direct reports. Reassign them first.');
        }

        return $this->employeeRepository->delete($employee);
    }

    /**
     * @return Collection<int, Employee>
     */
    public function getDepartmentStats(): Collection
    {
        return $this->employeeRepository->getDepartmentStats();
    }

    /**
     * Dispatch a background job to generate a CSV report.
     */
    public function queueReportGeneration(): void
    {
        GenerateEmployeeReportJob::dispatch();
    }
}
