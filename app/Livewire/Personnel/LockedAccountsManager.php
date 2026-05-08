<?php

namespace App\Livewire\Personnel;

use App\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class LockedAccountsManager extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 25;
    /** @var array<int, int> */
    public array $perPageOptions = [10, 25, 50, 100];
    public bool $showPasswordResetModal = false;
    public ?string $selectedUserId = null;
    public string $selectedUserName = '';
    public string $selectedUserEmail = '';
    public string $newPassword = '';
    public string $confirmPassword = '';
    public string $message = '';
    public string $messageType = 'success';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openPasswordResetModal(string $userId, string $userName, string $userEmail): void
    {
        $this->selectedUserId = $userId;
        $this->selectedUserName = $userName;
        $this->selectedUserEmail = $userEmail;
        $this->newPassword = '';
        $this->confirmPassword = '';
        $this->message = '';
        $this->showPasswordResetModal = true;
    }

    public function closePasswordResetModal(): void
    {
        $this->showPasswordResetModal = false;
        $this->selectedUserId = null;
        $this->newPassword = '';
        $this->confirmPassword = '';
    }

    public function resetPassword(): void
    {
        $this->validate([
            'newPassword' => ['required', 'string', 'min:8'],
            'confirmPassword' => ['required', 'same:newPassword'],
        ]);

        if (!$this->selectedUserId) {
            $this->message = __('personnel.invalid_user_id');
            $this->messageType = 'error';
            return;
        }

        try {
            $user = User::find($this->selectedUserId);

            if (!$user) {
                $this->message = __('personnel.user_not_found');
                $this->messageType = 'error';
                return;
            }

            $user->password = Hash::make($this->newPassword);
            $user->save();

            session()->flash('success', __('personnel.password_reset_success_for', ['name' => $user->name]));
            $this->closePasswordResetModal();
            $this->resetPage();
        } catch (\Exception $e) {
            $this->message = __('personnel.error_resetting_password') . ': ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function unlockAccount(string $userId): void
    {
        try {
            $user = User::find($userId);

            if (!$user) {
                session()->flash('error', __('personnel.user_not_found'));
                return;
            }

            $user->login_locked_by_admin_reset = false;
            $user->failed_login_attempts = 0;
            $user->save();

            session()->flash('success', __('personnel.account_unlocked_for', ['name' => $user->name]));
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', __('personnel.error_unlocking_account') . ': ' . $e->getMessage());
        }
    }

    public function render()
    {
        $lockedUsers = User::query()
            ->where('login_locked_by_admin_reset', true)
            ->where(function ($query) {
                $query->where('name', 'ilike', '%' . $this->search . '%')
                    ->orWhere('email', 'ilike', '%' . $this->search . '%')
                    ->orWhere('phone', 'ilike', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.personnel.locked-accounts-manager', [
            'lockedUsers' => $lockedUsers,
        ]);
    }
}
