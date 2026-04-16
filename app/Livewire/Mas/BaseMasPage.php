<?php

namespace App\Livewire\Mas;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.mas.layout.app')]
abstract class BaseMasPage extends Component
{
    public function boot(): void
    {
        if (!Auth::check() || !Auth::user()->hasRole('Admin')) {
            abort(403, 'Unauthorized access. AI Analytics is restricted to Administrators.');
        }
    }

    /**
     * Safe serialization for frontend (e.g. JSON.stringify($wire)).
     * Prevents "Public method [toJSON] not found on component" when Alpine/Select2 serializes the component.
     */
    public function toJSON(): array
    {
        return ['id' => $this->getId()];
    }
}
