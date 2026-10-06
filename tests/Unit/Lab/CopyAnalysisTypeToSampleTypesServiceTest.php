<?php

namespace Tests\Unit\Lab;

use App\Livewire\Analysis\AnalysisTypeManager;
use App\Services\Lab\CopyAnalysisTypeToSampleTypesService;
use InvalidArgumentException;
use Tests\TestCase;

class CopyAnalysisTypeToSampleTypesServiceTest extends TestCase
{
    public function test_copy_rejects_empty_target_list(): void
    {
        $service = new CopyAnalysisTypeToSampleTypesService;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Select at least one target sample type.');

        $service->copy('01900000-0000-7000-8000-000000000001', []);
    }

    public function test_copy_rejects_non_uuid_targets(): void
    {
        $service = new CopyAnalysisTypeToSampleTypesService;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Select at least one target sample type.');

        $service->copy('01900000-0000-7000-8000-000000000001', ['not-a-uuid', '', null]);
    }

    public function test_livewire_add_and_remove_copy_targets_deduplicate(): void
    {
        $component = new AnalysisTypeManager;
        $component->copyTargetSampleTypeIds = [];

        $component->addCopyTargetSampleType('01900000-0000-7000-8000-000000000011');
        $component->addCopyTargetSampleType('01900000-0000-7000-8000-000000000011');
        $component->addCopyTargetSampleType('01900000-0000-7000-8000-000000000012');

        $this->assertSame([
            '01900000-0000-7000-8000-000000000011',
            '01900000-0000-7000-8000-000000000012',
        ], $component->copyTargetSampleTypeIds);

        $component->removeCopyTargetSampleType('01900000-0000-7000-8000-000000000011');

        $this->assertSame([
            '01900000-0000-7000-8000-000000000012',
        ], $component->copyTargetSampleTypeIds);
    }
}
