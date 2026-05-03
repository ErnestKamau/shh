<?php

namespace App\Livewire\System;

use App\Exports\System\TranslationsExport;
use App\Models\System\Language;
use App\Models\System\TranslationLanguageLine;
use App\Services\System\TranslationManagementService;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class TranslationList extends Component
{
    use WithPagination;

    public string $groupFilter = '';
    public string $keySearch = '';
    public int $perPage = 15;
    public ?string $deleteLineId = null;
    public ?string $inlineEditId = null;
    public string $inlineGroup = '';
    public string $inlineKey = '';
    /** @var array<string, string> */
    public array $inlineText = [];

    protected $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        $this->authorizeAction('system.translations.keys.view');
    }

    public function updatingGroupFilter(): void
    {
        $this->resetPage();
    }

    public function updatingKeySearch(): void
    {
        $this->resetPage();
    }

    #[On('translation-saved')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function askDelete(string $id): void
    {
        $this->authorizeAction('system.translations.keys.delete');
        $this->deleteLineId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deleteLineId = null;
    }

    public function deleteTranslation(TranslationManagementService $translationService): void
    {
        $this->authorizeAction('system.translations.keys.delete');

        if ($this->deleteLineId === null) {
            return;
        }

        $line = TranslationLanguageLine::query()->findOrFail($this->deleteLineId);
        $translationService->deleteTranslation($line);

        $this->deleteLineId = null;
        session()->flash('success', 'Translation deleted successfully.');
        $this->resetPage();
    }

    public function getTranslationsProperty()
    {
        return TranslationLanguageLine::query()
            ->when($this->groupFilter !== '', fn ($query) => $query->where('group', $this->groupFilter))
            ->when($this->keySearch !== '', fn ($query) => $query->where('key', 'like', '%' . $this->keySearch . '%'))
            ->orderBy('group')
            ->orderBy('key')
            ->paginate($this->perPage);
    }

    public function getGroupsProperty(): Collection
    {
        return TranslationLanguageLine::query()
            ->select('group')
            ->distinct()
            ->orderBy('group')
            ->pluck('group');
    }

    public function startInlineEdit(string $id): void
    {
        $this->authorizeAction('system.translations.keys.edit');

        $line = TranslationLanguageLine::query()->findOrFail($id);
        $this->inlineEditId = $line->id;
        $this->inlineGroup = (string) $line->group;
        $this->inlineKey = (string) $line->key;

        $text = is_array($line->text) ? $line->text : [];
        $this->inlineText = [];
        foreach ($this->activeLanguageCodes as $code) {
            $this->inlineText[$code] = (string) ($text[$code] ?? '');
        }
    }

    public function cancelInlineEdit(): void
    {
        $this->inlineEditId = null;
        $this->inlineGroup = '';
        $this->inlineKey = '';
        $this->inlineText = [];
    }

    public function saveInlineEdit(TranslationManagementService $translationService): void
    {
        $this->authorizeAction('system.translations.keys.edit');

        if ($this->inlineEditId === null) {
            return;
        }

        $this->validate([
            'inlineGroup' => ['required', 'string', 'max:100'],
            'inlineKey' => ['required', 'string', 'max:100'],
            'inlineText.*' => ['nullable', 'string'],
        ]);

        $payload = collect($this->inlineText)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '')
            ->toArray();

        if ($payload === []) {
            $this->addError('inlineText', 'At least one language value is required.');
            return;
        }

        $translationService->upsertTranslation($this->inlineGroup, $this->inlineKey, $payload);
        session()->flash('success', 'Translation updated successfully.');
        $this->cancelInlineEdit();
    }

    public function exportJson()
    {
        $this->authorizeAction('system.translations.keys.view');

        $lines = TranslationLanguageLine::query()
            ->select(['group', 'key', 'text'])
            ->orderBy('group')
            ->orderBy('key')
            ->get();

        $grouped = [];

        foreach ($lines as $line) {
            if (!isset($grouped[$line->group])) {
                $grouped[$line->group] = [];
            }

            $grouped[$line->group][$line->key] = is_array($line->text) ? $line->text : [];
        }

        return response()->streamDownload(function () use ($grouped): void {
            echo json_encode(['data' => $grouped], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, 'translations_' . now()->format('Ymd_His') . '.json', [
            'Content-Type' => 'application/json',
        ]);
    }

    public function exportCsv()
    {
        $this->authorizeAction('system.translations.keys.view');

        return Excel::download(
            new TranslationsExport($this->activeLanguageCodes),
            'translations_' . now()->format('Ymd_His') . '.csv'
        );
    }

    /**
     * @return array<int, string>
     */
    public function getActiveLanguageCodesProperty(): array
    {
        return Language::query()->active()->orderBy('name')->pluck('code')->all();
    }

    public function missingCount(TranslationLanguageLine $line): int
    {
        $missing = 0;
        $text = is_array($line->text) ? $line->text : [];

        foreach ($this->activeLanguageCodes as $code) {
            $value = trim((string) ($text[$code] ?? ''));
            if ($value === '') {
                $missing++;
            }
        }

        return $missing;
    }

    public function render()
    {
        return view('livewire.system.translation-list');
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
