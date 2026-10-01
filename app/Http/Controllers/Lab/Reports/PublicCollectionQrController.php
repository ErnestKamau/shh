<?php

namespace App\Http\Controllers\Lab\Reports;

use App\Http\Controllers\Controller;
use App\Models\CollectionQrCode;
use App\Models\TestReportDocument;
use App\Services\Sampleworkflow\CollectionQrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * Public (no login) scan target for container QR stickers printed before collection.
 */
class PublicCollectionQrController extends Controller
{
    public function __construct(private readonly CollectionQrCodeService $collectionQrCodes) {}

    public function show(string $token): RedirectResponse|Response
    {
        $code = CollectionQrCode::query()->where('token', $token)->first();

        if ($code === null || $code->isVoid()) {
            return $this->page(
                'QR code not recognised',
                'This QR code does not match any sample container registered with the laboratory.',
                404,
            );
        }

        $this->collectionQrCodes->recordScan($code);

        $documents = $this->collectionQrCodes->reportDocumentsFor($code);

        if ($documents->isEmpty()) {
            return $this->page(
                'No report generated',
                'The test report for this sample has not been issued yet. Please check again later or contact the laboratory.',
                200,
            );
        }

        if ($documents->count() === 1) {
            return redirect()->away($documents->first()->publicUrl());
        }

        return $this->page(
            'Test reports',
            'More than one test report has been issued for this sample. Choose a report to open.',
            200,
            $documents->map(static fn (TestReportDocument $document): array => [
                'label' => $document->report_number ?: 'Test report',
                'url' => $document->publicUrl(),
            ])->all(),
        );
    }

    /**
     * @param  list<array{label: string, url: string}>  $links
     */
    private function page(string $title, string $message, int $status, array $links = []): Response
    {
        return response()->view(
            'lab.reports.public-test-report-unavailable',
            compact('title', 'message', 'links'),
            $status,
            [
                'Cache-Control' => 'no-store',
                'X-Robots-Tag' => 'noindex, nofollow',
            ],
        );
    }
}
