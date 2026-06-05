<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\CrmCustomerAttachment;
use App\Models\CRM\CRMCustomer;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerAttachmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function download(string $customer, string $attachment): StreamedResponse
    {
        CRMCustomer::findOrFail($customer);

        $attachmentModel = CrmCustomerAttachment::where('id', $attachment)
            ->where('crm_customer_id', $customer)
            ->where('is_delete', '!=', 1)
            ->firstOrFail();

        if (empty($attachmentModel->file_path)) {
            abort(404, 'Attachment has no file.');
        }

        $relativePath = ltrim(preg_replace('#^/storage/#', '', $attachmentModel->file_path), '/');

        if (!Storage::disk('public')->exists($relativePath)) {
            abort(404, 'File not found.');
        }

        $mime = Storage::disk('public')->mimeType($relativePath);
        $filename = basename($attachmentModel->file_path);

        return response()->streamDownload(
            fn () => print(Storage::disk('public')->get($relativePath)),
            $filename,
            ['Content-Type' => $mime]
        );
    }
}
