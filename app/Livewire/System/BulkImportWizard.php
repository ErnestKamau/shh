<?php

namespace App\Livewire\System;

use App\Company;
use App\Models\BulkImportBatch;
use App\Models\DataImportVersion;
use App\Services\BulkImportService;
use App\Services\ImportVersioning\DataImportVersionService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BulkImportWizard extends Component
{
    use WithFileUploads;

    /** @var list<string> */
    private const REPLACEABLE_FORM_TYPES = [
        'amspec_parameters',
        'analysis_type',
        'analyte',
        'analysis_method',
        'lab_hierarchy',
        'equipment',
        'customer',
    ];

    public int $currentStep = 1;
    public ?string $selectedModule = null;
    public ?string $selectedFormType = null;
    public ?BulkImportBatch $currentBatch = null;
    public $uploadedFile;
    public array $availableModules = [];
    public array $formTypes = [];
    public array $importResults = [];
    public string $message = '';
    public string $messageType = 'success';
    public ?string $selectedZoneId = null;
    public array $zones = [];
    public bool $replaceExisting = false;
    public string $purgeConfirmation = '';
    public bool $showImportVersionsModal = false;
    /** @var list<array{id: string, version: int, status: string, applied_at: ?string, user_name: ?string, summary: string, notes: ?string}> */
    public array $importVersions = [];

    public function mount()
    {
        $this->availableModules = $this->bulkImportService()->getAvailableModules();
        $this->zones = \App\Zone::orderBy('value')->get()->toArray();

        // Check if user has permission
        if (!Auth::user()->isSystemAdmin()) {
            return redirect('/')->with('error', 'Unauthorized access');
        }
    }

    public function selectModule(string $module): void
    {
        $this->selectedModule = $module;
        $this->formTypes = $this->availableModules[$module]['forms'] ?? [];
        $this->currentStep = 2;
        $this->message = '';
    }

    public function selectFormType(string $formType): void
    {
        $this->selectedFormType = $formType;
        $this->replaceExisting = $formType === 'analysis_method';
        $this->purgeConfirmation = '';
        $this->currentStep = 3;
        $this->message = '';
    }

    public function downloadTemplate()
    {
        try {
            if (!$this->selectedModule || !$this->selectedFormType) {
                $this->message = 'Please select both module and form type';
                $this->messageType = 'error';
                return;
            }

            return $this->bulkImportService()->generateTemplate(
                $this->selectedModule,
                $this->selectedFormType
            );
        } catch (\Exception $e) {
            $this->message = 'Error downloading template: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function downloadCurrentData()
    {
        try {
            if (! $this->selectedModule || ! $this->selectedFormType) {
                $this->message = 'Please select both module and form type';
                $this->messageType = 'error';

                return;
            }

            if ($this->selectedModule !== 'lab') {
                $this->message = 'Current-data export is available for Lab Management forms.';
                $this->messageType = 'error';

                return;
            }

            return $this->bulkImportService()->generateCurrentDataExport(
                $this->selectedModule,
                $this->selectedFormType
            );
        } catch (\Exception $e) {
            $this->message = 'Error downloading current data: '.$e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function openImportVersionsModal(): void
    {
        if ($this->selectedModule !== 'lab' || ! $this->selectedFormType) {
            $this->message = 'Version history is available for Lab Management forms.';
            $this->messageType = 'error';

            return;
        }

        $this->refreshImportVersions();
        $this->showImportVersionsModal = true;
    }

    public function closeImportVersionsModal(): void
    {
        $this->showImportVersionsModal = false;
    }

    public function refreshImportVersions(): void
    {
        if (! $this->selectedModule || ! $this->selectedFormType) {
            $this->importVersions = [];

            return;
        }

        $scopeKey = DataImportVersion::labMasterScopeKey($this->selectedModule, $this->selectedFormType);
        $this->importVersions = app(DataImportVersionService::class)
            ->listVersions(DataImportVersion::SCOPE_LAB_MASTER, $scopeKey)
            ->map(static function (DataImportVersion $version): array {
                $summary = $version->change_summary ?? [];
                $bits = [];
                if (isset($summary['imported'])) {
                    $bits[] = (int) $summary['imported'].' imported';
                }
                if (isset($summary['errors'])) {
                    $bits[] = (int) $summary['errors'].' errors';
                }

                return [
                    'id' => (string) $version->id,
                    'version' => (int) $version->version,
                    'status' => (string) $version->status,
                    'applied_at' => optional($version->applied_at)?->format('Y-m-d H:i'),
                    'user_name' => $version->user?->name,
                    'summary' => $bits !== [] ? implode(', ', $bits) : '—',
                    'notes' => $version->notes,
                ];
            })
            ->values()
            ->all();
    }

    public function downloadImportVersion(string $versionId)
    {
        $version = DataImportVersion::query()->findOrFail($versionId);

        return app(DataImportVersionService::class)->downloadVersionFile(
            $version,
            ($this->selectedFormType ?? 'import').'-v'.$version->version.'.xlsx',
        );
    }

    public function restoreImportVersion(string $versionId): void
    {
        $version = DataImportVersion::query()->findOrFail($versionId);
        $result = $this->bulkImportService()->rollbackLabMasterVersion($version, $this->selectedZoneId);

        $this->refreshImportVersions();

        if (! ($result['success'] ?? false)) {
            $this->message = (string) ($result['message'] ?? 'Restore failed.');
            $this->messageType = 'error';

            return;
        }

        $newVersion = $result['version']->version ?? null;
        $this->message = 'Restored from version '.$version->version
            .($newVersion ? " (saved as version {$newVersion})" : '')
            .'.';
        $this->messageType = 'success';
        $this->showImportVersionsModal = false;
    }

    public function goToUpload(): void
    {
        $this->currentStep = 4;
        $this->message = '';
    }

    public function uploadFile(): void
    {
        $rules = [];
        $confirmationPhrase = $this->purgeConfirmationPhrase();

        if ($this->supportsReplaceExisting() && $this->replaceExisting) {
            $companyName = $this->resolveCompanyName();
            $rules['purgeConfirmation'] = [
                'required',
                'string',
                Rule::in(array_values(array_filter([$confirmationPhrase, $companyName]))),
            ];
        }

        $maxUploadKilobytes = $this->selectedFormType === 'analysis_method' ? 15360 : 5120;
        $rules['uploadedFile'] = 'required|file|mimes:xlsx,xls,csv|max:'.$maxUploadKilobytes;

        $this->validate($rules, [
            'purgeConfirmation.in' => 'Type '.$confirmationPhrase.' or your company name exactly to confirm.',
        ]);

        try {
            $this->currentBatch = $this->bulkImportService()->createBatch(
                $this->selectedModule,
                $this->selectedFormType
            );

            $shouldReplaceExisting = $this->replaceExisting && $this->supportsReplaceExisting();

            $results = $this->bulkImportService()->processImport(
                $this->currentBatch,
                $this->uploadedFile,
                $this->selectedZoneId,
                $shouldReplaceExisting
            );

            if ($results['success']) {
                $this->importResults = $results['summary'];
                $this->currentStep = 5;
                $this->message = 'File processed successfully!';
                $this->messageType = 'success';
            } else {
                $this->message = 'Error processing file: ' . $results['message'];
                $this->messageType = 'error';
            }
        } catch (\Exception $e) {
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function goBack(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
            $this->message = '';
        }
    }

    public function resetWizard(): void
    {
        $this->currentStep = 1;
        $this->selectedModule = null;
        $this->selectedFormType = null;
        $this->currentBatch = null;
        $this->uploadedFile = null;
        $this->importResults = [];
        $this->selectedZoneId = null;
        $this->replaceExisting = false;
        $this->purgeConfirmation = '';
        $this->message = '';
        $this->formTypes = [];
    }

    public function resolveCompanyName(): string
    {
        $companyId = Auth::user()->company_id ?? Auth::user()->inventory_location_id;

        if (! $companyId) {
            return '';
        }

        return (string) (Company::query()->whereKey($companyId)->value('name') ?? '');
    }

    public function supportsReplaceExisting(): bool
    {
        return in_array($this->selectedFormType, self::REPLACEABLE_FORM_TYPES, true);
    }

    public function purgeConfirmationPhrase(): string
    {
        return match ($this->selectedFormType) {
            'analysis_method' => 'DELETE ALL METHODS',
            'analysis_type' => 'DELETE ALL SAMPLE AND ANALYSIS TYPES',
            'analyte' => 'DELETE ALL ANALYTES',
            'equipment' => 'DELETE ALL EQUIPMENT',
            'customer' => 'DELETE ALL CUSTOMERS',
            'amspec_parameters', 'lab_hierarchy' => 'DELETE ALL LAB DATA',
            default => 'CONFIRM REPLACE',
        };
    }

    public function downloadErrorReport()
    {
        if (!$this->currentBatch) {
            return;
        }

        $errors = $this->currentBatch->getErrorSummary();
        $filename = 'import_errors_' . $this->currentBatch->id . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ];

        $callback = function () use ($errors) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Error Message', 'Row Numbers', 'Count']);
            
            foreach ($errors as $error) {
                fputcsv($file, [
                    $error['message'],
                    implode(', ', $error['rows']),
                    $error['count'],
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.system.bulk-import-wizard');
    }

    protected function bulkImportService(): BulkImportService
    {
        return app(BulkImportService::class);
    }
}
