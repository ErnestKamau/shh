<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Commercial\CustomerPurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerPurchaseOrderDownloadController extends Controller
{
    public function __invoke(Request $request, string $id): StreamedResponse|Response
    {
        $po = CustomerPurchaseOrder::query()->findOrFail($id);

        abort_unless($po->fileExists(), 404);

        $path = (string) $po->file_path;
        $filename = (string) ($po->file_name ?: 'purchase-order');
        $mime = (string) ($po->mime ?: Storage::disk('public')->mimeType($path) ?: 'application/octet-stream');

        if ($request->boolean('inline')) {
            return Storage::disk('public')->response($path, $filename, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]);
        }

        return Storage::disk('public')->download($path, $filename);
    }
}
