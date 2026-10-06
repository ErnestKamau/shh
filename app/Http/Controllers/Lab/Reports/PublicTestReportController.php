<?php

namespace App\Http\Controllers\Lab\Reports;

use App\Http\Controllers\Controller;
use App\Models\TestReportDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Public (no login) access to a stored per-sample Test Report via its QR token.
 *
 * The QR opens an in-browser viewer page because mobile browsers without a built-in
 * PDF viewer (e.g. Chrome on Android) download a PDF instead of displaying it.
 */
class PublicTestReportController extends Controller
{
    public function show(string $token): Response
    {
        $document = $this->findOfficialDocument($token);

        if ($document === null) {
            return $this->notFound();
        }

        if ($document->absoluteFilePath() === null) {
            return $this->notAvailable();
        }

        $document->increment('view_count', 1, ['last_viewed_at' => now()]);

        return response()->view('lab.reports.public-test-report-viewer', [
            'reportNumber' => (string) ($document->report_number ?: 'Test report'),
            'pdfUrl' => route('public.test-report.pdf', ['token' => $document->token]),
            'downloadUrl' => route('public.test-report.pdf', ['token' => $document->token, 'download' => 1]),
        ], 200, [
            'Cache-Control' => 'private, max-age=300',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    public function pdf(Request $request, string $token): BinaryFileResponse|Response
    {
        $document = $this->findOfficialDocument($token);

        if ($document === null) {
            return $this->notFound();
        }

        $absolutePath = $document->absoluteFilePath();
        if ($absolutePath === null) {
            return $this->notAvailable();
        }

        $downloadName = preg_replace('/[^A-Za-z0-9\-_]/', '_', (string) ($document->report_number ?: 'test-report')).'.pdf';
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return response()->file($absolutePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.$downloadName.'"',
            'Cache-Control' => 'private, max-age=300',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    private function findOfficialDocument(string $token): ?TestReportDocument
    {
        return TestReportDocument::query()
            ->official()
            ->where('token', $token)
            ->first();
    }

    private function notFound(): Response
    {
        return $this->unavailable(
            'Report not found',
            'This QR code does not match any issued test report.',
            404,
        );
    }

    private function notAvailable(): Response
    {
        return $this->unavailable(
            'Report not available',
            'This test report is not available online yet. Please contact the laboratory.',
            404,
        );
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
