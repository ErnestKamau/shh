<?php

namespace Tests\Feature\Livewire\SubmissionForms;

use App\Livewire\SubmissionForms\SubmissionFormRegistry;
use Livewire\Livewire;
use Tests\TestCase;

class SubmissionFormRegistryTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(SubmissionFormRegistry::class)
            ->assertSuccessful();
    }
}
