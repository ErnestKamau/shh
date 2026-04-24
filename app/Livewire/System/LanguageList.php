<?php

namespace App\Livewire\System;

use App\Models\System\Language;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class LanguageList extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;
    public ?int $deleteLanguageId = null;

    protected $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        $this->authorizeAction('System.components.Translations.View');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[On('language-saved')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function toggleActive(int $id): void
    {
        $this->authorizeAction('System.components.Translations.Edit');

        $language = Language::query()->findOrFail($id);

        if ($language->is_default) {
            session()->flash('error', 'Default language must remain active.');
            return;
        }

        $language->is_active = !$language->is_active;
        $language->save();

        session()->flash('success', 'Language status updated.');
    }

    public function setDefault(int $id): void
    {
        $this->authorizeAction('System.components.Translations.Edit');

        $language = Language::query()->findOrFail($id);
        $language->is_default = true;
        $language->is_active = true;
        $language->save();

        session()->flash('success', 'Default language updated.');
    }

    public function askDelete(int $id): void
    {
        $this->authorizeAction('System.components.Translations.Delete');
        $this->deleteLanguageId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deleteLanguageId = null;
    }

    public function deleteLanguage(): void
    {
        $this->authorizeAction('System.components.Translations.Delete');

        if ($this->deleteLanguageId === null) {
            return;
        }

        $language = Language::query()->findOrFail($this->deleteLanguageId);

        if (!$language->canBeDeleted()) {
            session()->flash('error', 'Default language cannot be deleted.');
            return;
        }

        $language->delete();
        $this->deleteLanguageId = null;
        session()->flash('success', 'Language deleted successfully.');
        $this->resetPage();
    }

    public function getLanguagesProperty()
    {
        return Language::query()
            ->when($this->search !== '', function ($query): void {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%');
            })
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.system.language-list');
    }

    private function authorizeAction(string $permission): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        if ((method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) || $user->can('System.permission') || $user->can($permission)) {
            return;
        }

        abort(403);
    }
}
