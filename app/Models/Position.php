<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * WHY THIS EXISTS:
 *   Positions define job roles within the company (e.g., Junior Dev, Senior Dev).
 *   Each employee holds one position; positions belong to no specific department,
 *   allowing cross-department reuse (e.g., "Manager" level 4 in any dept).
 *
 * LARAVEL FEATURES DEMONSTRATED:
 *   - HasUuids trait for UUID primary key
 *   - hasMany relationship
 *   - Local scopes for common query patterns
 *   - orderBy scope via byLevel
 *
 * @property string $id
 * @property string $name
 * @property int $level
 * @property string|null $description
 */
class Position extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'level',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => 'integer',
        ];
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    /**
     * DEMONSTRATION: hasMany – one position has many employees holding that role.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    // -----------------------------------------------------------------------
    // Local Scopes
    // -----------------------------------------------------------------------

    /**
     * Order positions from entry-level (1) to executive (5+).
     *
     * @param  Builder<Position>  $query
     */
    public function scopeByLevel(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('level', $direction);
    }

    /**
     * Filter by level range.
     *
     * @param  Builder<Position>  $query
     */
    public function scopeOfLevel(Builder $query, int $level): Builder
    {
        return $query->where('level', $level);
    }
}
