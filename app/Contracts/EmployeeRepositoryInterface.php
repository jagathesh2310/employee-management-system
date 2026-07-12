<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * EmployeeRepositoryInterface – contract for employee data access.
 *
 * Defines operations covering the full range of Eloquent query patterns
 * demonstrated in EmployeeRepository:
 *   - Paginate (LengthAwarePaginator, SimplePaginator, CursorPaginator)
 *   - Search and filter
 *   - Eager loading with relationships
 *   - Chunk and chunckById for bulk operations
 *   - Aggregate queries (withCount, withAvg, withSum)
 */
interface EmployeeRepositoryInterface
{
    /**
     * Length-aware paginated list with filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Cursor-based paginated list (infinite scroll / API next-page token).
     *
     * @param  array<string, mixed>  $filters
     */
    public function cursorPaginate(array $filters = [], int $perPage = 15): CursorPaginator;

    public function findById(string $id): Employee;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Employee;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Employee $employee, array $data): Employee;

    public function delete(Employee $employee): bool;

    /**
     * Get department summary statistics.
     *
     * @return Collection<int, Employee>
     */
    public function getDepartmentStats(): Collection;

    /**
     * Generate a report by iterating in chunks (avoids memory exhaustion).
     *
     * DEMONSTRATES: chunk() and chunkById() patterns.
     */
    public function chunkForReport(int $chunkSize, callable $callback): void;
}
