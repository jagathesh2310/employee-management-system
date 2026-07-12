<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Fired when a leave request is rejected. */
class LeaveRejected
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly LeaveRequest $leaveRequest,
        public readonly User $approver,
    ) {}
}
