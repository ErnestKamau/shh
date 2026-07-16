<?php

namespace App\Livewire\System;

use App\Company;
use App\Models\BulkImportBatch;
use App\Services\BulkImportService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BulkImportWizard extends Component
{
    use WithFileUploads;

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

    public function goToUpload(): void
    {
        $this->currentStep = 4;
        $this->message = '';
    }

    public function uploadFile(): void
    {
        $rules = [];

        if ($this->selectedFormType === 'lab_hierarchy' && $this->replaceExisting) {
            $companyName = $this->resolveCompanyName();
            $rules['purgeConfirmation'] = [
                'required',
                'string',
                Rule::in(['DELETE ALL LAB DATA', $companyName]),
            ];
        }

        if ($this->selectedFormType === 'analysis_method' && $this->replaceExisting) {
            $companyName = $this->resolveCompanyName();
            $rules['purgeConfirmation'] = [
                'required',
                'string',
                Rule::in(['DELETE ALL METHODS', $companyName]),
            ];
        }

        $maxUploadKilobytes = $this->selectedFormType === 'analysis_method' ? 15360 : 5120;
        $rules['uploadedFile'] = 'required|file|mimes:xlsx,xls,csv|max:'.$maxUploadKilobytes;

        $this->validate($rules, [
            'purgeConfirmation.in' => $this->selectedFormType === 'analysis_method'
                ? 'Type DELETE ALL METHODS or your company name exactly to confirm.'
                : 'Type DELETE ALL LAB DATA or your company name exactly to confirm.',
        ]);

        try {
            $this->currentBatch = $this->bulkImportService()->createBatch(
                $this->selectedModule,
                $this->selectedFormType
            );

            $shouldReplaceExisting = $this->replaceExisting && in_array($this->selectedFormType, ['lab_hierarchy', 'analysis_method'], true);

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
