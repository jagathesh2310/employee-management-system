<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'leave_type'          => [
                'value' => $this->leave_type->value,
                'label' => $this->leave_type->label(),
            ],
            'start_date'          => $this->start_date->format('Y-m-d'),
            'end_date'            => $this->end_date->format('Y-m-d'),
            'duration_in_days'    => $this->duration_in_days, // Model accessor
            'reason'              => $this->reason,
            'status'              => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            'approved_at'         => $this->approved_at?->toIso8601String(),

            // Nested resources – loaded conditionally
            'employee'            => new EmployeeResource($this->whenLoaded('employee')),
            'approver'            => $this->whenLoaded('approver', fn () => [
                'id'   => $this->approver->id,
                'name' => $this->approver->name,
            ]),

            'created_at'          => $this->created_at?->toIso8601String(),
            'updated_at'          => $this->updated_at?->toIso8601String(),
        ];
    }
}
