<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\DepartmentRepositoryInterface;
use App\DTO\DepartmentData;
use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * DepartmentService – business logic layer for departments.
 *
 * WHY SERVICES:
 *   Controllers should only handle HTTP concerns (request/response, status codes).
 *   Repositories should only handle data access (SQL/Eloquent).
 *   Services sit between them, orchestrating business logic, firing events,
 *   and interacting with external APIs if necessary.
 *
 * LARAVEL FEATURE:
 *   Dependency Injection (DI): DepartmentRepositoryInterface is automatically
 *   resolved and injected by the Service Container.
 */
class DepartmentService
{
    public function __construct(
        private readonly DepartmentRepositoryInterface $departmentRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function getPaginated(array $filters = []): LengthAwarePaginator
    {
        return $this->departmentRepository->paginate($filters);
    }

    /**
     * @return Collection<int, Department>
     */
    public function getAll(): Collection
    {
        return $this->departmentRepository->all();
    }

    public function getById(string $id): Department
    {
        return $this->departmentRepository->findById($id);
    }

    public function create(DepartmentData $data): Department
    {
        // Business logic (if any) would go here before calling the repository.
        // E.g., external API validation, firing custom "before" events.

        return $this->departmentRepository->create($data->toArray());
    }

    public function update(Department $department, DepartmentData $data): Department
    {
        return $this->departmentRepository->update($department, $data->toArray());
    }

    public function delete(Department $department): bool
    {
        // Cannot delete a department if it has employees
        // (Using withCount from repository ensures employees_count is available if loaded,
        // but it's safer to run a fresh count query here to enforce the business rule).
        if ($department->employees()->count() > 0) {
            throw new \DomainException('Cannot delete a department that has employees.');
        }

        return $this->departmentRepository->delete($department);
    }
}
