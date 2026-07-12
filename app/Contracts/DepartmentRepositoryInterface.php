<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * DepartmentRepositoryInterface – contract for department data access.
 *
 * WHY THIS EXISTS:
 *   The Repository Pattern decouples the Service layer from the database.
 *   Services depend on this interface, not the concrete EloquentDepartmentRepository.
 *   This enables:
 *     1. Easy unit testing (swap in an in-memory implementation)
 *     2. Potential database engine changes without touching service code
 *     3. Clear API contract for what operations are supported
 *
 * LARAVEL FEATURE:
 *   Bound to DepartmentRepository in AppServiceProvider::register().
 *   The Service Container resolves DepartmentRepositoryInterface → DepartmentRepository
 *   automatically when injected into constructors.
 *
 * INTERACTION:
 *   DepartmentService depends on this interface.
 *   DepartmentRepository implements it.
 *   AppServiceProvider binds interface → implementation.
 */
interface DepartmentRepositoryInterface
{
    /**
     * Return paginated departments with optional search.
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Return all departments (for dropdowns / select lists).
     *
     * @return Collection<int, Department>
     */
    public function all(): Collection;

    public function findById(string $id): Department;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Department;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Department $department, array $data): Department;

    public function delete(Department $department): bool;
}
