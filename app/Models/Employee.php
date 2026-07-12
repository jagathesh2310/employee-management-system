<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EmployeeStatus;
use App\Models\Scopes\ActiveEmployeeScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * WHY THIS EXISTS:
 *   The central entity of the Employee Management System.
 *   Demonstrates the full suite of Eloquent model features.
 *
 * LARAVEL FEATURES DEMONSTRATED:
 *   - HasUuids: UUID primary keys
 *   - SoftDeletes: soft deletion via deleted_at
 *   - ScopedBy attribute: applies ActiveEmployeeScope globally
 *   - BelongsTo relationships: department, position, manager
 *   - HasMany relationships: subordinates (self-ref), leaveRequests
 *   - Enum casting: status cast to EmployeeStatus
 *   - Accessors: fullName, age (computed attributes, read-only)
 *   - Mutators: email (normalised to lowercase on write)
 *   - Local scopes: active(), inactive(), inDepartment(), salariedBetween()
 *   - Model events: creating, created, updating, updated, deleting
 *     (handled via EmployeeObserver registered in AppServiceProvider)
 *
 * BEST PRACTICE:
 *   The #[ScopedBy(ActiveEmployeeScope::class)] attribute (Laravel 11+) cleanly
 *   registers the global scope without overriding the booted() method.
 *
 * @property string $id
 * @property string $employee_id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $phone
 * @property string|null $gender
 * @property Carbon|null $date_of_birth
 * @property Carbon $joining_date
 * @property float $salary
 * @property EmployeeStatus $status
 * @property string $department_id
 * @property string $position_id
 * @property string|null $manager_id
 * @property-read string $full_name
 * @property-read int|null $age
 */
#[ScopedBy([ActiveEmployeeScope::class])]
class Employee extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'date_of_birth',
        'joining_date',
        'salary',
        'status',
        'department_id',
        'position_id',
        'manager_id',
    ];

    /**
     * Attribute casting.
     *
     * WHY:
     *   Casting 'status' to EmployeeStatus enum means $employee->status returns
     *   an enum instance, giving us $employee->status->label() and type safety.
     *   Casting dates to 'date' (not 'datetime') so Carbon computes age/duration correctly.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'status' => EmployeeStatus::class,
            'date_of_birth' => 'date',
            'joining_date' => 'date',
            'salary' => 'decimal:2',
        ];
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    /**
     * An employee belongs to one department.
     *
     * DEMONSTRATION: belongsTo with UUID FK.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * An employee holds one position.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Self-referential relationship: an employee may have a manager.
     *
     * DEMONSTRATION: Self-referential belongsTo (manager is also an Employee).
     * Note: We use withoutGlobalScope() in EmployeeRepository when fetching
     * managers to include inactive managers.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /**
     * An employee may manage other employees (subordinates).
     *
     * DEMONSTRATION: Self-referential hasMany.
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    /**
     * An employee can submit multiple leave requests over time.
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    // -----------------------------------------------------------------------
    // Accessors (computed attributes, PHP 8 syntax)
    // -----------------------------------------------------------------------

    /**
     * Accessor: full_name – combines first and last name.
     *
     * WHY ACCESSOR:
     *   Avoids repeating "{$employee->first_name} {$employee->last_name}"
     *   throughout the codebase. Exposed in EmployeeResource as 'full_name'.
     *
     * LARAVEL FEATURE: New Attribute cast syntax (Laravel 9+).
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->first_name} {$this->last_name}",
        );
    }

    /**
     * Accessor: age – computed from date_of_birth.
     *
     * WHY: Date arithmetic is error-prone; centralising in the model
     * ensures consistent calculation everywhere.
     */
    protected function age(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->date_of_birth?->age,
        );
    }

    /**
     * Accessor: years_of_service – time since joining date.
     */
    protected function yearsOfService(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->joining_date->diffInYears(now()),
        );
    }

    // -----------------------------------------------------------------------
    // Mutators
    // -----------------------------------------------------------------------

    /**
     * Mutator: normalise email to lowercase on assignment.
     *
     * WHY MUTATOR:
     *   Prevents duplicate emails caused by case differences
     *   (John@Example.com vs john@example.com).
     *   The unique constraint in the DB catches DB-level dupes, but the mutator
     *   normalises before hitting the DB.
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $value,
            set: fn (string $value) => strtolower(trim($value)),
        );
    }

    // -----------------------------------------------------------------------
    // Local Scopes
    // -----------------------------------------------------------------------

    /**
     * Scope: only active employees.
     *
     * WHY LOCAL SCOPE:
     *   The global scope (ActiveEmployeeScope) already filters to active,
     *   but this local scope is used explicitly where clarity matters or
     *   after removing the global scope with withoutGlobalScope().
     *
     * @param  Builder<Employee>  $query
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', EmployeeStatus::Active);
    }

    /**
     * @param  Builder<Employee>  $query
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', EmployeeStatus::Inactive);
    }

    /**
     * Filter employees belonging to a specific department.
     *
     * @param  Builder<Employee>  $query
     */
    public function scopeInDepartment(Builder $query, string $departmentId): Builder
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Filter employees within a salary range.
     *
     * @param  Builder<Employee>  $query
     */
    public function scopeSalariedBetween(Builder $query, float $min, float $max): Builder
    {
        return $query->whereBetween('salary', [$min, $max]);
    }

    /**
     * Filter by joining date range.
     *
     * @param  Builder<Employee>  $query
     */
    public function scopeJoinedBetween(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('joining_date', [$from, $to]);
    }

    /**
     * Search by name, email, or employee_id.
     *
     * @param  Builder<Employee>  $query
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('first_name', 'ilike', "%{$term}%")
                ->orWhere('last_name', 'ilike', "%{$term}%")
                ->orWhere('email', 'ilike', "%{$term}%")
                ->orWhere('employee_id', 'ilike', "%{$term}%");
        });
    }
}
