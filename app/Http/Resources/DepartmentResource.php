<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * DepartmentResource – transforms a Department model into an API JSON response.
 *
 * WHY API RESOURCES:
 *   Resources decouple the API response shape from the model.
 *   Benefits:
 *     1. Rename or omit internal fields without changing model
 *     2. Add computed fields (employees_count) conditionally
 *     3. Nest related resources (prevents N+1 by using already-loaded relations)
 *     4. Version API responses without touching models
 *
 * LARAVEL FEATURES DEMONSTRATED:
 *   - whenLoaded(): only includes 'employees' key if the relation was eager-loaded
 *     (prevents N+1 queries in the resource layer)
 *   - when(): conditionally includes a field based on a condition
 *   - $this->resource: the underlying model instance
 */
class DepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'code'            => $this->code,
            'description'     => $this->description,

            // whenLoaded(): only add 'employees_count' if withCount('employees') was called.
            // WHY: Avoids triggering a COUNT query when not needed (e.g., listing view)
            'employees_count' => $this->when(
                isset($this->employees_count),
                fn () => $this->employees_count
            ),

            // Conditionally nest employee resources if the 'employees' relation is loaded
            'employees'       => EmployeeResource::collection($this->whenLoaded('employees')),

            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
