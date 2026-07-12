<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WHY THIS EXISTS:
 *   Departments are the top-level organisational unit.
 *   Every employee belongs to exactly one department.
 *
 * LARAVEL FEATURES DEMONSTRATED:
 *   - UUID primary keys (uuid() helper)
 *   - Composite index on (name, code) for fast lookups
 *   - unique() constraint on code to prevent duplicates
 *   - Timestamps for audit trail
 *
 * BEST PRACTICE:
 *   We use $table->uuid('id')->primary() instead of $table->id() because UUIDs
 *   avoid exposing sequential IDs in URLs and enable distributed inserts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->string('code', 20)->unique(); // e.g. ENG, HR, FIN
            $table->text('description')->nullable();
            $table->timestamps();

            // Index for fast name searches
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
