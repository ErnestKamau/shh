<?php

namespace App\Livewire\System;

use App\Models\System\Language;
use App\Services\System\TranslationManagementService;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TranslationBulkUpload extends Component
{
    use WithFileUploads;

    public string $mode = 'json';
    public string $jsonPayload = '';
    public $uploadFile;
    /** @var array<string, mixed> */
    public array $summary = [];
    /** @var array<int, array<string, mixed>> */
    public array $previewRows = [];
    /** @var array<int, array<string, mixed>> */
    public array $pendingRows = [];
    /** @var array<string, mixed> */
    public array $previewStats = [];

    public function mount(): void
    {
        $this->authorizeAction('System.components.Translations.Bulk Import');
    }

    public function previewImport(): void
    {
        $this->authorizeAction('System.components.Translations.Bulk Import');

        $rows = $this->mode === 'json'
            ? $this->parseJsonRows()
            : $this->parseFileRows();

        if ($rows === []) {
            $this->addError('jsonPayload', 'No rows were found to import.');
            return;
        }

        $this->pendingRows = $rows;
        $this->previewRows = array_slice($rows, 0, 20);
        $this->previewStats = [
            'total_rows' => count($rows),
            'previewed_rows' => count($this->previewRows),
            'truncated' => count($rows) > 20,
        ];
    }

    public function clearPreview(): void
    {
        $this->previewRows = [];
        $this->pendingRows = [];
        $this->previewStats = [];
        $this->summary = [];
    }

    public function confirmImport(TranslationManagementService $translationService): void
    {
        $this->authorizeAction('System.components.Translations.Bulk Import');

        if ($this->pendingRows === []) {
            $this->addError('jsonPayload', 'Please preview rows before confirming import.');
            return;
        }

        $languageCodes = Language::query()->active()->pluck('code')->all();
        $summary = $translationService->bulkUpsert($this->pendingRows, $languageCodes);

        $this->summary = $summary;
        $this->dispatch('translation-saved');

        $this->pendingRows = [];

        if ($summary['failed'] === 0) {
            session()->flash('success', 'Bulk import completed successfully.');
        } else {
            session()->flash('error', 'Bulk import finished with validation errors.');
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseJsonRows(): array
    {
        $this->validate([
            'jsonPayload' => ['required', 'string'],
        ]);

        $decoded = json_decode($this->jsonPayload, true);

        if (!is_array($decoded)) {
            $this->addError('jsonPayload', 'Invalid JSON payload.');
            return [];
        }

        return $decoded;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseFileRows(): array
    {
        $this->validate([
            'uploadFile' => ['required', 'file', 'mimes:csv,txt,xlsx'],
        ]);

        $extension = strtolower((string) $this->uploadFile->getClientOriginalExtension());

        if ($extension === 'xlsx') {
            return $this->parseExcelFile();
        }

        return $this->parseCsvFile();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseCsvFile(): array
    {
        $rows = [];
        $path = $this->uploadFile->getRealPath();
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return [];
        }

        $headers = fgetcsv($handle) ?: [];
        $headers = array_map(fn ($value) => strtolower(trim((string) $value)), $headers);

        while (($columns = fgetcsv($handle)) !== false) {
            if ($columns === [null] || $columns === []) {
                continue;
            }

            $rows[] = $this->mapRow($headers, $columns);
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseExcelFile(): array
    {
        $rows = [];
        $spreadsheet = IOFactory::load($this->uploadFile->getRealPath());
        $sheetRows = $spreadsheet->getActiveSheet()->toArray();

        if ($sheetRows === []) {
            return [];
        }

        $headers = array_shift($sheetRows) ?: [];
        $headers = array_map(fn ($value) => strtolower(trim((string) $value)), $headers);

        foreach ($sheetRows as $columns) {
            if (!is_array($columns)) {
                continue;
            }

            $values = array_map(fn ($value) => (string) $value, $columns);

            if (implode('', array_map('trim', $values)) === '') {
                continue;
            }

            $rows[] = $this->mapRow($headers, $values);
        }

        return $rows;
    }

    /**
     * @param array<int, string> $headers
     * @param array<int, mixed> $columns
     * @return array<string, mixed>
     */
    private function mapRow(array $headers, array $columns): array
    {
        $row = [];

        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }

            $row[$header] = $columns[$index] ?? null;
        }

        return $row;
    }

    public function render()
    {
        return view('livewire.system.translation-bulk-upload');
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
