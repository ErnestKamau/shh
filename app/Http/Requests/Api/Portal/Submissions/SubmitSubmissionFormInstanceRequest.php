<?php

namespace App\Http\Requests\Api\Portal\Submissions;

use Illuminate\Foundation\Http\FormRequest;

class SubmitSubmissionFormInstanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:submit'],
            'title' => ['nullable', 'string', 'max:255'],
            'fields' => ['nullable', 'array'],
        ];
    }
}
