<?php

namespace Tests\Feature\Sampleworkflow;

use App\Models\TestReportDocument;
use App\Services\Sampleworkflow\SampleTestReportDocumentService;
use App\SampleHeader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicTestReportQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_stored_sample_report_pdf_by_token(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('reports/customer/samples/TRR_sample.pdf', '%PDF-1.4 sample');

        $document = TestReportDocument::factory()->withFile()->create([
            'report_number' => '260909054-001-R15',
        ]);

        $response = $this->get(route('public.test-report.show', ['token' => $document->token]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline;', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('260909054-001-R15.pdf', (string) $response->headers->get('Content-Disposition'));

        $document->refresh();
        $this->assertSame(1, $document->view_count);
        $this->assertNotNull($document->last_viewed_at);
    }

    public function test_unknown_token_shows_not_found_page(): void
    {
        $response = $this->get(route('public.test-report.show', ['token' => 'UnknownToken1']));

        $response->assertNotFound();
        $response->assertSee('Report not found');
    }

    public function test_draft_document_is_never_served(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('reports/customer/samples/TRR_sample.pdf', '%PDF-1.4 draft');

        $document = TestReportDocument::factory()->withFile()->draft()->create();

        $this->get(route('public.test-report.show', ['token' => $document->token]))
            ->assertNotFound()
            ->assertSee('Report not found');
    }

    public function test_document_without_stored_file_shows_not_available_page(): void
    {
        Storage::fake('public');

        $document = TestReportDocument::factory()->withFile('reports/customer/samples/missing.pdf')->create();

        $this->get(route('public.test-report.show', ['token' => $document->token]))
            ->assertNotFound()
            ->assertSee('Report not available');
    }

    public function test_invalid_token_format_does_not_match_route(): void
    {
        $this->get('/r/bad-token!')->assertNotFound();
    }

    public function test_reserving_same_revision_and_language_reuses_token(): void
    {
        $batch = new SampleHeader;
        $batch->id = (string) Str::uuid();
        $batch->batch_code = '260909054';

        $sampleId = (string) Str::uuid();
        $samples = [(object) ['id' => $sampleId, 'sample_code' => '260909054-001']];

        $service = app(SampleTestReportDocumentService::class);

        $first = $service->reserveForSamples($batch, 15, 'en', '260909054-R15', $samples);
        $second = $service->reserveForSamples($batch, 15, 'en', '260909054-R15', $samples);
        $arabic = $service->reserveForSamples($batch, 15, 'ar', '260909054-R15', $samples);
        $nextRevision = $service->reserveForSamples($batch, 16, 'en', '260909054-R16', $samples);

        $this->assertSame($first[$sampleId]->token, $second[$sampleId]->token);
        $this->assertNotSame($first[$sampleId]->token, $arabic[$sampleId]->token);
        $this->assertNotSame($first[$sampleId]->token, $nextRevision[$sampleId]->token);
        $this->assertSame(1, TestReportDocument::query()
            ->where('sample_detail_id', $sampleId)
            ->where('revision_no', 15)
            ->where('language', 'en')
            ->count());
        $this->assertStringEndsWith('/r/'.$first[$sampleId]->token, $first[$sampleId]->publicUrl());
    }

    public function test_qr_code_is_an_svg_data_uri(): void
    {
        $qrCode = app(SampleTestReportDocumentService::class)
            ->qrCodeDataUri('https://amspec-dubai.imaralims.com/r/Ab3dEf7hIj9K');

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $qrCode);
        $this->assertSame('', app(SampleTestReportDocumentService::class)->qrCodeDataUri(''));
    }
}
