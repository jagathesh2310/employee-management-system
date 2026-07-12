<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * DepartmentCollection – wraps a paginated collection of DepartmentResource.
 *
 * WHY RESOURCE COLLECTIONS:
 *   ResourceCollection adds consistent pagination metadata to list responses.
 *   The 'meta' key includes total, per_page, current_page, etc.
 *   This is the standard response format for all paginated API endpoints.
 *
 * LARAVEL FEATURE:
 *   When you return paginate() from a ResourceCollection, Laravel
 *   automatically adds the 'links' and 'meta' pagination keys.
 */
class DepartmentCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     */
    public string $collects = DepartmentResource::class;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            // Additional custom meta can be added here
        ];
    }

    /**
     * Add custom data to the collection wrapper.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'api_version' => 'v1',
            ],
        ];
    }
}
