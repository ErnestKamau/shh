<?php

namespace Tests\Feature\LogEntryWorksheets;

use App\AnalysisElements;
use App\AnalysisType;
use App\CapturedResult;
use App\Models\LogEntryWorksheets\LogEntryWorksheet;
use App\Models\LogEntryWorksheets\LogEntryWorksheetColumn;
use App\Models\LogEntryWorksheets\LogEntryWorksheetMandatoryField;
use App\Services\Analysis\CapturedResultWorksheetSyncService;
use App\Services\LogEntryWorksheets\LogEntryRowGeneratorService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Run locally only when PHPUnit uses an isolated test database (not dev PostgreSQL):
 * php artisan test --filter=LogEntryWorksheetTest
 */
class LogEntryWorksheetTest extends TestCase
{
    // Do not enable RefreshDatabase here unless phpunit.xml uses SQLite :memory:.

    public function test_manage_route_is_accessible(): void
    {
        $user = User::first();
        if (! $user) {
            $this->markTestSkipped('No users in database.');
        }

        $response = $this->actingAs($user)->get(route('formulars.log-entry-worksheets.manage'));

        $response->assertStatus(200);
        $response->assertSee('Log entry worksheets');
    }

    public function test_worksheet_editor_route_is_accessible(): void
    {
        $user = User::first();
        if (! $user) {
            $this->markTestSkipped('No users in database.');
        }

        $worksheet = LogEntryWorksheet::create([
            'name' => 'Test Log Entry '.uniqid(),
            'row_driver' => 'captured_result',
            'mandatory_fields_placement' => 'top',
            'allow_manual_rows' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('formulars.log-entry-worksheets.edit', $worksheet->id));

        $response->assertStatus(200);
        $response->assertSee($worksheet->name);
        $response->assertSee('Table columns');
        $response->assertSee('Mandatory fields');
    }

    public function test_sync_service_assigns_log_entry_worksheet_from_element(): void
    {
        $worksheet = LogEntryWorksheet::create([
            'name' => 'Sync Test '.uniqid(),
            'row_driver' => 'sample_detail',
            'mandatory_fields_placement' => 'bottom',
            'is_active' => true,
        ]);

        $captured = CapturedResult::query()->whereNotNull('analysis_type_id')->whereNotNull('analyte_id')->first();
        if (! $captured) {
            $this->markTestSkipped('No captured results available.');
        }

        $element = AnalysisElements::query()
            ->where('analysis_type_id', $captured->analysis_type_id)
            ->where('analyte_id', $captured->analyte_id)
            ->first();

        if (! $element) {
            $this->markTestSkipped('No matching analysis element.');
        }

        $element->log_entry_worksheet_id = $worksheet->id;
        $element->save();

        $captured->log_entry_worksheet_id = null;
        $captured->has_log_entry_worksheet = false;
        $captured->saveQuietly();

        $analysisType = AnalysisType::find($captured->analysis_type_id);
        $changed = app(CapturedResultWorksheetSyncService::class)->sync($captured, $analysisType, $element);

        $this->assertTrue($changed);
        $captured->refresh();
        $this->assertSame($worksheet->id, $captured->log_entry_worksheet_id);
        $this->assertTrue($captured->has_log_entry_worksheet);
    }

    public function test_row_generator_creates_auto_rows_for_sample_detail_driver(): void
    {
        $captured = CapturedResult::query()->whereNotNull('sample_header_id')->first();
        if (! $captured) {
            $this->markTestSkipped('No captured results.');
        }

        $batch = $captured->sampleHeader;
        if (! $batch) {
            $this->markTestSkipped('No sample header.');
        }

        $worksheet = LogEntryWorksheet::create([
            'name' => 'Row Gen '.uniqid(),
            'row_driver' => 'sample_detail',
            'mandatory_fields_placement' => 'top',
            'is_active' => true,
        ]);

        LogEntryWorksheetColumn::create([
            'log_entry_worksheet_id' => $worksheet->id,
            'label' => 'Note',
            'key' => 'note',
            'column_type' => 'input',
            'input_data_type' => 'string',
            'order' => 1,
        ]);

        LogEntryWorksheetMandatoryField::create([
            'log_entry_worksheet_id' => $worksheet->id,
            'label' => 'Operator',
            'field_type' => 'input',
            'field_value_name' => 'operator_name',
            'order' => 1,
            'is_required' => true,
        ]);

        $service = app(LogEntryRowGeneratorService::class);
        $instance = $service->firstOrCreateInstance($batch, $worksheet);
        $created = $service->syncAutoRows($instance, $batch);

        $this->assertGreaterThanOrEqual(0, $created);
        $this->assertGreaterThan(0, $instance->rows()->count());
    }
}
