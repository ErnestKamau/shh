<?php

namespace Tests\Unit\Analysis;

use App\Livewire\Analysis\ElementManager;
use ReflectionClass;
use Tests\TestCase;

class ElementManagerDropdownSearchTest extends TestCase
{
    public function test_method_options_query_uses_case_insensitive_search_bindings(): void
    {
        $component = new ElementManager;
        $method = (new ReflectionClass($component))->getMethod('queryMethodOptions');
        $method->setAccessible(true);

        $query = $method->invoke($component, 'ICP');
        $driver = $query->getConnection()->getDriverName();
        $sql = $query->toSql();
        $bindings = $query->getBindings();

        if ($driver === 'pgsql') {
            $this->assertStringContainsString('ilike', strtolower($sql));
            $this->assertContains('%ICP%', $bindings);
        } else {
            $this->assertStringContainsString('LOWER(name) LIKE ?', $sql);
            $this->assertContains('%icp%', $bindings);
        }
    }

    public function test_updated_search_hooks_reopen_dropdowns(): void
    {
        $component = new ElementManager;
        $component->analyteSearch = 'fructose';
        $component->formularSearch = 'hatche';
        $component->showAnalyteDropdown = false;
        $component->showFormularDropdown = false;

        $component->updatedAnalyteSearch();
        $component->updatedFormularSearch();

        $this->assertTrue($component->showAnalyteDropdown);
        $this->assertTrue($component->showFormularDropdown);
    }

    public function test_close_all_dropdowns_clears_open_flags(): void
    {
        $component = new ElementManager;
        $component->showAnalyteDropdown = true;
        $component->showFormularDropdown = true;
        $component->showLabSectionDropdown = true;

        $component->closeAllDropdowns();

        $this->assertFalse($component->showAnalyteDropdown);
        $this->assertFalse($component->showFormularDropdown);
        $this->assertFalse($component->showLabSectionDropdown);
    }
}
