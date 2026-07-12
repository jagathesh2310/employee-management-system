<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EmployeeCreated;
use App\Events\EmployeeDeleted;
use App\Events\EmployeeUpdated;
use Illuminate\Support\Facades\Cache;

/**
 * ClearEmployeeCache – invalidates Redis cache after employee data changes.
 *
 * WHY THIS LISTENER EXISTS:
 *   When employee data changes (created, updated, deleted), any cached
 *   employee lists or stats become stale and must be invalidated.
 *   Instead of the service manually clearing cache, this listener handles
 *   it automatically via the event system.
 *
 * LARAVEL FEATURE:
 *   This listener handles multiple events by implementing handle() for each.
 *   Registered in AppServiceProvider::boot() using Event::listen().
 *
 * REDIS CACHING:
 *   - Cache::tags() allows grouping related cache entries under a tag.
 *   - flush() on a tag clears all entries with that tag.
 *   - This is more granular than Cache::flush() which clears everything.
 *
 * INTERACTION:
 *   Subscribed to EmployeeCreated, EmployeeUpdated, EmployeeDeleted in
 *   AppServiceProvider. Runs synchronously (no ShouldQueue).
 */
class ClearEmployeeCache
{
    /**
     * Handle EmployeeCreated – invalidate employee list cache.
     *
     * WHY: A new employee means the list cache (employee-listing) is stale.
     */
    public function handleEmployeeCreated(EmployeeCreated $event): void
    {
        $this->clearEmployeeListingCache();
        $this->clearStatisticsCache();
    }

    /**
     * Handle EmployeeUpdated – invalidate both list and individual employee cache.
     */
    public function handleEmployeeUpdated(EmployeeUpdated $event): void
    {
        $this->clearEmployeeListingCache();
        // Clear the individual employee cache key
        Cache::forget("employee:{$event->employee->id}");
        $this->clearStatisticsCache();
    }

    /**
     * Handle EmployeeDeleted – remove from listing and individual caches.
     */
    public function handleEmployeeDeleted(EmployeeDeleted $event): void
    {
        $this->clearEmployeeListingCache();
        Cache::forget("employee:{$event->employee->id}");
        $this->clearStatisticsCache();
    }

    /**
     * Clear all employee listing pages from cache using Redis tags.
     *
     * WHY Redis tags:
     *   A paginated employee list has many cache keys (page 1, page 2, etc.)
     *   Tags group them so we can flush all pages at once with one call.
     */
    private function clearEmployeeListingCache(): void
    {
        // Cache::tags() requires a tag-supporting driver (Redis or Memcached).
        // The CACHE_STORE=redis setting in .env enables this.
        Cache::tags(['employees'])->flush();
    }

    private function clearStatisticsCache(): void
    {
        Cache::tags(['statistics'])->flush();
    }
}
