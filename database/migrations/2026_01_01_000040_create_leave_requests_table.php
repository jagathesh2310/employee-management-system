<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WHY THIS EXISTS:
 *   Tracks employee leave requests through their lifecycle (Pending → Approved/Rejected).
 *
 * LARAVEL FEATURES DEMONSTRATED:
 *   - UUID primary keys
 *   - Multiple foreign keys (employee, approver)
 *   - Nullable timestamp column (approved_at)
 *   - Soft deletes (leave requests are never hard-deleted for audit)
 *   - Composite index on (employee_id, status) for the overlap check in NoOverlappingLeave rule
 *   - Index on date range columns for filtering
 *
 * BEST PRACTICE:
 *   The composite index on (employee_id, start_date, end_date) supports the
 *   overlap detection query in NoOverlappingLeave custom rule efficiently.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('employee_id');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->cascadeOnDelete(); // Leave requests deleted when employee is hard-deleted

            $table->string('leave_type', 20);  // LeaveType enum value
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('pending'); // LeaveStatus enum value

            // Approver is a User (not an Employee) – demonstrates cross-model FKs
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ----- Indexes -----
            $table->index('employee_id');
            $table->index('status');
            $table->index('leave_type');
            $table->index(['start_date', 'end_date'], 'idx_leave_dates');

            // Composite index for overlap detection query
            $table->index(['employee_id', 'status', 'start_date', 'end_date'], 'idx_leave_overlap');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
