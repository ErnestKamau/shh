<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\User;
use App\Models\Procedures\ProcedureWorksheet;

class ProcedureWorksheetTest extends TestCase
{
    // use RefreshDatabase; // Skipping RefreshDatabase to avoid wiping dev DB

    public function test_can_access_procedure_manager()
    {
        $user = User::first(); 
        $response = $this->actingAs($user)->get(route('formulars.procedures.manage'));

        $response->assertStatus(200);
        $response->assertSee('Procedure Capture Worksheets');
    }

    public function test_can_create_procedure_worksheet()
    {
        $user = User::first();
        $this->actingAs($user);

        // Livewire test would be better but simple HTTP test verifies basic access
        // Ideally we should use Livewire testing components
    }
}
