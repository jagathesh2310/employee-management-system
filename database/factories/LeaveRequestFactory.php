<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-2 years', '+3 months');
        $endDate   = (clone $startDate)->modify('+' . $this->faker->numberBetween(1, 14) . ' days');

        return [
            'employee_id' => Employee::factory(),
            'leave_type'  => $this->faker->randomElement(LeaveType::cases()),
            'start_date'  => $startDate->format('Y-m-d'),
            'end_date'    => $endDate->format('Y-m-d'),
            'reason'      => $this->faker->sentence(8),
            'status'      => LeaveStatus::Pending,
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    // -----------------------------------------------------------------------
    // States
    // -----------------------------------------------------------------------

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'      => LeaveStatus::Pending,
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'      => LeaveStatus::Approved,
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'      => LeaveStatus::Rejected,
            'approved_at' => now(),
        ]);
    }

    public function casual(): static
    {
        return $this->state(fn (array $attributes) => [
            'leave_type' => LeaveType::Casual,
        ]);
    }

    public function sick(): static
    {
        return $this->state(fn (array $attributes) => [
            'leave_type' => LeaveType::Sick,
        ]);
    }
}
