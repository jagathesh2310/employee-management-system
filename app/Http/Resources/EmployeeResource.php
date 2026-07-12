<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * EmployeeResource – the most feature-rich resource in the system.
 *
 * DEMONSTRATES:
 *   - Nested resources: department, position, manager as nested resources
 *   - whenLoaded(): conditionally include nested relations
 *   - when(): conditionally include computed/aggregated fields
 *   - Enum accessors: status label and color from EmployeeStatus enum
 *   - Model accessors: full_name, age, years_of_service
 */
class EmployeeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'employee_id'       => $this->employee_id,
            'full_name'         => $this->full_name, // Model accessor
            'first_name'        => $this->first_name,
            'last_name'         => $this->last_name,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'gender'            => $this->gender,
            'date_of_birth'     => $this->date_of_birth?->format('Y-m-d'),
            'age'               => $this->age, // Computed accessor
            'joining_date'      => $this->joining_date?->format('Y-m-d'),
            'years_of_service'  => $this->years_of_service, // Computed accessor
            'salary'            => $this->salary,
            'status'            => [
                'value' => $this->status->value,
                'label' => $this->status->label(), // Enum method
                'color' => $this->status->color(), // Enum method
            ],

            // Nested resources – only included if the relation was eager-loaded
            // WHY whenLoaded(): avoids triggering extra queries if not needed
            'department'        => new DepartmentResource($this->whenLoaded('department')),
            'position'          => new PositionResource($this->whenLoaded('position')),
            'manager'           => new EmployeeResource($this->whenLoaded('manager')),

            // Aggregates – only include if withCount() / withExists() was called
            'leave_requests_count' => $this->when(
                isset($this->leave_requests_count),
                fn () => $this->leave_requests_count
            ),
            'has_subordinates'  => $this->when(
                isset($this->has_subordinates),
                fn () => (bool) $this->has_subordinates
            ),

            'created_at'        => $this->created_at?->toIso8601String(),
            'updated_at'        => $this->updated_at?->toIso8601String(),
        ];
    }
}
