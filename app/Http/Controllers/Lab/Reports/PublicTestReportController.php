<?php

namespace App\Http\Controllers\Lab\Reports;

use App\Http\Controllers\Controller;
use App\Models\TestReportDocument;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Public (no login) access to a stored per-sample Test Report PDF via its QR token.
 */
class PublicTestReportController extends Controller
{
    public function show(string $token): BinaryFileResponse|Response
    {
        $document = TestReportDocument::query()
            ->official()
            ->where('token', $token)
            ->first();

        if ($document === null) {
            return $this->unavailable(
                'Report not found',
                'This QR code does not match any issued test report.',
                404,
            );
        }

        $absolutePath = $document->absoluteFilePath();
        if ($absolutePath === null) {
            return $this->unavailable(
                'Report not available',
                'This test report is not available online yet. Please contact the laboratory.',
                404,
            );
        }

        $document->increment('view_count', 1, ['last_viewed_at' => now()]);

        $downloadName = preg_replace('/[^A-Za-z0-9\-_]/', '_', (string) ($document->report_number ?: 'test-report')).'.pdf';

        return response()->file($absolutePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$downloadName.'"',
            'Cache-Control' => 'private, max-age=300',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    private function unavailable(string $title, string $message, int $status): Response
    {
        return response()->view(
            'lab.reports.public-test-report-unavailable',
            compact('title', 'message'),
            $status,
            ['X-Robots-Tag' => 'noindex, nofollow'],
        );
    }
}
