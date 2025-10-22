<?php

namespace Tests\Feature\Livewire\Lab;

use App\Livewire\Lab\LabSectionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class LabSectionManagerTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(LabSectionManager::class)
            ->assertStatus(200);
    }
}
