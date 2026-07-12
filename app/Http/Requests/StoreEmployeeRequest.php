<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\EmployeeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreEmployeeRequest – validates new employee creation.
 *
 * DEMONSTRATES:
 *   - Multiple unique validations (employee_id, email)
 *   - exists: rule for FK validation (department, position, manager)
 *   - Rule::enum() for backed enum validation (Laravel 10+)
 *   - date_format validation with before/after constraints
 */
class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Employee::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'employee_id'   => ['required', 'string', 'max:20', 'unique:employees,employee_id', 'regex:/^EMP-\d{3,}$/'],
            'first_name'    => ['required', 'string', 'min:1', 'max:100'],
            'last_name'     => ['required', 'string', 'min:1', 'max:100'],
            'email'         => ['required', 'email', 'max:255', 'unique:employees,email'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'gender'        => ['nullable', 'string', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date', 'before:-18 years'], // Must be at least 18
            'joining_date'  => ['required', 'date', 'before_or_equal:today'],
            'salary'        => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'status'        => ['sometimes', Rule::enum(EmployeeStatus::class)],
            // exists: ensures the FK actually refers to an existing record
            'department_id' => ['required', 'uuid', 'exists:departments,id'],
            'position_id'   => ['required', 'uuid', 'exists:positions,id'],
            // Manager must be an existing employee (nullable for top-level managers)
            'manager_id'    => ['nullable', 'uuid', 'exists:employees,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'employee_id.regex'        => 'Employee ID must be in the format EMP-001, EMP-002, etc.',
            'date_of_birth.before'     => 'Employee must be at least 18 years old.',
            'department_id.exists'     => 'The selected department does not exist.',
            'position_id.exists'       => 'The selected position does not exist.',
            'manager_id.exists'        => 'The selected manager does not exist.',
            'email.unique'             => 'An employee with this email address already exists.',
            'employee_id.unique'       => 'This employee ID is already taken.',
        ];
    }
}
