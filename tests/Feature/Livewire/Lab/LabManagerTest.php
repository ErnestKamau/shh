<?php

namespace Tests\Feature\Livewire\Lab;

use App\Livewire\Lab\LabManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class LabManagerTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(LabManager::class)
            ->assertStatus(200);
    }
}
