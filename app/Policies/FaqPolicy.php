<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Faq;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * FaqPolicy – authorization rules for FAQ management.
 *
 * Viewing and searching FAQs is open to all authenticated users.
 * Creating, updating, and deleting is restricted to admins (via before()).
 */
class FaqPolicy
{
    use HandlesAuthorization;

    /**
     * Intercept all checks. Admins can do everything.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Faq $faq): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false; // Only admin (handled by before())
    }

    public function update(User $user, Faq $faq): bool
    {
        return false; // Only admin
    }

    public function delete(User $user, Faq $faq): bool
    {
        return false; // Only admin
    }
}
