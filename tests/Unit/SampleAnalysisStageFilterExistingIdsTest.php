<?php

namespace Tests\Unit;

use App\Livewire\Personnel\PersonnelDetailManager;
use App\Livewire\Personnel\PersonnelUserProfileManager;
use App\SampleAnalysisStage;
use ReflectionClass;
use Tests\TestCase;

class SampleAnalysisStageFilterExistingIdsTest extends TestCase
{
    public function test_filter_existing_ids_returns_empty_for_blank_input(): void
    {
        $this->assertSame([], SampleAnalysisStage::filterExistingIds([]));
        $this->assertSame([], SampleAnalysisStage::filterExistingIds(['', '  ', null]));
    }

    public function test_personnel_managers_normalize_lab_section_ids_before_validation(): void
    {
        $detailSource = file_get_contents(
            (new ReflectionClass(PersonnelDetailManager::class))->getFileName()
        );
        $profileSource = file_get_contents(
            (new ReflectionClass(PersonnelUserProfileManager::class))->getFileName()
        );

        $this->assertStringContainsString(
            'SampleAnalysisStage::filterExistingIds',
            $detailSource
        );
        $this->assertStringContainsString(
            'SampleAnalysisStage::filterExistingIds',
            $profileSource
        );
        $this->assertStringContainsString(
            "'selectedLabSectionIds.*' => 'lab section'",
            $detailSource
        );
    }
}
