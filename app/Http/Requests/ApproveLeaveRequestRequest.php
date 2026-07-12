<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ApproveLeaveRequestRequest – validates leave approval action.
 *
 * WHY SEPARATE REQUEST:
 *   Approval is a distinct action with distinct authorization rules.
 *   Only managers/admins can approve; only pending leaves can be approved.
 *   Having a separate FormRequest makes this explicit.
 */
class ApproveLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $leaveRequest = $this->route('leave_request');

        return $this->user()?->can('approve', $leaveRequest) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // No additional input needed for approval – the action itself is the request.
        // The policy handles the business rule (only pending can be approved).
        return [];
    }
}
