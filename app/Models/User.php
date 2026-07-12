<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * WHY THIS EXISTS:
 *   The User model represents system users who can authenticate via Sanctum tokens.
 *   Users are separate from Employees – a User is a system account, an Employee
 *   is a business entity. A user might approve leave without being an employee.
 *
 * LARAVEL FEATURES DEMONSTRATED:
 *   - HasApiTokens (Sanctum) for API token authentication
 *   - Role-based authorization via simple string column (admin | manager | employee)
 *   - The role is read by Policies and Gates in AppServiceProvider
 *
 * BEST PRACTICE:
 *   Keeping User and Employee as separate models follows SRP and makes
 *   the system extensible (e.g., external auditors can have User accounts
 *   without being employees).
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Convenience helpers for role checks used by Policies and Gates.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager' || $this->role === 'admin';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }
}
