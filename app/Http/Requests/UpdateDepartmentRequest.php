<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateDepartmentRequest – validates department update input.
 *
 * KEY DIFFERENCE from Store:
 *   The 'code' unique rule must ignore the CURRENT department's ID
 *   to allow updating without changing the code.
 *   Uses Rule::unique()->ignore($id) for this.
 */
class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $department = $this->route('department');

        return $this->user()?->can('update', $department) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Department $department */
        $department = $this->route('department');

        return [
            'name'        => ['sometimes', 'required', 'string', 'min:2', 'max:150'],
            // Rule::unique()->ignore(): unique but excluding the current record
            'code'        => [
                'sometimes', 'required', 'string', 'max:20', 'regex:/^[A-Z0-9]+$/i',
                Rule::unique('departments', 'code')->ignore($department->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
