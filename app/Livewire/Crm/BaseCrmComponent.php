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

    public function toJSON(mixed $value = null): array
    {
        return [];
    }

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
     * Backwards-compatibility handler: some client-side code (or devtools)
     * may attempt to call `toJSON` on the Livewire proxy which results
     * in a MethodNotFoundException when not present. Provide a safe
     * handler that returns an empty array to avoid errors.
     */
}
