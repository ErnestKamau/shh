<?php

namespace App\Livewire\Billing;

use App\Livewire\Concerns\WithToastNotifications;
use App\Services\Commercial\QuotationApprovalConfigService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class QuotationApprovalSettings extends Component
{
    use AuthorizesRequests;
    use WithToastNotifications;

    public string $configTitle = '';

    public string $approverRole = '';

    public string $editTitle = '';

    public string $editRole = '';

    public bool $showEditModal = false;

    /** @var list<array{id: string|int, name: string}> */
    public array $roleOptions = [];

    public function mount(QuotationApprovalConfigService $config): void
    {
        $this->configTitle = $config->configurationTitle();
        $this->approverRole = $config->approverRoleName();
        $this->editTitle = $this->configTitle;
        $this->editRole = $this->approverRole;
        $this->roleOptions = $config->availableRoles();
    }

    public function openEditModal(): void
    {
        $this->editTitle = $this->configTitle;
        $this->editRole = $this->approverRole;
        $this->resetErrorBag();
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->resetErrorBag();
    }

    public function save(QuotationApprovalConfigService $config): void
    {
        $this->validate([
            'editTitle' => ['required', 'string', 'max:120'],
            'editRole' => ['required', 'string', 'max:120'],
        ], [
            'editTitle.required' => 'Enter a name for this approval.',
            'editRole.required' => 'Select a role.',
        ]);

        try {
            $config->saveConfiguration($this->editTitle, $this->editRole);
            $this->configTitle = trim($this->editTitle);
            $this->approverRole = $this->editRole;
            $this->showEditModal = false;
            $this->imaraToast(
                'success',
                'Approval settings saved',
                'Users with the '.$this->approverRole.' role can approve quotations.'
            );
        } catch (\Throwable $exception) {
            $this->imaraToast('error', 'Could not save settings', $exception->getMessage());
        }
    }

    public function render(QuotationApprovalConfigService $config)
    {
        return view('livewire.billing.quotation-approval-settings', [
            'approverUsers' => $config->usersForApproverRole($this->approverRole),
        ]);
    }
}
