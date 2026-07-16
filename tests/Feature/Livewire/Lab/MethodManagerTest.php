<?php

namespace Tests\Feature\Livewire\Lab;

use App\Livewire\Lab\MethodManager;
use Livewire\Livewire;
use Tests\TestCase;

class MethodManagerTest extends TestCase
{
    public function test_renders_when_reference_methods_are_provided_as_array(): void
    {
        Livewire::test(MethodManager::class)
            ->set('referenceMethods', [['id' => 1, 'name' => 'Reference Method']])
            ->assertStatus(200);
    }
}
