<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WHY THIS EXISTS:
 *   The core entity of the system. Stores all employee profile data.
 *
 * LARAVEL FEATURES DEMONSTRATED:
 *   - UUID primary keys
 *   - Foreign key constraints with cascade rules
 *   - Self-referential foreign key (manager_id → employees.id)
 *   - Soft deletes (deleted_at) – employees are never hard-deleted
 *   - Enum-backed string column for status
 *   - Decimal column for salary (precise money storage)
 *   - Composite indexes for common query patterns
 *   - Indexes on FK columns (Laravel doesn't auto-index FKs)
 *
 * BEST PRACTICE:
 *   We index every foreign key column and searchable columns to prevent
 *   sequential scans on a 200+ row table. The composite index on
 *   (department_id, status) covers the most common filter pattern.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Business identifier (e.g. EMP-001) – unique across all employees
            $table->string('employee_id', 20)->unique();

            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 255)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('gender', 10)->nullable(); // male | female | other

            $table->date('date_of_birth')->nullable();
            $table->date('joining_date');

            // decimal(12,2) stores salaries up to 9,999,999,999.99
            $table->decimal('salary', 12, 2)->default(0);

            // Status backed by EmployeeStatus enum
            $table->string('status', 20)->default('active');

            // Department FK – restrict delete (can't delete dept with employees)
            $table->uuid('department_id');
            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->restrictOnDelete();

            // Position FK
            $table->uuid('position_id');
            $table->foreign('position_id')
                ->references('id')
                ->on('positions')
                ->restrictOnDelete();

            // Self-referential FK for manager (nullable – CEO has no manager)
            $table->uuid('manager_id')->nullable();

            $table->timestamps();
            $table->softDeletes(); // Soft delete – deleted_at column

            // ----- Indexes -----
            // Single column indexes for filtering/sorting
            $table->index('status');
            $table->index('joining_date');
            $table->index('salary');
            $table->index('department_id');
            $table->index('position_id');
            $table->index('manager_id');

            // Composite index – the most common query: active employees in a department
            $table->index(['department_id', 'status'], 'idx_employees_dept_status');

            // Full-name search index (first + last)
            $table->index(['first_name', 'last_name'], 'idx_employees_name');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('manager_id')
                ->references('id')
                ->on('employees')
                ->nullOnDelete(); // If manager is deleted, set to null
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
