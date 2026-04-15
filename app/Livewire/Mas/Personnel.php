<?php

namespace App\Livewire\Mas;

use Illuminate\Support\Facades\DB;

class Personnel extends BaseMasPage
{
    public array $stats = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function refresh(): void
    {
        $this->loadStats();
    }

    private function loadStats(): void
    {
        $this->stats = [
            'total_staff'    => DB::table('users')->count(),
            'active_users'   => DB::table('users')->where('active', 1)->count(),
            'departments'    => DB::table('inventory_departments')->count(),
            'certifications' => DB::table('personel_certifications')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.mas.personnel', ['stats' => $this->stats]);
    }
}
