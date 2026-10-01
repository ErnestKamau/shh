<?php

namespace Tests\Feature\Sampleworkflow;

use App\Http\Requests\Sampleworkflow\StoreCollectionQrExtrasRequest;
use App\Models\CollectionQrCode;
use App\Models\TestReportDocument;
use App\Services\Sampleworkflow\CollectionQrCodeService;
use App\Services\Sampleworkflow\SampleTestReportDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;

class CollectionQrCodeTest extends TestCase
{
    use RefreshDatabase;

    private const SAMPLE_PDF = 'reports/customer/samples/TRR_sample.pdf';

    public function test_unknown_token_shows_not_recognised_page(): void
    {
        $this->get(route('public.collection-qr.show', ['token' => 'UnknownToken1']))
            ->assertNotFound()
            ->assertSee('QR code not recognised');
    }

    public function test_void_code_is_not_recognised(): void
    {
        $code = CollectionQrCode::factory()->void()->create();

        $this->get(route('public.collection-qr.show', ['token' => $code->token]))
            ->assertNotFound()
            ->assertSee('QR code not recognised');
    }

    public function test_invalid_token_format_does_not_match_route(): void
    {
        $this->get('/s/bad-token!')->assertNotFound();
    }

    public function test_unlinked_code_shows_no_report_generated_and_counts_scan(): void
    {
        $code = CollectionQrCode::factory()->forSchedule((string) Str::uuid())->create();

        $this->get(route('public.collection-qr.show', ['token' => $code->token]))
            ->assertOk()
            ->assertSee('No report generated');

        $code->refresh();
        $this->assertSame(1, $code->scan_count);
        $this->assertNotNull($code->last_scanned_at);
    }

    public function test_linked_code_without_issued_report_shows_no_report_generated(): void
    {
        $sampleId = (string) Str::uuid();
        $code = CollectionQrCode::factory()->linkedTo((string) Str::uuid(), $sampleId)->create();

        TestReportDocument::factory()->withFile()->draft()->create(['sample_detail_id' => $sampleId]);

        $this->get(route('public.collection-qr.show', ['token' => $code->token]))
            ->assertOk()
            ->assertSee('No report generated');
    }

    public function test_linked_code_redirects_to_latest_official_sample_report(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put(self::SAMPLE_PDF, '%PDF-1.4 sample');

        $batchId = (string) Str::uuid();
        $sampleId = (string) Str::uuid();
        $code = CollectionQrCode::factory()->linkedTo($batchId, $sampleId)->create();

        TestReportDocument::factory()->withFile()->create([
            'batch_id' => $batchId,
            'sample_detail_id' => $sampleId,
            'revision_no' => 1,
            'language' => 'en',
        ]);
        $latest = TestReportDocument::factory()->withFile()->create([
            'batch_id' => $batchId,
            'sample_detail_id' => $sampleId,
            'revision_no' => 2,
            'language' => 'en',
        ]);

        $this->get(route('public.collection-qr.show', ['token' => $code->token]))
            ->assertRedirect($latest->publicUrl());
    }

    public function test_latest_document_prefers_english_within_the_latest_revision(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put(self::SAMPLE_PDF, '%PDF-1.4 sample');

        $batchId = (string) Str::uuid();
        $sampleId = (string) Str::uuid();

        TestReportDocument::factory()->withFile()->create([
            'batch_id' => $batchId, 'sample_detail_id' => $sampleId, 'revision_no' => 3, 'language' => 'ar',
        ]);
        $english = TestReportDocument::factory()->withFile()->create([
            'batch_id' => $batchId, 'sample_detail_id' => $sampleId, 'revision_no' => 3, 'language' => 'en',
        ]);
        TestReportDocument::factory()->withFile()->create([
            'batch_id' => $batchId, 'sample_detail_id' => $sampleId, 'revision_no' => 2, 'language' => 'en',
        ]);

        $document = app(SampleTestReportDocumentService::class)->latestOfficialDocumentFor($sampleId);

        $this->assertNotNull($document);
        $this->assertSame($english->id, $document->id);
    }

    public function test_latest_document_ignores_documents_without_stored_file(): void
    {
        Storage::fake('public');

        $sampleId = (string) Str::uuid();
        TestReportDocument::factory()->withFile('reports/customer/samples/missing.pdf')->create([
            'sample_detail_id' => $sampleId,
        ]);

        $this->assertNull(app(SampleTestReportDocumentService::class)->latestOfficialDocumentFor($sampleId));
    }

    public function test_extras_quantity_is_validated(): void
    {
        $rules = (new StoreCollectionQrExtrasRequest)->rules();

        $this->assertTrue(Validator::make(['quantity' => 3], $rules)->passes());
        $this->assertTrue(Validator::make(['quantity' => 0], $rules)->fails());
        $this->assertTrue(Validator::make(['quantity' => CollectionQrCodeService::MAX_EXTRAS_PER_REQUEST + 1], $rules)->fails());
        $this->assertTrue(Validator::make([], $rules)->fails());
        $this->assertTrue(Validator::make(['quantity' => 2, 'layout' => 'label-roll'], $rules)->fails());
        $this->assertTrue(Validator::make(['quantity' => 2, 'layout' => 'thermal', 'copies' => 2], $rules)->passes());
    }

    public function test_public_url_uses_short_scan_route(): void
    {
        $code = CollectionQrCode::factory()->create(['token' => 'Ab3dEf7hIj9K']);

        $this->assertStringEndsWith('/s/Ab3dEf7hIj9K', $code->publicUrl());
    }
}
