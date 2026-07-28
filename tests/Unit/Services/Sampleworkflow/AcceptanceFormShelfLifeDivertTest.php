<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\SampleHeader;
use App\Services\Sampleworkflow\AcceptanceFormService;
use App\Services\ShelfLife\ShelfLifeStudyBootstrapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class AcceptanceFormShelfLifeDivertTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_shelf_life_acceptance_reads_form_flag(): void
    {
        if (! Schema::hasColumn('analysis_acceptance_forms', 'is_shelf_life')) {
            $this->markTestSkipped('is_shelf_life column not migrated.');
        }

        $form = new AnalysisAcceptanceForm([
            'is_shelf_life' => true,
        ]);

        $method = new ReflectionMethod(AcceptanceFormService::class, 'isShelfLifeAcceptance');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke(app(AcceptanceFormService::class), $form));
    }

    public function test_shelf_life_batch_status_constant(): void
    {
        $this->assertSame('Shelf Life Study', ShelfLifeStudyBootstrapService::BATCH_STATUS);
    }

    public function test_sample_header_is_shelf_life_job_helper(): void
    {
        $header = new SampleHeader([
            'status' => ShelfLifeStudyBootstrapService::BATCH_STATUS,
            'is_shelf_life' => true,
        ]);

        $this->assertTrue($header->isShelfLifeJob());
    }
}
