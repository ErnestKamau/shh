<?php

namespace Tests\Unit\Services;

use App\Enums\Procedures\ProcedureStepTableColumnType;
use App\Enums\Procedures\ProcedureTableRowDriver;
use App\Models\Procedures\ProcedureWorksheetStep;
use PHPUnit\Framework\TestCase;

class ProcedureStepTableRowGeneratorServiceTest extends TestCase
{
    public function test_row_driver_enum_includes_equipment(): void
    {
        $this->assertContains('equipment', array_column(ProcedureTableRowDriver::cases(), 'value'));
    }

    public function test_column_type_options_match_log_entry_shape(): void
    {
        $this->assertSame(['input', 'derived', 'dataset'], array_keys(ProcedureStepTableColumnType::options()));
    }

    public function test_custom_table_step_detection(): void
    {
        $step = new ProcedureWorksheetStep([
            'value_type' => 'custom_table',
        ]);

        $this->assertTrue($step->isCustomTable());
    }
}
