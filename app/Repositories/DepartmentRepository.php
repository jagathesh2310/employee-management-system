<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\DepartmentRepositoryInterface;
use App\Exceptions\EmployeeNotFoundException;
use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * DepartmentRepository – concrete Eloquent implementation of DepartmentRepositoryInterface.
 *
 * WHY THIS EXISTS:
 *   Centralises all SQL/Eloquent queries for departments.
 *   Services never touch Eloquent directly – they call this repository.
 *   This separation makes it trivial to unit-test services by mocking
 *   this class without hitting the database.
 *
 * LARAVEL FEATURES DEMONSTRATED:
 *   - LengthAwarePaginator: paginate() returns total count (used in API response meta)
 *   - withCount(): adds employees_count column without N+1 query
 *   - search() scope: uses model local scope
 *   - Database transactions: create() and update() wrapped in transactions
 *
 * INTERACTION:
 *   Bound to DepartmentRepositoryInterface in AppServiceProvider.
 *   Injected into DepartmentService via constructor (Dependency Injection).
 */
class DepartmentRepository implements DepartmentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        // withCount(): adds employees_count to each result in a single JOIN query
        // instead of a separate query per department (prevents N+1).
        $query = Department::withCount('employees');

        if (! empty($filters['search'])) {
            // Using model local scope – keeps query logic in the model
            $query->search($filters['search']);
        }

        // Apply sorting
        $sortBy        = in_array($filters['sort_by'] ?? 'name', ['name', 'created_at']) ? ($filters['sort_by'] ?? 'name') : 'name';
        $sortDirection = ($filters['sort_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    public function all(): Collection
    {
        // Simple eager load – no pagination for dropdown selects.
        // orderBy name for consistent dropdown ordering.
        return Department::orderBy('name')->get();
    }

    public function findById(string $id): Department
    {
        try {
            return Department::with([
                // load() pattern: eagerly load employees with their position counts
                'employees' => fn ($q) => $q->withCount('leaveRequests'),
            ])->findOrFail($id);
        } catch (ModelNotFoundException) {
            throw new EmployeeNotFoundException("Department [{$id}] not found.");
        }
    }

    public function create(array $data): Department
    {
        // WHY TRANSACTION:
        // Even for a simple create, wrapping in a transaction ensures atomicity
        // if model events (Observer) trigger additional DB writes.
        return \DB::transaction(fn () => Department::create($data));
    }

    public function update(Department $department, array $data): Department
    {
        return \DB::transaction(function () use ($department, $data) {
            $department->update($data);

            return $department->fresh();
        });
    }

    public function delete(Department $department): bool
    {
        return \DB::transaction(fn () => $department->delete());
    }
}
