<?php

namespace App\Http\Controllers;

use App\Models\DMS\Document;
use App\Models\DMS\DocumentAuditLog;
use App\Services\DMS\PermissionResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DMSController extends Controller
{
    protected $permissionResolver;

    public function __construct(PermissionResolver $permissionResolver)
    {
        $this->middleware('auth');
        $this->permissionResolver = $permissionResolver;
    }

    /**
     * Download a document
     *
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
     */
    public function download(int $id)
    {
        $document = Document::findOrFail($id);

        // Check permission
        if (!$this->permissionResolver->checkPermission(auth()->user(), $document, 'view')) {
            abort(403, 'You do not have permission to download this document.');
        }

        // Log the download
        DocumentAuditLog::log(
            $document,
            'downloaded',
            null,
            null,
            'Document downloaded by ' . auth()->user()->name
        );

        // Stream the file
        return Storage::disk('dms')->download($document->file_path, $document->file_name);
    }

    /**
     * Preview a document
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function preview(int $id)
    {
        $document = Document::findOrFail($id);

        // Check permission
        if (!$this->permissionResolver->checkPermission(auth()->user(), $document, 'view')) {
            abort(403, 'You do not have permission to preview this document.');
        }

        // Log the view
        DocumentAuditLog::log(
            $document,
            'viewed',
            null,
            null,
            'Document viewed by ' . auth()->user()->name
        );

        // Check if file is previewable
        $previewableMimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'text/plain',
        ];

        if (!in_array($document->mime_type, $previewableMimeTypes)) {
            return response()->json([
                'error' => 'This file type cannot be previewed. Please download it instead.',
                'mime_type' => $document->mime_type,
            ], 400);
        }

        $file = Storage::disk('dms')->get($document->file_path);

        return response($file, 200)
            ->header('Content-Type', $document->mime_type)
            ->header('Content-Disposition', 'inline; filename="' . $document->file_name . '"');
    }

    /**
     * Download a specific version of a document
     *
     * @param int $documentId
     * @param int $versionId
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
     */
    public function downloadVersion(int $documentId, int $versionId)
    {
        $document = Document::findOrFail($documentId);
        $version = $document->versions()->findOrFail($versionId);

        // Check permission
        if (!$this->permissionResolver->checkPermission(auth()->user(), $document, 'view')) {
            abort(403, 'You do not have permission to download this document version.');
        }

        // Log the download
        DocumentAuditLog::log(
            $document,
            'downloaded',
            null,
            ['version' => $version->version_number],
            "Version {$version->version_number} downloaded by " . auth()->user()->name
        );

        return Storage::disk('dms')->download($version->file_path, $version->file_name);
    }
}

