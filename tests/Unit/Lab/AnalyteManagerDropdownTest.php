<?php

namespace Tests\Unit\Lab;

use App\Livewire\Lab\AnalyteManager;
use ReflectionClass;
use Tests\TestCase;

class AnalyteManagerDropdownTest extends TestCase
{
    public function test_normalize_id_list_casts_and_deduplicates_values(): void
    {
        $component = new AnalyteManager;
        $method = (new ReflectionClass($component))->getMethod('normalizeIdList');
        $method->setAccessible(true);

        $normalized = $method->invoke($component, [
            '  uuid-1  ',
            '',
            null,
            'uuid-1',
            42,
            'uuid-2',
        ]);

        $this->assertSame(['uuid-1', '42', 'uuid-2'], $normalized);
    }

    public function test_filtered_option_queries_use_boolean_active_and_search_bindings(): void
    {
        $component = new AnalyteManager;
        $reflection = new ReflectionClass($component);

        $methodQuery = $reflection->getMethod('methodOptionsQuery');
        $methodQuery->setAccessible(true);
        $methods = $methodQuery->invoke($component, 'ICP');

        $equipmentQuery = $reflection->getMethod('equipmentOptionsQuery');
        $equipmentQuery->setAccessible(true);
        $equipment = $equipmentQuery->invoke($component, 'HPLC');

        $unitQuery = $reflection->getMethod('reportingUnitOptionsQuery');
        $unitQuery->setAccessible(true);
        $units = $unitQuery->invoke($component, 'mg');

        $this->assertSame([true, '%ICP%', '%ICP%'], $methods->getBindings());
        $this->assertSame([true, '%HPLC%', '%HPLC%'], $equipment->getBindings());
        $this->assertSame([true, '%mg%'], $units->getBindings());

        $this->assertStringContainsString('"active" = ?', $methods->toSql());
        $this->assertStringContainsString('"active" = ?', $equipment->toSql());
        $this->assertStringContainsString('"active" = ?', $units->toSql());
    }

    public function test_filtered_collections_stay_empty_until_dropdown_is_opened(): void
    {
        $component = new AnalyteManager;
        $component->methodSearch = 'anything';
        $component->equipmentSearch = 'anything';
        $component->reportingUnitSearch = 'anything';
        $component->showMethodDropdown = false;
        $component->showEquipmentDropdown = false;
        $component->showReportingUnitDropdown = false;

        $this->assertTrue($component->filteredMethods->isEmpty());
        $this->assertTrue($component->filteredEquipment->isEmpty());
        $this->assertTrue($component->filteredReportingUnits->isEmpty());
    }

    public function test_add_and_remove_method_keep_string_ids(): void
    {
        $component = new AnalyteManager;
        $component->analyteForm['method'] = [];

        $component->addMethod('method-a');
        $component->addMethod('method-a');
        $component->addMethod('method-b');

        $this->assertSame(['method-a', 'method-b'], $component->analyteForm['method']);

        $component->removeMethod('method-a');

        $this->assertSame(['method-b'], $component->analyteForm['method']);
        $this->assertFalse($component->showMethodDropdown);
        $this->assertSame('', $component->methodSearch);
    }
}
