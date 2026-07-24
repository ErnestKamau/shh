<?php

namespace Tests\Unit\Standards;

use App\Livewire\Standards\StandardAnalytesManager;
use ReflectionClass;
use Tests\TestCase;

class StandardAnalytesManagerDropdownSearchTest extends TestCase
{
    public function test_analyte_search_uses_case_insensitive_bindings(): void
    {
        $component = new StandardAnalytesManager;
        $component->analyteSearch = 'fructose';
        $component->searchAnalytes();

        $this->assertTrue($component->showAnalyteDropdown);

        $method = (new ReflectionClass($component))->getMethod('applyCaseInsensitiveSearch');
        $this->assertTrue($method->isProtected());
    }

    public function test_updated_search_hooks_open_dropdowns(): void
    {
        $component = new StandardAnalytesManager;
        $component->analyteSearch = 'a';
        $component->standardValueSearch = 'a';
        $component->showAnalyteDropdown = false;
        $component->showStandardValueDropdown = false;

        $component->updatedAnalyteSearch();
        $component->updatedStandardValueSearch();

        $this->assertTrue($component->showAnalyteDropdown);
        $this->assertTrue($component->showStandardValueDropdown);
    }

    public function test_close_all_dropdowns_clears_open_flags(): void
    {
        $component = new StandardAnalytesManager;
        $component->showAnalyteDropdown = true;
        $component->showStandardValueDropdown = true;

        $component->closeAllDropdowns();

        $this->assertFalse($component->showAnalyteDropdown);
        $this->assertFalse($component->showStandardValueDropdown);
    }
}
