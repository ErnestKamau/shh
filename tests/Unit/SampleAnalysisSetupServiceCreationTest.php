<?php

namespace Tests\Unit;

use App\AnalysisElements;
use App\AnalysisMethod;
use App\Analyte;
use App\CapturedResult;
use App\ReportingUnit;
use App\Result;
use App\SampleDetails;
use App\SampleHeader;
use App\Services\Sampleworkflow\SampleAnalysisSetupService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SampleAnalysisSetupServiceCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_captured_results_sets_analysis_element_id_and_unit_uuid(): void
    {
        if (! extension_loaded('pdo_pgsql') && config('database.default') === 'pgsql') {
            $this->markTestSkipped('pgsql unavailable in this environment');
        }

        $user = User::query()->create([
            'name' => 'Setup Tester',
            'email' => 'setup-tester-'.Str::random(6).'@example.com',
            'password' => bcrypt('password'),
            'active' => 1,
        ]);

        $batch = SampleHeader::query()->create([
            'batch_code' => 'JOB-TEST-'.Str::random(4),
            'status' => 'Samples In Lab',
            'created_by' => $user->id,
        ]);

        $detail = SampleDetails::query()->create([
            'sample_header_id' => $batch->id,
            'sample_code' => 'CH-001',
        ]);

        $analyte = Analyte::query()->create([
            'name' => 'pH',
            'code' => 'pH-'.Str::random(4),
            'active' => 1,
        ]);

        $unit = ReportingUnit::query()->create([
            'name' => 'pH_unit_'.Str::random(4),
            'active' => 1,
        ]);

        $analysisTypeId = (string) Str::uuid();
        $labSectionId = $this->createLabSection();

        $method = AnalysisMethod::query()->create([
            'name' => 'Valid Method '.Str::random(4),
            'code' => 'M-'.Str::upper(Str::random(4)),
            'active' => 1,
        ]);

        $element = AnalysisElements::query()->create([
            'analysis_type_id' => $analysisTypeId,
            'analyte_id' => $analyte->id,
            'method' => $method->id,
            'lab_section_id' => $labSectionId,
            'reporting_unit' => $unit->name,
            'active' => 1,
            'level' => 1,
            'non_accredited' => 0,
        ]);

        $service = app(SampleAnalysisSetupService::class);
        $service->createCapturedResultsForAnalysisType(
            (string) $batch->id,
            (string) $detail->id,
            $analysisTypeId,
            (string) $detail->sample_code,
            (string) $user->id,
        );

        $captured = CapturedResult::query()
            ->where('sample_header_id', $batch->id)
            ->where('analyte_id', $analyte->id)
            ->first();

        $this->assertNotNull($captured);
        $this->assertSame((string) $element->id, (string) $captured->analysis_element_id);
        $this->assertSame((string) $unit->id, (string) $captured->reporting_unit_id);
        $this->assertSame((string) $method->id, (string) $captured->method_id);
        $this->assertNull($captured->operator_id);

        $result = Result::query()->where('captured_result_id', $captured->id)->first();
        $this->assertNotNull($result);
        $this->assertSame($unit->name, $result->unit_code);
    }

    public function test_create_captured_results_nulls_orphaned_method_id(): void
    {
        if (! extension_loaded('pdo_pgsql') && config('database.default') === 'pgsql') {
            $this->markTestSkipped('pgsql unavailable in this environment');
        }

        $user = User::query()->create([
            'name' => 'Orphan Method Tester',
            'email' => 'orphan-method-'.Str::random(6).'@example.com',
            'password' => bcrypt('password'),
            'active' => 1,
        ]);

        $batch = SampleHeader::query()->create([
            'batch_code' => 'JOB-ORPH-'.Str::random(4),
            'status' => 'Samples In Lab',
            'created_by' => $user->id,
        ]);

        $detail = SampleDetails::query()->create([
            'sample_header_id' => $batch->id,
            'sample_code' => 'CH-ORPH-001',
        ]);

        $analyte = Analyte::query()->create([
            'name' => 'BARIUM',
            'code' => 'BA-'.Str::random(4),
            'active' => 1,
        ]);

        $unit = ReportingUnit::query()->create([
            'name' => 'mg_L_'.Str::random(4),
            'active' => 1,
        ]);

        $analysisTypeId = (string) Str::uuid();
        $orphanedMethodId = (string) Str::uuid();
        $labSectionId = $this->createLabSection();

        AnalysisElements::query()->create([
            'analysis_type_id' => $analysisTypeId,
            'analyte_id' => $analyte->id,
            'method' => $orphanedMethodId,
            'lab_section_id' => $labSectionId,
            'reporting_unit' => $unit->name,
            'active' => 1,
            'level' => 1,
            'non_accredited' => 0,
        ]);

        $this->assertFalse(AnalysisMethod::query()->whereKey($orphanedMethodId)->exists());

        app(SampleAnalysisSetupService::class)->createCapturedResultsForAnalysisType(
            (string) $batch->id,
            (string) $detail->id,
            $analysisTypeId,
            (string) $detail->sample_code,
            (string) $user->id,
        );

        $captured = CapturedResult::query()
            ->where('sample_header_id', $batch->id)
            ->where('analyte_id', $analyte->id)
            ->first();

        $this->assertNotNull($captured);
        $this->assertNull($captured->method_id);
    }

    private function createLabSection(): string
    {
        $sectionId = (string) Str::uuid();
        $labId = (string) Str::uuid();
        $companyId = (string) Str::uuid();

        DB::table('labs')->insert([
            'id' => $labId,
            'code' => 'LAB-'.Str::upper(Str::random(3)),
            'name' => 'Method Lab',
            'phone1' => '000',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sample_analysis_stages')->insert([
            'id' => $sectionId,
            'lab_id' => $labId,
            'company_id' => $companyId,
            'name' => 'CHEM',
            'code' => 'CHM',
            'active' => true,
            'is_sample_stage' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $sectionId;
    }

    public function test_sync_batch_lab_section_ids_from_analysis_types(): void
    {
        if (! extension_loaded('pdo_pgsql') && config('database.default') === 'pgsql') {
            $this->markTestSkipped('pgsql unavailable in this environment');
        }

        $sectionId = (string) Str::uuid();
        $labId = (string) Str::uuid();
        $companyId = (string) Str::uuid();

        \Illuminate\Support\Facades\DB::table('labs')->insert([
            'id' => $labId,
            'code' => 'LAB-'.Str::upper(Str::random(3)),
            'name' => 'Sync Lab',
            'phone1' => '000',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Illuminate\Support\Facades\DB::table('sample_analysis_stages')->insert([
            'id' => $sectionId,
            'lab_id' => $labId,
            'company_id' => $companyId,
            'name' => 'MICRO',
            'code' => 'MIC',
            'active' => true,
            'is_sample_stage' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $analysisType = \App\AnalysisType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Micro Type '.Str::random(4),
            'code' => 'MT-'.Str::upper(Str::random(4)),
            'lab_section_id' => $sectionId,
            'active' => 1,
        ]);

        $batch = SampleHeader::query()->create([
            'batch_code' => 'SYNC-'.Str::random(4),
            'status' => 'Samples In Lab',
        ]);

        $detail = SampleDetails::query()->create([
            'sample_header_id' => $batch->id,
            'sample_code' => 'MIC-001',
            'analysis_type_id' => $analysisType->id,
        ]);

        \App\SampleAnalysisTypeRelation::query()->create([
            'id' => (string) Str::uuid(),
            'analysis_type_id' => $analysisType->id,
            'batch_id' => $batch->id,
            'sample_detail_id' => $detail->id,
        ]);

        $csv = app(SampleAnalysisSetupService::class)->syncBatchLabSectionIdsFromAnalysisTypes($batch->fresh());

        $this->assertSame($sectionId, $csv);
        $this->assertSame($sectionId, (string) $batch->fresh()->lab_section_ids);
    }

    public function test_sync_captured_result_lab_sections_updates_stale_section_from_analysis_type(): void
    {
        if (! extension_loaded('pdo_pgsql') && config('database.default') === 'pgsql') {
            $this->markTestSkipped('pgsql unavailable in this environment');
        }

        $companyId = (string) Str::uuid();
        $labId = (string) Str::uuid();
        $oldSectionId = (string) Str::uuid();
        $newSectionId = (string) Str::uuid();

        DB::table('labs')->insert([
            'id' => $labId,
            'company_id' => $companyId,
            'name' => 'Sync Lab',
            'code' => 'SL',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ([
            [$oldSectionId, 'OLD', 'OLD'],
            [$newSectionId, 'NEW', 'NEW'],
        ] as [$id, $name, $code]) {
            DB::table('sample_analysis_stages')->insert([
                'id' => $id,
                'lab_id' => $labId,
                'company_id' => $companyId,
                'name' => $name,
                'code' => $code,
                'active' => true,
                'is_sample_stage' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $analysisType = \App\AnalysisType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Type '.Str::random(4),
            'code' => 'TY-'.Str::upper(Str::random(4)),
            'lab_id' => $labId,
            'lab_section_id' => $newSectionId,
            'active' => 1,
        ]);

        $analyte = Analyte::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Analyte '.Str::random(4),
            'code' => 'AN-'.Str::upper(Str::random(4)),
            'company_id' => $companyId,
            'active' => 1,
        ]);

        $element = AnalysisElements::query()->create([
            'id' => (string) Str::uuid(),
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $analyte->id,
            'lab_section_id' => $newSectionId,
            'company_id' => $companyId,
            'active' => 1,
            'level' => 1,
        ]);

        $batch = SampleHeader::query()->create([
            'batch_code' => 'CRS-'.Str::random(4),
            'status' => 'Samples In Lab',
        ]);

        $detail = SampleDetails::query()->create([
            'sample_header_id' => $batch->id,
            'sample_code' => 'CRS-001',
            'analysis_type_id' => $analysisType->id,
        ]);

        $captured = CapturedResult::query()->create([
            'sample_header_id' => $batch->id,
            'sample_detail_id' => $detail->id,
            'sample_detail_code' => $detail->sample_code,
            'analysis_type_id' => $analysisType->id,
            'analysis_element_id' => $element->id,
            'analyte_id' => $analyte->id,
            'lab_section_id' => $oldSectionId,
        ]);

        Result::query()->create([
            'sample_header_id' => $batch->id,
            'sample_detail_id' => $detail->id,
            'sample_detail_code' => $detail->sample_code,
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $analyte->id,
            'lab_section_id' => $oldSectionId,
        ]);

        $updated = app(SampleAnalysisSetupService::class)
            ->syncCapturedResultLabSectionsForSampleDetail($detail);

        $this->assertSame(1, $updated);
        $this->assertSame($newSectionId, (string) $captured->fresh()->lab_section_id);
        $this->assertSame(
            $newSectionId,
            (string) Result::query()->where('sample_detail_id', $detail->id)->value('lab_section_id')
        );
    }

    public function test_sync_batch_sample_type_id_keeps_primary_when_still_on_a_sample(): void
    {
        if (! extension_loaded('pdo_pgsql') && config('database.default') === 'pgsql') {
            $this->markTestSkipped('pgsql unavailable in this environment');
        }

        $nonseafood = \App\SampleType::query()->create([
            'name' => 'Nonseafood '.Str::random(4),
            'code' => 'NS-'.Str::upper(Str::random(4)),
            'active' => 1,
        ]);
        $seafood = \App\SampleType::query()->create([
            'name' => 'Seafood '.Str::random(4),
            'code' => 'SF-'.Str::upper(Str::random(4)),
            'active' => 1,
        ]);

        $batch = SampleHeader::query()->create([
            'batch_code' => 'ST-'.Str::random(4),
            'status' => 'Samples In Lab',
            'sample_type_id' => $nonseafood->id,
        ]);

        SampleDetails::query()->create([
            'sample_header_id' => $batch->id,
            'sample_code' => '001',
            'sample_type_id' => $nonseafood->id,
        ]);
        SampleDetails::query()->create([
            'sample_header_id' => $batch->id,
            'sample_code' => '002',
            'sample_type_id' => $seafood->id,
        ]);

        $service = app(SampleAnalysisSetupService::class);
        $primary = $service->syncBatchSampleTypeIdFromSamples($batch->fresh());
        $labels = $service->sampleTypeLabelsFromSamples($batch->fresh());

        $this->assertSame((string) $nonseafood->id, $primary);
        $this->assertSame((string) $nonseafood->id, (string) $batch->fresh()->sample_type_id);
        $this->assertCount(2, $labels);
        $this->assertEqualsCanonicalizing(
            [(string) $nonseafood->id, (string) $seafood->id],
            array_column($labels, 'id')
        );
    }

    public function test_sync_batch_sample_type_id_switches_when_primary_no_longer_on_samples(): void
    {
        if (! extension_loaded('pdo_pgsql') && config('database.default') === 'pgsql') {
            $this->markTestSkipped('pgsql unavailable in this environment');
        }

        $stale = \App\SampleType::query()->create([
            'name' => 'Stale '.Str::random(4),
            'code' => 'ST-'.Str::upper(Str::random(4)),
            'active' => 1,
        ]);
        $seafood = \App\SampleType::query()->create([
            'name' => 'Seafood '.Str::random(4),
            'code' => 'SF-'.Str::upper(Str::random(4)),
            'active' => 1,
        ]);

        $batch = SampleHeader::query()->create([
            'batch_code' => 'ST2-'.Str::random(4),
            'status' => 'Samples In Lab',
            'sample_type_id' => $stale->id,
        ]);

        SampleDetails::query()->create([
            'sample_header_id' => $batch->id,
            'sample_code' => '001',
            'sample_type_id' => $seafood->id,
        ]);

        $primary = app(SampleAnalysisSetupService::class)
            ->syncBatchSampleTypeIdFromSamples($batch->fresh());

        $this->assertSame((string) $seafood->id, $primary);
        $this->assertSame((string) $seafood->id, (string) $batch->fresh()->sample_type_id);
    }
}
