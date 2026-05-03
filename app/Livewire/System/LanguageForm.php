<?php

namespace App\Livewire\System;

use App\Models\System\Language;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class LanguageForm extends Component
{
    public bool $showForm = false;
    public ?string $editingLanguageId = null;
    public string $name = '';
    public string $code = '';
    public bool $is_active = true;
    public bool $is_default = false;

    #[On('open-language-create')]
    public function openCreate(): void
    {
        $this->authorizeAction('system.translations.language.add');

        $this->editingLanguageId = null;
        $this->name = '';
        $this->code = '';
        $this->is_active = true;
        $this->is_default = false;
        $this->showForm = true;
    }

    #[On('open-language-edit')]
    public function openEdit(string $id): void
    {
        $this->authorizeAction('system.translations.language.edit');

        $language = Language::query()->findOrFail($id);

        $this->editingLanguageId = $language->id;
        $this->name = (string) $language->name;
        $this->code = (string) $language->code;
        $this->is_active = (bool) $language->is_active;
        $this->is_default = (bool) $language->is_default;
        $this->showForm = true;
    }

    public function close(): void
    {
        $this->showForm = false;
    }

    public function save(): void
    {
        $permission = $this->editingLanguageId === null
            ? 'system.translations.language.add'
            : 'system.translations.language.edit';

        $this->authorizeAction($permission);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('languages', 'code')->ignore($this->editingLanguageId),
            ],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
        ]);

        $validated['code'] = strtolower(trim((string) $validated['code']));

        if ($this->editingLanguageId !== null) {
            $language = Language::query()->findOrFail($this->editingLanguageId);
            $language->fill($validated);
        } else {
            $language = new Language($validated);
        }

        if ($language->is_default) {
            $language->is_active = true;
        }

        $language->save();

        session()->flash('success', $this->editingLanguageId ? 'Language updated successfully.' : 'Language created successfully.');

        $this->showForm = false;
        $this->dispatch('language-saved');
    }

    public function render()
    {
        return view('livewire.system.language-form');
    }

    private function authorizeAction(string $permission): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        if ((method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) || $user->can($permission)) {
            return;
        }

        abort(403);
    }
}
