<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WHY THIS EXISTS:
 *   Positions define an employee's role level within the company hierarchy
 *   (e.g., Junior Engineer → Senior Engineer → Lead → Manager).
 *
 * LARAVEL FEATURES DEMONSTRATED:
 *   - UUID primary key
 *   - Integer level column for ordering positions hierarchically
 *   - Index on level for efficient sorting
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->unsignedTinyInteger('level')->default(1); // 1 = Entry, 5 = Executive
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('level');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
