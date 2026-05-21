<?php

namespace App\Http\Requests\Registry;

use Illuminate\Foundation\Http\FormRequest;

class UploadRegistryDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('registry.components.documents.add') ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,docx,xlsx,jpg,jpeg,png'],
        ];
    }
}
