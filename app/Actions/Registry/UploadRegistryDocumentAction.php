<?php

namespace App\Actions\Registry;

use App\Models\Registry\RegistryRequest;
use App\Models\Registry\RegistryRequestDocument;
use App\Services\Registry\RegistryDocumentService;
use App\Services\Registry\WorkflowEngineService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class UploadRegistryDocumentAction
{
    public function __construct(
        protected RegistryDocumentService $documentService,
        protected WorkflowEngineService $workflowEngineService,
    ) {
    }

    public function execute(RegistryRequest $request, UploadedFile $file): RegistryRequestDocument
    {
        $document = $this->documentService->upload($request, $file);

        $this->workflowEngineService->recordAction(
            $request,
            'document_uploaded',
            $request->current_stage,
            $request->current_stage,
            'Uploaded: ' . $document->original_name,
            Auth::id(),
            ['document_id' => $document->id, 'version' => $document->version]
        );

        return $document;
    }
}
