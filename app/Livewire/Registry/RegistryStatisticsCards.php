<?php

namespace App\Livewire\Registry;

use Livewire\Component;

class RegistryStatisticsCards extends Component
{
    public array $kpis = [];

    public function mount(array $kpis = []): void
    {
        $this->kpis = $kpis;
    }

    public function render()
    {
        return view('livewire.registry.registry-statistics-cards');
    }
}
