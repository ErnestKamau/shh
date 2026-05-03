<?php

namespace App\Livewire\System;

use App\Models\System\Language;
use App\Models\System\TranslationLanguageLine;
use App\Services\System\TranslationManagementService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class TranslationForm extends Component
{
    public bool $showForm = false;
    public ?string $editingLineId = null;
    public string $group = '';
    public string $key = '';
    /** @var array<string, string> */
    public array $textInputs = [];
    /** @var array<int, App\Models\System\Language> */
    public array $languages = [];

    #[On('open-translation-create')]
    public function openCreate(?string $group = null): void
    {
        $this->authorizeAction('system.translations.keys.add');

        $this->editingLineId = null;
        $this->group = $group ? trim($group) : '';
        $this->key = '';
        $this->loadLanguages();
        $this->textInputs = $this->emptyLanguagePayload();
        $this->showForm = true;
    }

    #[On('open-translation-edit')]
    public function openEdit(string $id): void
    {
        $this->authorizeAction('system.translations.keys.edit');

        $line = TranslationLanguageLine::query()->findOrFail($id);
        $this->loadLanguages();

        $this->editingLineId = $line->id;
        $this->group = (string) $line->group;
        $this->key = (string) $line->key;

        $this->textInputs = $this->emptyLanguagePayload();
        foreach ($line->text as $code => $value) {
            $this->textInputs[(string) $code] = (string) $value;
        }

        $this->showForm = true;
    }

    public function close(): void
    {
        $this->showForm = false;
    }

    public function save(TranslationManagementService $translationService): void
    {
        $permission = $this->editingLineId === null
            ? 'system.translations.keys.add'
            : 'system.translations.keys.edit';

        $this->authorizeAction($permission);

        $this->validate([
            'group' => ['required', 'string', 'max:100'],
            'key' => [
                'required',
                'string',
                'max:100',
                Rule::unique('language_lines', 'key')
                    ->where('group', trim($this->group))
                    ->ignore($this->editingLineId),
            ],
            'textInputs.*' => ['nullable', 'string'],
        ]);

        $payload = collect($this->textInputs)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '')
            ->toArray();

        if ($payload === []) {
            $this->addError('textInputs', 'At least one language value is required.');
            return;
        }

        $translationService->upsertTranslation($this->group, $this->key, $payload);

        session()->flash('success', $this->editingLineId ? 'Translation updated successfully.' : 'Translation created successfully.');

        $this->showForm = false;
        $this->dispatch('translation-saved');
    }

    public function render()
    {
        return view('livewire.system.translation-form');
    }

    private function loadLanguages(): void
    {
        $this->languages = Language::query()->active()->orderBy('name')->get()->all();
    }

    /**
     * @return array<string, string>
     */
    private function emptyLanguagePayload(): array
    {
        $payload = [];

        foreach ($this->languages as $language) {
            $payload[(string) $language->code] = '';
        }

        return $payload;
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
