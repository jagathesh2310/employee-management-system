<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * WHY THIS EXISTS:
 *   Represents an organisational department (e.g., Engineering, HR).
 *   Employees belong to departments; departments are the top of the hierarchy.
 *
 * LARAVEL FEATURES DEMONSTRATED:
 *   - HasUuids trait: automatically generates UUID v7 for the 'id' column
 *   - HasMany relationship: $department->employees
 *   - Local query scopes: search() and withEmployeeCount()
 *   - Attribute casting
 *
 * BEST PRACTICE:
 *   Use HasUuids instead of manually calling Str::uuid() in boot() –
 *   it's the canonical Laravel 10+ approach and handles UUID generation in a
 *   single trait without model booting overhead.
 *
 * @property string $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Department extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    /**
     * A department has many employees.
     *
     * DEMONSTRATION: hasMany one-to-many relationship.
     * Use: $department->employees()->active()->get()
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    // -----------------------------------------------------------------------
    // Local Scopes
    // -----------------------------------------------------------------------

    /**
     * Local scope: filter departments by name search.
     *
     * WHY LOCAL SCOPE:
     *   Encapsulates the search logic in the model so controllers/repositories
     *   don't repeat the WHERE clause. Called as: Department::search('eng')
     *
     * @param  Builder<Department>  $query
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where('name', 'ilike', "%{$term}%");
    }

    /**
     * Local scope: eager-load employee count in one query.
     *
     * WHY: withCount() avoids N+1 when listing departments with employee counts.
     *
     * @param  Builder<Department>  $query
     */
    public function scopeWithEmployeeCount(Builder $query): Builder
    {
        return $query->withCount('employees');
    }
}
