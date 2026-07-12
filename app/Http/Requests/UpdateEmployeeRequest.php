<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\EmployeeStatus;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateEmployeeRequest – validates employee update input.
 *
 * All fields are 'sometimes' + 'required' (PATCH semantics):
 * only provided fields are validated and updated.
 */
class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $employee = $this->route('employee');

        return $this->user()?->can('update', $employee) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Employee $employee */
        $employee = $this->route('employee');

        return [
            'employee_id'   => ['sometimes', 'required', 'string', 'max:20', 'regex:/^EMP-\d{3,}$/',
                Rule::unique('employees', 'employee_id')->ignore($employee->id)],
            'first_name'    => ['sometimes', 'required', 'string', 'min:1', 'max:100'],
            'last_name'     => ['sometimes', 'required', 'string', 'min:1', 'max:100'],
            'email'         => ['sometimes', 'required', 'email', 'max:255',
                Rule::unique('employees', 'email')->ignore($employee->id)],
            'phone'         => ['nullable', 'string', 'max:20'],
            'gender'        => ['nullable', 'string', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date', 'before:-18 years'],
            'joining_date'  => ['sometimes', 'required', 'date', 'before_or_equal:today'],
            'salary'        => ['sometimes', 'required', 'numeric', 'min:0', 'max:99999999.99'],
            'status'        => ['sometimes', 'required', Rule::enum(EmployeeStatus::class)],
            'department_id' => ['sometimes', 'required', 'uuid', 'exists:departments,id'],
            'position_id'   => ['sometimes', 'required', 'uuid', 'exists:positions,id'],
            'manager_id'    => ['nullable', 'uuid', 'exists:employees,id',
                // An employee cannot be their own manager
                Rule::notIn([$employee->id])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'manager_id.not_in' => 'An employee cannot be assigned as their own manager.',
        ];
    }
}
