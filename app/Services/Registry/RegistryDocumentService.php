<?php

namespace App\Services\Registry;

use App\Models\Registry\RegistryRequest;
use App\Models\Registry\RegistryRequestDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RegistryDocumentService
{
    /** @var array<int, string> */
    protected array $allowedMimes = [
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/png',
    ];

    public function upload(RegistryRequest $request, UploadedFile $file): RegistryRequestDocument
    {
        $mime = (string) $file->getMimeType();
        if (! in_array($mime, $this->allowedMimes, true)) {
            throw ValidationException::withMessages([
                'file' => 'File type not allowed. Allowed: PDF, DOCX, XLSX, JPG, PNG.',
            ]);
        }

        $version = (int) $request->documents()->max('version') + 1;
        $path = $file->store('registry/' . $request->id, 'local');

        return RegistryRequestDocument::create([
            'registry_request_id' => $request->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'mime_type' => $mime,
            'file_size' => $file->getSize(),
            'version' => $version,
            'uploaded_by' => Auth::id(),
        ]);
    }

    public function downloadPath(RegistryRequestDocument $document): string
    {
        if (! Storage::disk('local')->exists($document->stored_path)) {
            throw ValidationException::withMessages(['file' => 'Document file not found.']);
        }

        return Storage::disk('local')->path($document->stored_path);
    }
}
