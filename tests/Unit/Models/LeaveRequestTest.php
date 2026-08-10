<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\LeaveRequest;
use Tests\TestCase;

class LeaveRequestTest extends TestCase
{
    public function test_duration_in_days_returns_integer(): void
    {
        $leaveRequest = new LeaveRequest([
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-03',
        ]);

        $duration = $leaveRequest->duration_in_days;

        $this->assertIsInt($duration);
        $this->assertSame(3, $duration);
    }

    public function test_duration_in_days_returns_zero_when_dates_are_missing(): void
    {
        $leaveRequest = new LeaveRequest;

        $duration = $leaveRequest->duration_in_days;

        $this->assertIsInt($duration);
        $this->assertSame(0, $duration);
    }
}
