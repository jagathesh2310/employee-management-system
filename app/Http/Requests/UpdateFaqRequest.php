<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('faq')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'question' => ['sometimes', 'required', 'string', 'min:5', 'max:500'],
            'answer' => ['sometimes', 'required', 'string', 'min:10'],
            'is_active' => ['boolean'],
        ];
    }
}
