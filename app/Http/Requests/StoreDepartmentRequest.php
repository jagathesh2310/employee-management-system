<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreDepartmentRequest – validates new department creation input.
 *
 * WHY FORM REQUESTS:
 *   Instead of validate() in controllers, FormRequest classes:
 *     1. Keep controllers thin (single responsibility)
 *     2. Make validation rules easily testable in isolation
 *     3. Provide a central place for authorization checks
 *     4. Auto-reject with 422 before the controller runs
 *
 * LARAVEL FEATURE:
 *   authorize(): returns true/false. If false, Laravel returns 403.
 *   rules(): the validation rule array.
 *   messages(): custom error messages per field/rule.
 */
class StoreDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * WHY: FormRequest authorization runs before rules(). If false,
     * the request never reaches the controller. Here we delegate
     * to the DepartmentPolicy via $this->user()->can().
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Department::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'min:2', 'max:150'],
            // unique with table/column – prevents duplicate department codes
            'code'        => ['required', 'string', 'max:20', 'unique:departments,code', 'regex:/^[A-Z0-9]+$/i'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'This department code is already in use. Please choose a different one.',
            'code.regex'  => 'Department code must contain only letters and numbers (no spaces).',
        ];
    }
}
