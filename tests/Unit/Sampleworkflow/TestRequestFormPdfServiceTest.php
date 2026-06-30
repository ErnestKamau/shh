<?php

namespace Tests\Unit\Sampleworkflow;

use App\Models\TestRequestFormInstance;
use App\Services\Sampleworkflow\TestRequestFormPdfService;
use Tests\TestCase;

class TestRequestFormPdfServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped('TRF layer deprecated — see docs/deprecation/TRF_LAYER_MANIFEST.md');
    }

    public function test_download_filename_replaces_slashes_in_form_number(): void
    {
        $instance = new TestRequestFormInstance([
            'id' => '019edacd-1fd2-713d-9fd0-ce5a4cdaeb2a',
        ]);

        $service = app(TestRequestFormPdfService::class);

        $filename = $service->resolveDownloadFilename($instance, [
            'serialNumber' => 'TRFW001/26',
        ]);

        $this->assertSame('test-request-form-TRFW001-26.pdf', $filename);
    }

    public function test_download_filename_falls_back_to_instance_id_when_serial_is_empty(): void
    {
        $instance = new TestRequestFormInstance([
            'id' => '019edacd-1fd2-713d-9fd0-ce5a4cdaeb2a',
        ]);

        $service = app(TestRequestFormPdfService::class);

        $filename = $service->resolveDownloadFilename($instance, [
            'serialNumber' => '',
        ]);

        $this->assertSame('test-request-form-019edacd-1fd2-713d-9fd0-ce5a4cdaeb2a.pdf', $filename);
    }
}
