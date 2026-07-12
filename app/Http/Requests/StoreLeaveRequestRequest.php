<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\LeaveType;
use App\Rules\NoOverlappingLeave;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreLeaveRequestRequest – validates new leave request submission.
 *
 * DEMONSTRATES:
 *   - Cross-field validation: end_date must be after start_date
 *   - Custom rule injection: NoOverlappingLeave (prevents overlapping leaves)
 *   - Rule::enum(): validates LeaveType backed enum
 *   - exists: validates the employee ID exists in the employees table
 *   - Dependency on multiple validated fields in a custom rule
 */
class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\LeaveRequest::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'uuid', 'exists:employees,id'],
            'leave_type'  => ['required', Rule::enum(LeaveType::class)],
            'start_date'  => ['required', 'date', 'after_or_equal:today'],
            // end_date must be after or equal to start_date
            'end_date'    => [
                'required',
                'date',
                'after_or_equal:start_date', // Cross-field validation using another field name
            ],
            'reason'      => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom validation after basic rules pass.
     *
     * WHY withValidator():
     *   The NoOverlappingLeave rule needs both start_date and end_date.
     *   We add it here (after basic rules pass) to ensure both dates exist.
     *   This avoids running a DB query when the dates are invalid.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $v) {
            if ($v->errors()->has('start_date') || $v->errors()->has('end_date')) {
                return; // Don't check overlap if dates are already invalid
            }

            $rule = new NoOverlappingLeave(
                employeeId: $this->input('employee_id'),
                startDate:  $this->input('start_date'),
                endDate:    $this->input('end_date'),
            );

            $rule->validate('start_date', $this->input('start_date'), function (string $message) use ($v) {
                $v->errors()->add('start_date', $message);
            });
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
            'start_date.after_or_equal' => 'Leave cannot be requested for past dates.',
        ];
    }
}
