<?php

namespace App\Livewire\Planner;

use Livewire\Component;
use App\Models\SamplingSchedule;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\SampleType;
use App\AnalysisType;
use App\AnalysisElements;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use App\Mail\SamplingScheduleNotification;
use App\Exports\SamplingSchedulesExport;
use Maatwebsite\Excel\Facades\Excel;

class ScheduleSamplingManager extends Component
{
    // UI State
    public $showModal = false;
    public $showViewModal = false;
    public $editingSchedule = null;
    public $viewingSchedule = null;

    public $search = '';
    public $message = '';
    public $messageType = '';

    // Filters
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $filterClientId = '';
    public $filterContactId = '';
    public $filterSampleTypeId = '';
    public $filterAnalysisTypeId = '';
    public $filterFrequency = '';
    public $filterMinSamples = '';
    public $filterMaxSamples = '';
    public $filterParameterId = '';
    public $showFilters = false;

    // Form fields
    public $form = [
        'title' => '',
        'crm_customer_id' => '',
        'contact_id' => '',
        'sampling_datetime' => '',
        'location' => '',
        'number_of_samples' => null, // Optional, defaults to 1
        'frequency' => 'One-time',
        'notify_client' => false,
        'personnel_id' => '',
        'description' => '',
    ];

    // Multi-sample entries: [{sample_type_id, analysis_type_id, parameters: [], analysisTypes: [], availableParameters: []}]
    public $sampleEntries = [];

    // Customer prefill
    public $contractValidFrom = '';
    public $contractValidTo = '';
    public $customerContacts = [];
    public $selectedContactEmail = '';
    public $selectedContactPhone = '';

    // Supporting data
    public $clients = [];
    public $users = [];
    public $allSampleTypes = [];
    public $allAnalysisTypes = [];
    public $allParameters = [];
    public $frequencies = ['One-time', 'Daily', 'Weekly', 'Monthly', 'Quarterly', 'Annually'];

    public function mount()
    {
        $this->runMigrations();
        $this->loadSupportingData();
    }

    protected function runMigrations()
    {
        try {
            Artisan::call('migrate');
        } catch (\Throwable $e) {
            Log::error("Migration failed in ScheduleSamplingManager: " . $e->getMessage());
        }

        // Direct schema fix for company_id type
        try {
            if (Schema::hasTable('sampling_schedules')) {
                $type = Schema::getColumnType('sampling_schedules', 'company_id');
                if ($type !== 'guid' && $type !== 'uuid' && $type !== 'string') {
                    DB::statement('ALTER TABLE sampling_schedules DROP COLUMN IF EXISTS company_id');
                    DB::statement('ALTER TABLE sampling_schedules ADD COLUMN company_id UUID');
                    DB::statement('CREATE INDEX IF NOT EXISTS idx_sampling_schedules_company_id ON sampling_schedules(company_id)');
                }

                // Ensure sample_details column exists
                if (!Schema::hasColumn('sampling_schedules', 'sample_details')) {
                    DB::statement("ALTER TABLE sampling_schedules ADD COLUMN sample_details JSON NULL");
                }
            }
        } catch (\Throwable $e) {
            Log::error("Direct schema fix failed: " . $e->getMessage());
        }
    }

    protected function loadSupportingData()
    {
        $this->clients = CRMCustomer::where('active', 1)->orderBy('name')->get()->toArray();
        $this->users = User::where('is_client', 0)
            ->whereNull('supplier_id')
            ->where('active', 1)
            ->where('is_support_staff', 0)
            ->orderBy('name')
            ->get()
            ->toArray();
        $this->allSampleTypes = SampleType::where('active', 1)->orderBy('name')->get()->toArray();
        $this->allAnalysisTypes = AnalysisType::where('active', 1)->orderBy('name')->get()->toArray();
        $this->allParameters = \App\Analyte::where('active', 1)->orderBy('name')->get()->toArray();
    }

    public function getSchedulesProperty()
    {
        $companyId = getUserCompany();

        $query = SamplingSchedule::with(['client', 'contact', 'sample_type', 'analysis_type', 'personnel'])
            ->where('company_id', $companyId)
            ->orderBy('sampling_datetime', 'desc');

        // General search
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('location', 'like', '%' . $search . '%')
                  ->orWhere('frequency', 'like', '%' . $search . '%')
                  ->orWhereHas('client', function ($sub) use ($search) {
                      $sub->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Date range filter
        if ($this->filterDateFrom) {
            $query->whereDate('sampling_datetime', '>=', $this->filterDateFrom);
        }
        if ($this->filterDateTo) {
            $query->whereDate('sampling_datetime', '<=', $this->filterDateTo);
        }

        // Client filter
        if ($this->filterClientId) {
            $query->where('crm_customer_id', $this->filterClientId);
        }

        // Contact filter
        if ($this->filterContactId) {
            $query->where('contact_id', $this->filterContactId);
        }

        // Sample type filter (from sample_details JSON)
        if ($this->filterSampleTypeId) {
            $query->where(function ($q) {
                $q->whereJsonContains('sample_details', [['sample_type_id' => $this->filterSampleTypeId]])
                  ->orWhere('sample_type_id', $this->filterSampleTypeId);
            });
        }

        // Analysis type filter (from sample_details JSON)
        if ($this->filterAnalysisTypeId) {
            $query->where(function ($q) {
                $q->whereJsonContains('sample_details', [['analysis_type_id' => $this->filterAnalysisTypeId]])
                  ->orWhere('analysis_type_id', $this->filterAnalysisTypeId);
            });
        }

        // Frequency filter
        if ($this->filterFrequency) {
            $query->where('frequency', $this->filterFrequency);
        }

        // Number of samples range
        if ($this->filterMinSamples !== '' && $this->filterMinSamples !== null) {
            $query->where('number_of_samples', '>=', (int) $this->filterMinSamples);
        }
        if ($this->filterMaxSamples !== '' && $this->filterMaxSamples !== null) {
            $query->where('number_of_samples', '<=', (int) $this->filterMaxSamples);
        }

        // Parameter filter (search in sample_details JSON)
        if ($this->filterParameterId) {
            $query->where(function ($q) {
                $q->whereJsonContains('sample_details', [['parameters' => [$this->filterParameterId]]])
                  ->orWhereJsonContains('parameters', $this->filterParameterId);
            });
        }

        return $query->get();
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function resetFilters()
    {
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->filterClientId = '';
        $this->filterContactId = '';
        $this->filterSampleTypeId = '';
        $this->filterAnalysisTypeId = '';
        $this->filterFrequency = '';
        $this->filterMinSamples = '';
        $this->filterMaxSamples = '';
        $this->filterParameterId = '';
        $this->search = '';
    }

    public function exportToPdf()
    {
        try {
            $schedules = $this->schedules;
            $company = getActiveCompany();
            
            // Get company logo path
            $logoPath = null;
            if ($company && !empty($company->logo)) {
                $fullPath = public_path($company->logo);
                if (file_exists($fullPath)) {
                    $logoPath = $fullPath;
                }
            }
            
            $pdf = \PDF::loadView('pdfs.sampling_schedules', [
                'schedules' => $schedules,
                'company' => $company,
                'logoPath' => $logoPath,
                'filters' => [
                    'date_from' => $this->filterDateFrom,
                    'date_to' => $this->filterDateTo,
                    'client_id' => $this->filterClientId,
                    'contact_id' => $this->filterContactId,
                    'sample_type_id' => $this->filterSampleTypeId,
                    'analysis_type_id' => $this->filterAnalysisTypeId,
                    'frequency' => $this->filterFrequency,
                    'min_samples' => $this->filterMinSamples,
                    'max_samples' => $this->filterMaxSamples,
                    'parameter_id' => $this->filterParameterId,
                ],
                'generatedAt' => now()->format('Y-m-d H:i:s'),
            ]);
            
            $filename = 'sampling_schedules_' . now()->format('Y-m-d_H-i-s') . '.pdf';
            
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename);
            
        } catch (\Exception $e) {
            $this->message = 'Error generating PDF: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function exportToExcel()
    {
        try {
            $schedules = $this->schedules;
            
            if ($schedules->count() === 0) {
                $this->message = 'No data to export.';
                $this->messageType = 'error';
                return;
            }
            
            $filename = 'sampling_schedules_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            
            return Excel::download(new SamplingSchedulesExport($schedules), $filename);
            
        } catch (\Exception $e) {
            $this->message = 'Error generating Excel: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    // ── Modal actions ──

    public function showCreateModal()
    {
        $this->resetForm();
        $this->editingSchedule = null;
        $this->showModal = true;
    }

    public function showEditModal($id)
    {
        $schedule = SamplingSchedule::findOrFail($id);
        $this->editingSchedule = $schedule;

        $this->form = [
            'title' => $schedule->title,
            'crm_customer_id' => $schedule->crm_customer_id,
            'contact_id' => $schedule->contact_id ?? '',
            'sampling_datetime' => $schedule->sampling_datetime ? $schedule->sampling_datetime->format('Y-m-d\TH:i') : '',
            'location' => $schedule->location ?? '',
            'number_of_samples' => $schedule->number_of_samples ?? 1,
            'frequency' => $schedule->frequency ?? 'One-time',
            'notify_client' => $schedule->notify_client ?? false,
            'personnel_id' => $schedule->personnel_id ?? '',
            'description' => $schedule->description ?? '',
        ];

        // Load sample entries from sample_details or legacy columns
        $this->sampleEntries = [];
        if (!empty($schedule->sample_details) && is_array($schedule->sample_details)) {
            foreach ($schedule->sample_details as $entry) {
                $stId = $entry['sample_type_id'] ?? '';
                $atId = $entry['analysis_type_id'] ?? '';
                $params = $entry['parameters'] ?? [];

                $analysisTypes = [];
                if ($stId) {
                    $analysisTypes = AnalysisType::where('sample_type_id', $stId)
                        ->where('active', 1)->get(['id', 'name'])->toArray();
                }

                $availableParameters = [];
                if ($atId) {
                    $availableParameters = $this->loadParametersForAnalysisType($atId);
                }

                $this->sampleEntries[] = [
                    'sample_type_id' => $stId,
                    'analysis_type_id' => $atId,
                    'parameters' => $params,
                    'analysisTypes' => $analysisTypes,
                    'availableParameters' => $availableParameters,
                ];
            }
        } elseif ($schedule->sample_type_id) {
            // Legacy single entry
            $analysisTypes = AnalysisType::where('sample_type_id', $schedule->sample_type_id)
                ->where('active', 1)->get(['id', 'name'])->toArray();
            $availableParameters = $schedule->analysis_type_id
                ? $this->loadParametersForAnalysisType($schedule->analysis_type_id)
                : [];

            $this->sampleEntries[] = [
                'sample_type_id' => $schedule->sample_type_id,
                'analysis_type_id' => $schedule->analysis_type_id ?? '',
                'parameters' => $schedule->parameters ?? [],
                'analysisTypes' => $analysisTypes,
                'availableParameters' => $availableParameters,
            ];
        }

        // Load customer data for prefill
        if ($schedule->crm_customer_id) {
            $this->loadCustomerDetails($schedule->crm_customer_id);
        }

        $this->showModal = true;
    }

    public function viewSchedule($id)
    {
        $this->viewingSchedule = SamplingSchedule::with(['client', 'contact', 'personnel'])
            ->findOrFail($id);
        $this->showViewModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showViewModal = false;
        $this->resetForm();
    }

    // ── Form management ──

    public function resetForm()
    {
        $this->form = [
            'title' => '',
            'crm_customer_id' => '',
            'contact_id' => '',
            'sampling_datetime' => '',
            'location' => '',
            'number_of_samples' => null, // Optional, defaults to 1
            'frequency' => 'One-time',
            'notify_client' => false,
            'personnel_id' => '',
            'description' => '',
        ];
        $this->sampleEntries = [];
        $this->contractValidFrom = '';
        $this->contractValidTo = '';
        $this->customerContacts = [];
        $this->selectedContactEmail = '';
        $this->selectedContactPhone = '';
        $this->editingSchedule = null;
        $this->viewingSchedule = null;
    }

    // ── Customer reactivity ──

    public function updatedFormCrmCustomerId($value)
    {
        $this->form['contact_id'] = '';
        $this->selectedContactEmail = '';
        $this->selectedContactPhone = '';

        if ($value) {
            $this->loadCustomerDetails($value);
        } else {
            $this->contractValidFrom = '';
            $this->contractValidTo = '';
            $this->customerContacts = [];
        }
    }

    protected function loadCustomerDetails($customerId)
    {
        $customer = CRMCustomer::find($customerId);
        if (!$customer) return;

        $this->contractValidFrom = $customer->contract_valid_from
            ? (is_string($customer->contract_valid_from) ? substr($customer->contract_valid_from, 0, 10) : $customer->contract_valid_from->format('Y-m-d'))
            : 'N/A';
        $this->contractValidTo = $customer->contract_valid_to
            ? (is_string($customer->contract_valid_to) ? substr($customer->contract_valid_to, 0, 10) : $customer->contract_valid_to->format('Y-m-d'))
            : 'N/A';

        $this->customerContacts = CustomerContact::where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => trim(($c->first_name ?? '') . ' ' . ($c->middle_name ?? '') . ' ' . ($c->last_name ?? '')),
                    'email' => $c->email,
                    'telephone' => $c->telephone,
                    'mobile' => $c->mobile,
                ];
            })
            ->toArray();
    }

    public function updatedFormContactId($value)
    {
        $this->selectedContactEmail = '';
        $this->selectedContactPhone = '';

        if ($value) {
            $contact = collect($this->customerContacts)->firstWhere('id', $value);
            if ($contact) {
                $this->selectedContactEmail = $contact['email'] ?? 'N/A';
                $this->selectedContactPhone = $contact['telephone'] ?? $contact['mobile'] ?? 'N/A';
            }
        }
    }

    // ── Multi-sample entry management ──

    public function addSampleEntry()
    {
        $this->sampleEntries[] = [
            'sample_type_id' => '',
            'analysis_type_id' => '',
            'parameters' => [],
            'analysisTypes' => [],
            'availableParameters' => [],
        ];
    }

    public function removeSampleEntry($index)
    {
        unset($this->sampleEntries[$index]);
        $this->sampleEntries = array_values($this->sampleEntries);
    }

    public function updatedSampleEntries($value, $key)
    {
        // $key format: "0.sample_type_id" or "0.analysis_type_id" or "0.parameters.1"
        $parts = explode('.', $key);
        $index = (int) $parts[0];
        $field = $parts[1] ?? '';

        if ($field === 'sample_type_id') {
            // Load analysis types for this sample type
            $this->sampleEntries[$index]['analysis_type_id'] = '';
            $this->sampleEntries[$index]['parameters'] = [];
            $this->sampleEntries[$index]['availableParameters'] = [];

            if ($value) {
                $this->sampleEntries[$index]['analysisTypes'] = AnalysisType::where('sample_type_id', $value)
                    ->where('active', 1)
                    ->get(['id', 'name'])
                    ->toArray();
            } else {
                $this->sampleEntries[$index]['analysisTypes'] = [];
            }
        }

        if ($field === 'analysis_type_id') {
            $this->sampleEntries[$index]['parameters'] = [];

            if ($value) {
                $this->sampleEntries[$index]['availableParameters'] = $this->loadParametersForAnalysisType($value);
            } else {
                $this->sampleEntries[$index]['availableParameters'] = [];
            }
        }
    }

    protected function loadParametersForAnalysisType($analysisTypeId)
    {
        return AnalysisElements::where('analysis_type_id', $analysisTypeId)
            ->where('active', 1)
            ->with('analyte')
            ->get()
            ->filter(fn($el) => $el->analyte)
            ->map(fn($el) => [
                'id' => $el->analyte->id,
                'name' => $el->analyte->name,
            ])
            ->values()
            ->toArray();
    }

    // ── CRUD ──

    public function save()
    {
        $this->validate([
            'form.title' => 'required|string|max:255',
            'form.crm_customer_id' => 'required|string',
            'form.sampling_datetime' => 'required|date',
            'form.location' => 'required|string|max:255',
            'form.number_of_samples' => 'nullable|integer|min:1',
            'form.frequency' => 'required|string',
            'form.personnel_id' => 'required|string',
        ], [
            'form.title.required' => 'Schedule title is required.',
            'form.crm_customer_id.required' => 'Client / Customer is required.',
            'form.sampling_datetime.required' => 'Date & time of sampling is required.',
            'form.location.required' => 'Location is required.',
            'form.frequency.required' => 'Frequency is required.',
            'form.personnel_id.required' => 'Personnel is required.',
        ]);

        try {
            DB::beginTransaction();

            $companyId = getUserCompany();

            if ($this->editingSchedule) {
                $schedule = $this->editingSchedule;
            } else {
                $schedule = new SamplingSchedule();
                $schedule->company_id = $companyId;
            }

            $schedule->title = $this->form['title'];
            $schedule->crm_customer_id = $this->form['crm_customer_id'];
            $schedule->contact_id = $this->form['contact_id'] ?: null;
            $schedule->sampling_datetime = $this->form['sampling_datetime'];
            $schedule->location = $this->form['location'];
            $schedule->number_of_samples = $this->form['number_of_samples'] ?: 1;
            $schedule->frequency = $this->form['frequency'];
            $schedule->notify_client = $this->form['notify_client'] ? true : false;
            $schedule->personnel_id = $this->form['personnel_id'];
            $schedule->description = $this->form['description'];

            // Build sample_details from entries
            $sampleDetails = [];
            foreach ($this->sampleEntries as $entry) {
                if (!empty($entry['sample_type_id'])) {
                    $sampleDetails[] = [
                        'sample_type_id' => $entry['sample_type_id'],
                        'analysis_type_id' => $entry['analysis_type_id'] ?? '',
                        'parameters' => $entry['parameters'] ?? [],
                    ];
                }
            }
            $schedule->sample_details = $sampleDetails;

            // Also set legacy single FK for backward compat (first entry)
            if (!empty($sampleDetails)) {
                $schedule->sample_type_id = $sampleDetails[0]['sample_type_id'] ?: null;
                $schedule->analysis_type_id = $sampleDetails[0]['analysis_type_id'] ?: null;
                $schedule->parameters = $sampleDetails[0]['parameters'] ?? [];
            } else {
                $schedule->sample_type_id = null;
                $schedule->analysis_type_id = null;
                $schedule->parameters = [];
            }

            $schedule->save();

            // Send notification email if notify_client is checked
            if ($this->form['notify_client']) {
                $this->sendClientNotification($schedule);
            }

            DB::commit();

            $this->closeModal();
            $this->message = $this->editingSchedule
                ? 'Schedule updated successfully!'
                : 'Sampling scheduled successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $schedule = SamplingSchedule::findOrFail($id);
            $schedule->delete();

            DB::commit();

            $this->message = 'Schedule deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    /**
     * Resolve sample details to display names for the view/table.
     */
    public function resolveSampleDetails($schedule)
    {
        $details = $schedule->sample_details;
        if (empty($details) || !is_array($details)) {
            // Fallback to legacy single FK
            $info = [];
            if ($schedule->sample_type) {
                $info[] = ['type' => $schedule->sample_type->name, 'analysis' => $schedule->analysis_type->name ?? '', 'params_count' => count($schedule->parameters ?? [])];
            }
            return $info;
        }

        $result = [];
        foreach ($details as $entry) {
            $stName = '';
            $atName = '';
            $paramsCount = count($entry['parameters'] ?? []);

            if (!empty($entry['sample_type_id'])) {
                $st = SampleType::find($entry['sample_type_id']);
                $stName = $st ? $st->name : '';
            }
            if (!empty($entry['analysis_type_id'])) {
                $at = AnalysisType::find($entry['analysis_type_id']);
                $atName = $at ? $at->name : '';
            }

            $result[] = ['type' => $stName, 'analysis' => $atName, 'params_count' => $paramsCount];
        }

        return $result;
    }

    /**
     * Resolve detailed sample info including parameter names for the view modal.
     */
    public function resolveDetailedSampleDetails($schedule)
    {
        $details = $schedule->sample_details;
        if (empty($details) || !is_array($details)) {
            $info = [];
            if ($schedule->sample_type) {
                $parameterIds = $schedule->parameters ?? [];
                $paramNames = [];
                if (!empty($parameterIds)) {
                    $paramNames = \App\Analyte::whereIn('id', $parameterIds)
                        ->pluck('name')
                        ->toArray();
                }
                $info[] = [
                    'type' => $schedule->sample_type->name,
                    'analysis' => $schedule->analysis_type->name ?? '',
                    'params_count' => count($parameterIds),
                    'param_names' => $paramNames,
                ];
            }
            return $info;
        }

        $result = [];
        foreach ($details as $entry) {
            $stName = '';
            $atName = '';
            $paramNames = [];
            $parameterIds = $entry['parameters'] ?? [];

            if (!empty($entry['sample_type_id'])) {
                $st = SampleType::find($entry['sample_type_id']);
                $stName = $st ? $st->name : '';
            }
            if (!empty($entry['analysis_type_id'])) {
                $at = AnalysisType::find($entry['analysis_type_id']);
                $atName = $at ? $at->name : '';
            }
            if (!empty($parameterIds)) {
                $paramNames = \App\Analyte::whereIn('id', $parameterIds)
                    ->pluck('name')
                    ->toArray();
            }

            $result[] = [
                'type' => $stName,
                'analysis' => $atName,
                'params_count' => count($parameterIds),
                'param_names' => $paramNames,
            ];
        }

        return $result;
    }

    /**
     * Send notification email to client contact.
     */
    protected function sendClientNotification($schedule)
    {
        try {
            $contactEmail = null;
            
            // Get contact email from the schedule's contact relationship
            if ($schedule->contact && !empty($schedule->contact->email)) {
                $contactEmail = $schedule->contact->email;
            }
            
            // Fallback: try to get from selected contact in form
            if (empty($contactEmail) && !empty($this->selectedContactEmail) && $this->selectedContactEmail !== 'N/A') {
                $contactEmail = $this->selectedContactEmail;
            }
            
            // If no contact email, log warning and return
            if (empty($contactEmail)) {
                Log::warning('Cannot send sampling schedule notification: No contact email found for schedule ' . $schedule->id);
                return;
            }
            
            Mail::to($contactEmail)->send(new SamplingScheduleNotification($schedule));
            Log::info('Sampling schedule notification sent to: ' . $contactEmail);
            
        } catch (\Exception $e) {
            Log::error('Failed to send sampling schedule notification: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.planner.schedule-sampling-manager');
    }
}
