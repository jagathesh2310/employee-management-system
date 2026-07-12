<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * WHY THIS EXISTS:
 *   Tracks leave applications submitted by employees.
 *   The status lifecycle (Pending → Approved/Rejected) is enforced in
 *   the LeaveRequestService and LeaveRequestPolicy.
 *
 * LARAVEL FEATURES DEMONSTRATED:
 *   - HasUuids: UUID primary key
 *   - SoftDeletes: audit trail – leave requests are never hard-deleted
 *   - Enum casting: status → LeaveStatus, leave_type → LeaveType
 *   - Multiple BelongsTo relationships (Employee + User approver)
 *   - Local scopes: pending(), approved(), byType(), dateRange()
 *   - Model events: handled by LeaveRequestObserver
 *   - Duration accessor: computed number of working days
 *
 * @property string $id
 * @property string $employee_id
 * @property LeaveType $leave_type
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property string|null $reason
 * @property LeaveStatus $status
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 */
class LeaveRequest extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'approved_by',
        'approved_at',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'leave_type'  => LeaveType::class,
            'status'      => LeaveStatus::class,
            'start_date'  => 'date',
            'end_date'    => 'date',
            'approved_at' => 'datetime',
        ];
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    /**
     * The employee who submitted this leave request.
     *
     * DEMONSTRATION: belongsTo with UUID FK.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * The user (manager/admin) who approved or rejected the leave.
     *
     * DEMONSTRATION: belongsTo pointing to a different model (User, not Employee).
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // -----------------------------------------------------------------------
    // Local Scopes
    // -----------------------------------------------------------------------

    /**
     * @param  Builder<LeaveRequest>  $query
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', LeaveStatus::Pending);
    }

    /**
     * @param  Builder<LeaveRequest>  $query
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', LeaveStatus::Approved);
    }

    /**
     * @param  Builder<LeaveRequest>  $query
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', LeaveStatus::Rejected);
    }

    /**
     * Filter by leave type.
     *
     * @param  Builder<LeaveRequest>  $query
     */
    public function scopeByType(Builder $query, LeaveType $type): Builder
    {
        return $query->where('leave_type', $type);
    }

    /**
     * Filter by date range (requests overlapping the given range).
     *
     * @param  Builder<LeaveRequest>  $query
     */
    public function scopeDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->where('start_date', '<=', $to)
            ->where('end_date', '>=', $from);
    }

    // -----------------------------------------------------------------------
    // Accessors
    // -----------------------------------------------------------------------

    /**
     * Compute the number of days in the leave request.
     *
     * WHY ACCESSOR: Business logic that belongs on the model,
     * not in controllers or resources.
     */
    public function getDurationInDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }
}
