<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Models\Sampleworkflow\LabSectionWorksheet;
use App\Services\Sampleworkflow\LabSectionWorksheetAccess;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LabSectionWorksheetController extends Controller
{
    public function downloadPdf(LabSectionWorksheet $worksheet, LabSectionWorksheetAccess $access): StreamedResponse|BinaryFileResponse
    {
        abort_unless($access->canDownloadWorksheet(request()->user(), $worksheet), 403);

        $path = trim((string) ($worksheet->pdf_path ?? ''));
        abort_if($path === '' || ! Storage::disk('public')->exists($path), 404);

        $worksheet->forceFill(['downloaded_at' => now()])->save();

        $filename = $this->safeFilename((string) $worksheet->worksheet_number).'.pdf';

        return Storage::disk('public')->download($path, $filename);
    }

    public function downloadExcel(LabSectionWorksheet $worksheet, LabSectionWorksheetAccess $access): StreamedResponse|BinaryFileResponse
    {
        abort_unless($access->canDownloadWorksheet(request()->user(), $worksheet), 403);

        $path = trim((string) ($worksheet->excel_path ?? ''));
        abort_if($path === '' || ! Storage::disk('public')->exists($path), 404);

        $worksheet->forceFill(['downloaded_at' => now()])->save();

        $filename = $this->safeFilename((string) $worksheet->worksheet_number).'.xlsx';

        return Storage::disk('public')->download($path, $filename);
    }

    private function safeFilename(string $value): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_-]+/', '-', $value) ?? 'worksheet';

        return trim($safe, '-') !== '' ? trim($safe, '-') : 'worksheet';
    }
}
