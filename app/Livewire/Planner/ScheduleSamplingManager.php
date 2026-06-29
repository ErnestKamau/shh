<?php

namespace App\Livewire\Planner;

use Livewire\Component;
use App\Models\SamplingSchedule;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\Models\SubmissionForm;
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
use App\Services\Commercial\CommercialEnquirySyncService;
use App\Services\SubmissionForm\PortalSubmissionFormAccess;
use App\Services\SubmissionForm\SubmissionFormSubmissionService;
use App\Services\SubmissionForm\SubmissionFormValueNormalizer;

class ScheduleSamplingManager extends Component
{
    // UI State
    public $showModal = false;
    public $showViewModal = false;
    public $editingSchedule = null;
    public $viewingSchedule = null;
    public bool $showFormModal = false;
    public ?string $selectedScheduleId = null;
    public ?string $selectedSampleTypeId = null;
    public array $formData = [];
    public array $scheduleSampleTypes = [];

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
        $this->viewingSchedule = SamplingSchedule::with([
            'client',
            'contact',
            'personnel',
            'submissionFormInstances.submissionForm.sampleTypes',
            'submissionFormInstances.submittedBy',
        ])->findOrFail($id);
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

    public function openScheduleFormModal($scheduleId)
    {
        $this->selectedScheduleId = $scheduleId;
        $this->selectedSampleTypeId = null;
        $this->formData = [];
        $this->scheduleSampleTypes = [];

        $schedule = SamplingSchedule::findOrFail($scheduleId);

        // Get all sample types, just like on Samples Receiving page
        $this->scheduleSampleTypes = SampleType::orderBy('name')->get()->toArray();

        // Resolve preselected sample type from the schedule
        $preselectedId = null;
        if ($schedule->sample_type_id) {
            $preselectedId = $schedule->sample_type_id;
        } elseif (!empty($schedule->sample_details) && is_array($schedule->sample_details)) {
            foreach ($schedule->sample_details as $entry) {
                if (!empty($entry['sample_type_id'])) {
                    $preselectedId = $entry['sample_type_id'];
                    break;
                }
            }
        }

        if ($preselectedId) {
            $this->selectedSampleTypeId = $preselectedId;
            $this->updatedSelectedSampleTypeId($preselectedId);
        }

        $this->showFormModal = true;
    }

    public function getSelectedSampleTypeProperty()
    {
        if (!$this->selectedSampleTypeId) {
            return null;
        }
        return \App\SampleType::find($this->selectedSampleTypeId);
    }

    public function getIsFoodProperty()
    {
        $st = $this->selectedSampleType;
        if (!$st) {
            return false;
        }
        return stripos($st->name, 'Food') !== false || stripos($st->code, 'FOOD') !== false;
    }

    public function getIsWaterProperty()
    {
        $st = $this->selectedSampleType;
        if (!$st) {
            return false;
        }
        $isWasteWater = stripos($st->name, 'Waste Water') !== false || stripos($st->code, 'WWTR') !== false;
        return !$isWasteWater && (stripos($st->name, 'Water') !== false || stripos($st->code, 'WTR') !== false);
    }

    public function getIsWasteWaterProperty()
    {
        $st = $this->selectedSampleType;
        if (!$st) {
            return false;
        }
        return stripos($st->name, 'Waste Water') !== false || stripos($st->code, 'WWTR') !== false;
    }

    public function getDefaultSampleRow()
    {
        $matchingEntry = null;
        $analysisTypeName = '';
        $parameterNames = [];
        if ($this->selectedScheduleId && $this->selectedSampleTypeId) {
            $schedule = \App\Models\SamplingSchedule::find($this->selectedScheduleId);
            if ($schedule) {
                if (!empty($schedule->sample_details) && is_array($schedule->sample_details)) {
                    foreach ($schedule->sample_details as $entry) {
                        if (($entry['sample_type_id'] ?? '') === $this->selectedSampleTypeId) {
                            $matchingEntry = $entry;
                            break;
                        }
                    }
                }
                if (!$matchingEntry && ($schedule->sample_type_id === $this->selectedSampleTypeId)) {
                    $matchingEntry = [
                        'sample_type_id' => $schedule->sample_type_id,
                        'analysis_type_id' => $schedule->analysis_type_id,
                        'parameters' => $schedule->parameters ?? [],
                    ];
                }
                if ($matchingEntry) {
                    if (!empty($matchingEntry['analysis_type_id'])) {
                        $at = \App\AnalysisType::find($matchingEntry['analysis_type_id']);
                        if ($at) {
                            $analysisTypeName = $at->name;
                        }
                    }
                    $parameterIds = $matchingEntry['parameters'] ?? [];
                    if (!empty($parameterIds) && is_array($parameterIds)) {
                        $parameterNames = \App\Analyte::whereIn('id', $parameterIds)->pluck('name')->toArray();
                    }
                }
            }
        }

        if ($this->isFood) {
            return [
                'sample_no' => '',
                'sample_description' => '',
                'sampling_point' => '',
                'qty' => '',
                'sample_type' => '', // Raw, Cooked, Ready To Eat
                'sample_condition' => '', // Acceptable, Chilled, Chilled/Ambient
                'sample_temp' => '',
                'production_date' => '',
                'expiration_date' => '',
                'batch_number' => '',
                'parameters' => implode(', ', $parameterNames),
                'state_of_sample' => '',
                'microbiology' => false,
                'chemistry' => false,
                'test_category' => '',
            ];
        }

        if ($this->isWater) {
            $hasMicro = false;
            $hasLegionella = false;
            $hasChem = false;

            if ($analysisTypeName) {
                if (stripos($analysisTypeName, 'micro') !== false) {
                    $hasMicro = true;
                }
                if (stripos($analysisTypeName, 'legionella') !== false) {
                    $hasLegionella = true;
                }
                if (stripos($analysisTypeName, 'chem') !== false) {
                    $hasChem = true;
                }
            }

            foreach ($parameterNames as $pName) {
                if (stripos($pName, 'micro') !== false || stripos($pName, 'coliform') !== false || stripos($pName, 'plate count') !== false || stripos($pName, 'e. coli') !== false || stripos($pName, 'bacteria') !== false) {
                    $hasMicro = true;
                }
                if (stripos($pName, 'legionella') !== false) {
                    $hasLegionella = true;
                }
                if (stripos($pName, 'chem') !== false || stripos($pName, 'ph') !== false || stripos($pName, 'chlorine') !== false || stripos($pName, 'hardness') !== false || stripos($pName, 'tds') !== false || stripos($pName, 'metal') !== false || stripos($pName, 'nitrate') !== false) {
                    $hasChem = true;
                }
            }

            $testCategory = '';
            if ($hasMicro) {
                $testCategory = 'microbiology';
            } elseif ($hasLegionella) {
                $testCategory = 'legionella';
            } elseif ($hasChem) {
                $testCategory = 'chemistry';
            }

            return [
                'sample_no' => '',
                'sample_description' => '',
                'location' => '',
                'qty' => '',
                'sampling_point' => '',
                'ph' => '',
                'appearance' => '',
                'residual_chlorine' => '',
                'odor' => '',
                'sample_temp' => '',
                'microbiology' => $hasMicro,
                'legionella' => $hasLegionella,
                'chemistry' => $hasChem,
                'test_category' => $testCategory,
            ];
        }

        return [];
    }

    public function addSampleRow(): void
    {
        if (!isset($this->formData['sample_rows'])) {
            $this->formData['sample_rows'] = [];
        }
        $this->formData['sample_rows'][] = $this->getDefaultSampleRow();
    }

    public function removeSampleRow(int $index): void
    {
        if (isset($this->formData['sample_rows'][$index])) {
            unset($this->formData['sample_rows'][$index]);
            $this->formData['sample_rows'] = array_values($this->formData['sample_rows']);
        }
    }

    public function updatedSelectedSampleTypeId($value)
    {
        $this->formData = [];
        if ($value) {
            $submissionForm = $this->submissionForm;
            if ($submissionForm) {
                foreach ($submissionForm->sections->sortBy('sort_order') as $section) {
                    $elements = $section->elementHolders->flatMap->elements->sortBy('sort_order');
                    if (($section->section_type ?? '') === 'rows_section') {
                        foreach ($elements as $element) {
                            $this->formData[$element->name] = [$element->element_type === 'checkbox' ? false : ''];
                        }
                        continue;
                    }
                    foreach ($elements as $element) {
                        $this->formData[$element->name] = $element->element_type === 'checkbox' ? false : '';
                    }
                }
            }

            // Auto-prefill client/customer details from schedule client if available
            if ($this->selectedScheduleId) {
                $schedule = \App\Models\SamplingSchedule::with(['client', 'contact'])->find($this->selectedScheduleId);
                if ($schedule && $schedule->client) {
                    $crmCustomer = $schedule->client;
                    $contact = $schedule->contact;
                    foreach ($this->formData as $key => $val) {
                        if (in_array($key, ['customer_name', 'client_name', 'customer', 'client'], true)) {
                            $this->formData[$key] = $crmCustomer->name;
                        }
                        if (in_array($key, ['phone', 'telephone', 'phone_number', 'mobile_number', 'telephone_number', 'tel_fax_no'], true)) {
                            if ($contact) {
                                $this->formData[$key] = $contact->mobile ?: $contact->telephone ?: $crmCustomer->telephone1 ?: $crmCustomer->telephone2 ?: '';
                            } else {
                                $this->formData[$key] = $crmCustomer->telephone1 ?? $crmCustomer->telephone2 ?? '';
                            }
                        }
                        if (in_array($key, ['email', 'email_address'], true)) {
                            if ($contact && $contact->email) {
                                $this->formData[$key] = $contact->email;
                            } else {
                                $this->formData[$key] = $crmCustomer->email ?? '';
                            }
                        }
                        if (in_array($key, ['address', 'physical_address', 'postal_address', 'customer_address'], true)) {
                            $this->formData[$key] = $crmCustomer->physical_address ?? $crmCustomer->postal_address ?? '';
                        }
                        if (in_array($key, ['contact_person', 'contact', 'contact_name'], true)) {
                            if ($contact) {
                                $this->formData[$key] = trim(($contact->first_name ?? '') . ' ' . ($contact->middle_name ?? '') . ' ' . ($contact->last_name ?? ''));
                            } else {
                                $firstContact = method_exists($crmCustomer, 'contacts') ? $crmCustomer->contacts()->first() : null;
                                if ($firstContact) {
                                    $this->formData[$key] = trim(($firstContact->first_name ?? '') . ' ' . ($firstContact->middle_name ?? '') . ' ' . ($firstContact->last_name ?? ''));
                                } else {
                                    $this->formData[$key] = '';
                                }
                            }
                        }
                    }
                }

                if ($schedule) {
                    $matchingEntry = null;
                    if (!empty($schedule->sample_details) && is_array($schedule->sample_details)) {
                        foreach ($schedule->sample_details as $entry) {
                            if (($entry['sample_type_id'] ?? '') === $value) {
                                $matchingEntry = $entry;
                                break;
                            }
                        }
                    }
                    if (!$matchingEntry && ($schedule->sample_type_id === $value)) {
                        $matchingEntry = [
                            'sample_type_id' => $schedule->sample_type_id,
                            'analysis_type_id' => $schedule->analysis_type_id,
                            'parameters' => $schedule->parameters ?? [],
                        ];
                    }

                    if ($matchingEntry) {
                        $analysisTypeName = '';
                        if (!empty($matchingEntry['analysis_type_id'])) {
                            $at = \App\AnalysisType::find($matchingEntry['analysis_type_id']);
                            if ($at) {
                                $analysisTypeName = $at->name;
                            }
                        }

                        if ($analysisTypeName) {
                            foreach (['analysis_type', 'analysis_types'] as $k) {
                                if (array_key_exists($k, $this->formData)) {
                                    $this->formData[$k] = $analysisTypeName;
                                }
                            }
                        }

                        $parameterNames = [];
                        $parameterIds = $matchingEntry['parameters'] ?? [];
                        if (!empty($parameterIds) && is_array($parameterIds)) {
                            $parameterNames = \App\Analyte::whereIn('id', $parameterIds)->pluck('name')->toArray();
                        }

                        if (!empty($parameterNames)) {
                            foreach (['parameter', 'parameters'] as $k) {
                                if (array_key_exists($k, $this->formData)) {
                                    $this->formData[$k] = $parameterNames[0] ?? '';
                                }
                            }
                        }

                        // Re-initialize default sample_rows with updated parameters/checkboxes
                        if ($this->isFood || $this->isWater) {
                            $this->formData['sample_rows'] = [$this->getDefaultSampleRow()];
                        }
                    }
                }
            }
        }
    }

    public function updated($propertyName, $value): void
    {
        $fieldKey = str_replace('formData.', '', $propertyName);

        // Prefill customer details when customer is selected
        if (in_array($fieldKey, ['customer_name', 'client_name', 'customer', 'client'], true) && $value) {
            $customer = CRMCustomer::where('name', $value)->first();
            if ($customer) {
                foreach ($this->formData as $key => $val) {
                    if (in_array($key, ['phone', 'telephone', 'phone_number', 'mobile_number', 'telephone_number', 'tel_fax_no'], true)) {
                        $this->formData[$key] = $customer->telephone1 ?? $customer->telephone2 ?? '';
                    }
                    if (in_array($key, ['email', 'email_address'], true)) {
                        $this->formData[$key] = $customer->email ?? '';
                    }
                    if (in_array($key, ['address', 'physical_address', 'postal_address', 'customer_address'], true)) {
                        $this->formData[$key] = $customer->physical_address ?? $customer->postal_address ?? '';
                    }
                    if (in_array($key, ['contact_person', 'contact', 'contact_name'], true)) {
                        $contact = method_exists($customer, 'contacts') ? $customer->contacts()->first() : null;
                        $this->formData[$key] = $contact ? $contact->name : '';
                    }
                }
            }
        }

        // Clear parameter selection when analysis type changes
        if (in_array($fieldKey, ['analysis_type', 'analysis_types'], true)) {
            foreach (['parameter', 'parameters'] as $paramKey) {
                if (array_key_exists($paramKey, $this->formData)) {
                    $this->formData[$paramKey] = '';
                }
            }
        }
    }

    public function getCustomersProperty()
    {
        return CRMCustomer::where('active', 1)->orderBy('name')->get();
    }

    public function getAnalysisTypesProperty()
    {
        if (!$this->selectedSampleTypeId) {
            return collect();
        }
        return \App\AnalysisType::where('sample_type_id', $this->selectedSampleTypeId)->orderBy('name')->get();
    }

    public function getParametersProperty()
    {
        if (!$this->selectedSampleTypeId) {
            return collect();
        }
        $atName = null;
        foreach (['analysis_type', 'analysis_types'] as $key) {
            if (!empty($this->formData[$key])) {
                $atName = $this->formData[$key];
                break;
            }
        }
        if (!$atName) {
            return collect();
        }
        $at = \App\AnalysisType::where('sample_type_id', $this->selectedSampleTypeId)
            ->where('name', $atName)
            ->first();
        if (!$at) {
            return collect();
        }
        return \App\Analyte::whereHas('analysis_elements', function ($q) use ($at) {
            $q->where('analysis_type_id', $at->id)->where('active', 1);
        })->orderBy('name')->get();
    }

    public function saveScheduleForm()
    {
        $this->validate([
            'selectedSampleTypeId' => 'required|exists:sample_types,id',
        ], [
            'selectedSampleTypeId.required' => 'Please select a Sample Type.',
        ]);

        $submissionForm = app(PortalSubmissionFormAccess::class)
            ->testRequestFormForSampleType((string) $this->selectedSampleTypeId);
        if ($submissionForm === null) {
            $this->addError('selectedSampleTypeId', 'No active Test Request Form template found for the selected sample type.');

            return;
        }

        $submissionForm->loadMissing(['sections.elementHolders.elements']);
        $rules = [];
        $messages = [];
        foreach ($submissionForm->sections->flatMap->elementHolders->flatMap->elements as $element) {
            if (! $element->is_required) {
                continue;
            }
            $key = 'formData.'.$element->name;
            $rules[$key] = 'required';
            $messages[$key.'.required'] = ($element->label ?? $element->name).' is required.';
        }

        if (!empty($rules)) {
            $this->validate($rules, $messages);
        }

        try {
            DB::beginTransaction();

            $schedule = SamplingSchedule::findOrFail($this->selectedScheduleId);

            $submissionForm = app(PortalSubmissionFormAccess::class)
                ->testRequestFormForSampleType((string) $this->selectedSampleTypeId);

            if (! $submissionForm) {
                throw new \Exception('No active Test Request Form template found for this sample type. Please seed TRF templates first.');
            }

            $payload = app(SubmissionFormValueNormalizer::class)->toRequestPayload($this->formData);

            app(SubmissionFormSubmissionService::class)->submitWalkInInstance(
                $submissionForm,
                $payload,
                $schedule->crm_customer_id !== null ? (string) $schedule->crm_customer_id : null,
                (string) $this->selectedSampleTypeId,
                CommercialEnquirySyncService::SOURCE_SCHEDULED,
                (string) $this->selectedScheduleId,
            );

            $schedule->is_collected = true;
            $schedule->save();

            DB::commit();

            $this->showFormModal = false;
            $this->message = 'Form responses saved, request created, and tied to this sampling schedule successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error saving form response: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function getSubmissionFormProperty(): ?SubmissionForm
    {
        if (! $this->selectedSampleTypeId) {
            return null;
        }

        $form = app(PortalSubmissionFormAccess::class)
            ->testRequestFormForSampleType((string) $this->selectedSampleTypeId);

        return $form?->loadMissing(['sections.elementHolders.elements']);
    }

    public function render()
    {
        return view('livewire.planner.schedule-sampling-manager', [
            'submissionForm' => $this->submissionForm,
        ]);
    }
}
