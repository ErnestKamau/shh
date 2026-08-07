<?php

namespace Tests\Unit\Imports;

use App\Imports\BaseImporter;
use PHPUnit\Framework\TestCase;

class BaseImporterEquipmentSplitTest extends TestCase
{
    public function test_split_imported_equipment_names_preserves_lc_ms_ms(): void
    {
        $importer = new class extends BaseImporter {
            protected function validateRow(array $row): array
            {
                return [];
            }

            protected function transformRow(array $row): mixed
            {
                return $row;
            }

            protected function importRow(array $transformedData, array $originalRow): bool
            {
                return true;
            }

            public function splitNames(?string $raw): array
            {
                return $this->splitImportedEquipmentNames($raw);
            }
        };

        $this->assertSame(['LC-MS/MS'], $importer->splitNames('LC-MS/MS'));
        $this->assertSame(['AMS/M/INS/037'], $importer->splitNames('AMS/M/INS/037'));
        $this->assertSame(['ICP-OES', 'AAS'], $importer->splitNames('ICP-OES, AAS'));
    }
}
