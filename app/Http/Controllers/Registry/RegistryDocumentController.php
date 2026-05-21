<?php

namespace App\Http\Controllers\Registry;

use App\Actions\Registry\UploadRegistryDocumentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Registry\UploadRegistryDocumentRequest;
use App\Models\Registry\RegistryRequest;
use App\Models\Registry\RegistryRequestDocument;
use App\Services\Registry\RegistryDocumentService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RegistryDocumentController extends Controller
{
    public function __construct(
        protected UploadRegistryDocumentAction $uploadAction,
        protected RegistryDocumentService $documentService,
    ) {
        $this->middleware('auth');
    }

    public function store(UploadRegistryDocumentRequest $request, string $requestId): RedirectResponse
    {
        $registryRequest = RegistryRequest::query()->forCompany()->findOrFail($requestId);
        $this->authorize('uploadDocument', $registryRequest);

        $this->uploadAction->execute($registryRequest, $request->file('file'));

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function download(string $documentId): BinaryFileResponse
    {
        $document = RegistryRequestDocument::query()->with('request')->findOrFail($documentId);
        $this->authorize('downloadDocument', $document->request);

        $path = $this->documentService->downloadPath($document);

        return response()->download($path, $document->original_name);
    }
}
