<?php

namespace App\Livewire\Crm;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Crm\Traits\HasCrmPermissions;

abstract class BaseCrmComponent extends Component
{
    use HasCrmPermissions;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    /**
     * Initialize method called by child components in mount()
     */
    protected function initialize()
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Get current user's company ID
     */
    protected function getUserCompany()
    {
        return getUserCompany();
    }

    /**
     * Get company details (name, etc.) for the current user's company
     *
     * @return array<string, mixed>
     */
    protected function getCompanyDetails(): array
    {
        return getCompanyDetails();
    }

    /**
     * Show success flash message and dispatch event
     */
    protected function showSuccess($message)
    {
        session()->flash('success', $message);
        $this->dispatch('notify', type: 'success', message: $message);
    }

    /**
     * Show error flash message and dispatch event
     */
    protected function showError($message)
    {
        session()->flash('error', $message);
        $this->dispatch('notify', type: 'error', message: $message);
    }

    /**
     * Properties that trigger a page reset when updated
     * Can be overridden by child components
     */
    protected $resetPageOnUpdate = ['search', 'activeFilter', 'customerFilter', 'typeFilter', 'statusFilter', 'priorityFilter', 'perPage'];

    /**
     * Reset pagination when filters change
     */
    public function updating($name, $value)
    {
        if (in_array($name, $this->resetPageOnUpdate)) {
            $this->resetPage();
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
