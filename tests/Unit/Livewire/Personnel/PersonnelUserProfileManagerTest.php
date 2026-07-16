<?php

namespace Tests\Unit\Livewire\Personnel;

use App\Livewire\Personnel\PersonnelUserProfileManager;
use ReflectionClass;
use Tests\TestCase;

class PersonnelUserProfileManagerTest extends TestCase
{
    public function test_step_two_validation_uses_spatie_roles_for_position(): void
    {
        $source = file_get_contents(
            (new ReflectionClass(PersonnelUserProfileManager::class))->getFileName()
        );

        $this->assertStringContainsString("Rule::exists('spatie_roles', 'id')", $source);
        $this->assertStringContainsString("where('type', 'Job Description')", $source);
        $this->assertStringNotContainsString(
            "'selectedPositionId' => 'nullable|string|exists:module_pre_configs,id'",
            $source
        );
    }
}
