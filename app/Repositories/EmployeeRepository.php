<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\EmployeeRepositoryInterface;
use App\Models\Employee;
use App\Models\Scopes\ActiveEmployeeScope;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * EmployeeRepository – the most feature-rich repository in the system.
 *
 * WHY THIS EXISTS:
 *   Centralises ALL Eloquent queries for employees, demonstrating the full
 *   range of Laravel query builder capabilities.
 *
 * LARAVEL FEATURES DEMONSTRATED:
 *   - with() / load() / loadMissing(): eager loading strategies
 *   - whereHas() / whereRelation(): relationship constraint queries
 *   - withCount() / withExists() / withAvg() / withSum(): aggregate sub-queries
 *   - withoutGlobalScope(): bypass the ActiveEmployeeScope global scope
 *   - chunk() / chunkById(): process large datasets in memory-safe batches
 *   - cursor(): LazyCollection for memory-efficient iteration
 *   - paginate() / cursorPaginate(): different pagination strategies
 *
 * BEST PRACTICE:
 *   - Always whitelist sortable columns to prevent SQL injection via sort_by params
 *   - Use chunkById() instead of chunk() when ordering matters (chunk() with ORDER BY
 *     can cause records to be skipped)
 */
class EmployeeRepository implements EmployeeRepositoryInterface
{
    /**
     * Whitelisted sortable columns – prevents ORDER BY injection.
     *
     * @var array<string>
     */
    private const SORTABLE_COLUMNS = ['first_name', 'last_name', 'joining_date', 'salary', 'created_at'];

    public function all(): Collection
    {
        return Employee::all();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        // WHY withoutGlobalScope:
        // The list API can filter by status (including inactive/resigned),
        // so we bypass the ActiveEmployeeScope and filter manually.
        $query = Employee::withoutGlobalScope(ActiveEmployeeScope::class)
            ->with([
                // Eager load relationships to prevent N+1.
                // with() fetches related models in a single additional query per relation.
                'department:id,name,code',
                'position:id,name,level',
                'manager:id,first_name,last_name,employee_id',
            ])
            ->withCount('leaveRequests') // adds leave_requests_count column
            ->withExists('subordinates as has_subordinates'); // boolean: has any direct reports?

        // Search filter
        if (! empty($filters['search'])) {
            $query->search($filters['search']); // local scope
        }

        // Status filter
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Department filter
        if (! empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        // Position filter
        if (! empty($filters['position_id'])) {
            $query->where('position_id', $filters['position_id']);
        }

        // Salary range filter
        if (! empty($filters['salary_min']) && ! empty($filters['salary_max'])) {
            $query->salariedBetween((float) $filters['salary_min'], (float) $filters['salary_max']);
        }

        // Joining date range filter
        if (! empty($filters['joined_from']) && ! empty($filters['joined_to'])) {
            $query->joinedBetween($filters['joined_from'], $filters['joined_to']);
        }

        // Department filter via whereHas (demonstrates relationship constraint)
        // WHY whereHas: filters employees whose department matches a name search
        if (! empty($filters['department_search'])) {
            $query->whereHas('department', function ($q) use ($filters) {
                $q->where('name', 'ilike', "%{$filters['department_search']}%");
            });
        }

        // Sorting – using whitelisted columns only
        $sortBy = in_array($filters['sort_by'] ?? null, self::SORTABLE_COLUMNS, true)
            ? $filters['sort_by']
            : 'created_at';
        $sortDirection = ($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Cursor-based pagination – ideal for "load more" / infinite scroll APIs.
     *
     * WHY cursorPaginate():
     *   Unlike paginate() which uses OFFSET (slow on large tables), cursor
     *   pagination uses a WHERE clause on a cursor column, which is O(log n)
     *   with an index. Perfect for real-time feeds and mobile APIs.
     *
     * TRADE-OFF: No total count, no jumping to page N.
     */
    public function cursorPaginate(array $filters = [], int $perPage = 15): CursorPaginator
    {
        $query = Employee::withoutGlobalScope(ActiveEmployeeScope::class)
            ->with(['department:id,name', 'position:id,name'])
            ->orderBy('id'); // Cursor pagination requires a stable sort column

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->cursorPaginate($perPage);
    }

    public function findById(string $id): Employee
    {
        $employee = Employee::withoutGlobalScope(ActiveEmployeeScope::class)
            ->findOrFail($id);

        // load() vs with():
        // - with() is used in the query builder chain (before the query executes)
        // - load() is called on an already-retrieved model instance
        // WHY load() here: the model is already fetched, we load relations lazily
        $employee->load([
            'department',
            'position',
            'manager:id,first_name,last_name,employee_id',
            'subordinates:id,first_name,last_name,employee_id',
        ]);

        // loadMissing(): like load() but skips already-loaded relations.
        // Useful when a relation might already be loaded (e.g., via with() earlier).
        $employee->loadMissing('leaveRequests');

        return $employee;
    }

    public function create(array $data): Employee
    {
        return DB::transaction(fn () => Employee::create($data));
    }

    public function update(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            $employee->update($data);

            return $employee->fresh(['department', 'position', 'manager']);
        });
    }

    public function delete(Employee $employee): bool
    {
        return DB::transaction(fn () => $employee->delete());
    }

    /**
     * Get department statistics with aggregates.
     *
     * WHY withSum / withAvg:
     *   These use a single subquery per aggregate, not N queries.
     *   withSum('leaveRequests', 'id') would give leave request count per employee,
     *   but here we use it at the department level (called from DepartmentRepository).
     *
     * @return Collection<int, Employee>
     */
    public function getDepartmentStats(): Collection
    {
        return Employee::withoutGlobalScope(ActiveEmployeeScope::class)
            ->select('department_id')
            ->withCount('leaveRequests')                                 // total leave requests
            ->withAvg('leaveRequests', 'id')                             // avg (demo only)
            ->withSum('leaveRequests as pending_leave_count', function ($q) {
                // Custom withSum using closure for conditional aggregate
                $q->where('status', 'pending');
            })
            ->groupBy('department_id')
            ->get();
    }

    /**
     * Process employees in chunks for report generation.
     *
     * WHY chunk() vs chunkById():
     *   chunk() paginates with OFFSET, so if rows are deleted during iteration,
     *   some records may be skipped. chunkById() uses WHERE id > last_id,
     *   which is safe even if records are deleted or inserted during iteration.
     *
     *   Use chunkById() for production export jobs.
     *   Use chunk() for read-only reporting where data changes are unlikely.
     */
    public function chunkForReport(int $chunkSize, callable $callback): void
    {
        // chunkById() is safer than chunk() for write operations during iteration
        Employee::withoutGlobalScope(ActiveEmployeeScope::class)
            ->with(['department:id,name', 'position:id,name'])
            ->orderBy('id')
            ->chunkById($chunkSize, $callback, 'id');
    }
}
