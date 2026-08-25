<?php

namespace App\Livewire\Planner;

use Livewire\Component;
use App\Models\SamplingSchedule;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\SamplePoint;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\SampleType;
use App\AnalysisType;
use App\AnalysisElements;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\SamplingScheduleNotification;
use App\Exports\SamplingSchedulesExport;
use App\Services\Commercial\CommercialEnquirySyncService;
use App\Services\Planner\SamplingScheduleTrfSync;
use App\Services\Planner\SamplingScheduleCollectionProgress;
use App\Services\Planner\SamplingScheduleSamplePlanHistoryRecorder;
use App\Services\SubmissionForm\PortalSubmissionFormAccess;
use App\Services\SubmissionForm\SubmissionFormSchemaHelper;
use App\Services\SubmissionForm\SubmissionFormSubmissionService;
use App\Services\Sampleworkflow\WalkInParameterCatalogService;
use App\Services\SubmissionForm\SubmissionFormValueNormalizer;

class ScheduleSamplingManager extends Component
{
    // UI State
    public $showModal = false;
    public $showViewModal = false;
    public $showTrfFormsModal = false;
    public $editingSchedule = null;
    public $viewingSchedule = null;
    public $viewingTrfSchedule = null;
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
        'contact_ids' => [],
        'sampling_datetime' => '',
        'location' => '',
        'sample_point_id' => '',
        'number_of_samples' => 1,
        'frequency' => 'One-time',
        'notify_client' => false,
        'personnel_ids' => [],
        'description' => '',
    ];

    // Multi-sample entries: [{sample_type_id, analysis_type_id, parameters: [], analysisTypes: [], availableParameters: []}]
    public $sampleEntries = [];

    // Customer prefill
    public $contractValidFrom = '';
    public $contractValidTo = '';
    public $customerContactOptions = [];
    public $customerSamplePointOptions = [];
    public $customerCompanyUnitOptions = [];
    public $selectedContactsSummary = [];

    // Inline add sample point (schedule form + TRF modal)
    public bool $showAddSamplePointModal = false;
    public string $newSamplePointName = '';
    public string $newSamplePointUnitId = '';
    public string $samplePointTargetField = 'sampling_location';
    public ?int $samplePointTargetRowIndex = null;
    public string $samplePointAssignTarget = 'schedule'; // schedule|trf

    // Supporting data
    public $clients = [];
    public $users = [];
    public $allSampleTypes = [];
    public $allAnalysisTypes = [];
    public $allParameters = [];
    public $frequencies = ['One-time', 'Daily', 'Weekly', 'Monthly', 'Quarterly', 'Annually'];

    public function mount()
    {
        $this->loadSupportingData();

        if (session('success')) {
            $this->message = (string) session('success');
            $this->messageType = 'success';
        }

        $editId = request()->query('edit');
        if (is_string($editId) && $editId !== '') {
            $exists = SamplingSchedule::query()->visibleTo()->whereKey($editId)->exists();
            if ($exists) {
                $this->showEditModal($editId);
            }
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

        $query = SamplingSchedule::with([
            'client',
            'contact',
            'samplePoint',
            'sample_type',
            'analysis_type',
            'personnel',
            'submissionFormInstances.values.element',
        ])
            ->where('company_id', $companyId)
            ->visibleTo()
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

        // Contact filter (legacy single + multi JSON)
        if ($this->filterContactId) {
            $query->where(function ($q) {
                $q->where('contact_id', $this->filterContactId)
                  ->orWhereJsonContains('contact_ids', $this->filterContactId);
            });
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

    /**
     * Table rows with recurring series collapsed into a single row.
     *
     * Occurrences sharing a recurrence_group_id are grouped; the row shows the
     * next upcoming occurrence (or the last one when all are in the past) as
     * representative, plus series metadata for the matched occurrences.
     *
     * @return Collection<int, array{schedule: SamplingSchedule, series: null|array{group_id: string, count: int, collected_count: int, forms_count: int, first_date: ?\Illuminate\Support\Carbon, last_date: ?\Illuminate\Support\Carbon, occurrences: Collection<int, SamplingSchedule>}}>
     */
    public function getScheduleRowsProperty(): Collection
    {
        $now = now();

        return $this->schedules
            ->groupBy(fn (SamplingSchedule $s) => $s->recurrence_group_id ?: 'single-'.$s->id)
            ->map(function (Collection $occurrences) use ($now) {
                $occurrences = $occurrences->sortBy('sampling_datetime')->values();

                if ($occurrences->count() === 1 && empty($occurrences->first()->recurrence_group_id)) {
                    return ['schedule' => $occurrences->first(), 'series' => null];
                }

                $representative = $occurrences->first(
                    fn (SamplingSchedule $s) => $s->sampling_datetime && $s->sampling_datetime->gte($now)
                ) ?? $occurrences->last();

                return [
                    'schedule' => $representative,
                    'series' => [
                        'group_id' => (string) $representative->recurrence_group_id,
                        'count' => $occurrences->count(),
                        'collected_count' => $occurrences->filter(fn (SamplingSchedule $s) => (bool) $s->is_collected)->count(),
                        'forms_count' => $occurrences->sum(fn (SamplingSchedule $s) => $s->submissionFormInstances->count()),
                        'first_date' => $occurrences->first()->sampling_datetime,
                        'last_date' => $occurrences->last()->sampling_datetime,
                        'occurrences' => $occurrences,
                    ],
                ];
            })
            ->sortByDesc(fn (array $row) => optional($row['schedule']->sampling_datetime)->getTimestamp() ?? 0)
            ->values();
    }

    /**
     * All occurrences of the series the viewed schedule belongs to.
     *
     * @return Collection<int, SamplingSchedule>
     */
    public function getViewingSeriesOccurrencesProperty(): Collection
    {
        if (! $this->viewingSchedule || empty($this->viewingSchedule->recurrence_group_id)) {
            return collect();
        }

        return SamplingSchedule::with(['submissionFormInstances'])
            ->visibleTo()
            ->where('recurrence_group_id', $this->viewingSchedule->recurrence_group_id)
            ->orderBy('sampling_datetime')
            ->get();
    }

    /**
     * All occurrences of the series the edited schedule belongs to (for the switcher).
     *
     * @return Collection<int, SamplingSchedule>
     */
    public function getEditingSeriesOccurrencesProperty(): Collection
    {
        if (! $this->editingSchedule || empty($this->editingSchedule->recurrence_group_id)) {
            return collect();
        }

        return SamplingSchedule::query()
            ->visibleTo()
            ->where('recurrence_group_id', $this->editingSchedule->recurrence_group_id)
            ->orderBy('sampling_datetime')
            ->get(['id', 'sampling_datetime', 'is_collected', 'recurrence_group_id']);
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
        // Close other overlays first — view modal is rendered after edit in the DOM
        // and shares the same z-index, so leaving it open hides the edit form.
        $this->showViewModal = false;
        $this->viewingSchedule = null;
        $this->showTrfFormsModal = false;
        $this->viewingTrfSchedule = null;

        $schedule = SamplingSchedule::query()->visibleTo()->findOrFail($id);
        $this->editingSchedule = $schedule;

        $this->form = [
            'title' => $schedule->title,
            'crm_customer_id' => $schedule->crm_customer_id,
            'contact_ids' => $schedule->resolvedContactIds(),
            'sampling_datetime' => $schedule->sampling_datetime ? $schedule->sampling_datetime->format('Y-m-d\TH:i') : '',
            'location' => $schedule->location ?? '',
            'sample_point_id' => $this->resolveSamplePointIdForSchedule($schedule),
            'number_of_samples' => $schedule->number_of_samples ?? 1,
            'frequency' => $schedule->frequency ?? 'One-time',
            'notify_client' => $schedule->notify_client ?? false,
            'personnel_ids' => $schedule->resolvedPersonnelIds(),
            'description' => $schedule->description ?? '',
        ];

        // Load sample entries from sample_details or legacy columns
        $this->sampleEntries = [];
        if (!empty($schedule->sample_details) && is_array($schedule->sample_details)) {
            foreach ($schedule->sample_details as $entry) {
                $stId = $entry['sample_type_id'] ?? '';
                $atId = $entry['analysis_type_id'] ?? '';
                $params = array_values(array_filter(array_map('strval', $entry['parameters'] ?? [])));

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
                'parameters' => array_values(array_filter(array_map('strval', $schedule->parameters ?? []))),
                'analysisTypes' => $analysisTypes,
                'availableParameters' => $availableParameters,
            ];
        }

        if ($this->sampleEntries === []) {
            $this->sampleEntries[] = [
                'sample_type_id' => '',
                'analysis_type_id' => '',
                'parameters' => [],
                'analysisTypes' => [],
                'availableParameters' => [],
            ];
        }

        $this->syncNumberOfSamplesFromEntries();

        // Load customer data for prefill
        if ($schedule->crm_customer_id) {
            $this->loadCustomerDetails($schedule->crm_customer_id);
            $this->refreshSelectedContactsSummary();
        }

        $this->showModal = true;
    }

    public function viewSchedule($id)
    {
        return redirect()->route('system-planner.schedule-sampling.show', ['schedule' => $id]);
    }

    public function viewTrfForms($id)
    {
        $this->showViewModal = false;
        $this->viewingSchedule = null;
        $this->viewingTrfSchedule = SamplingSchedule::with([
            'client',
            'submissionFormInstances' => function ($query) {
                $query->orderByDesc('submitted_at')->orderByDesc('created_at');
            },
            'submissionFormInstances.submissionForm.sampleTypeCategories',
            'submissionFormInstances.submittedBy',
        ])->visibleTo()->findOrFail($id);
        $this->showTrfFormsModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showViewModal = false;
        $this->showTrfFormsModal = false;
        $this->viewingTrfSchedule = null;
        $this->resetForm();
    }

    // ── Form management ──

    public function resetForm()
    {
        $this->form = [
            'title' => '',
            'crm_customer_id' => '',
            'contact_ids' => [],
            'sampling_datetime' => '',
            'location' => '',
            'sample_point_id' => '',
            'number_of_samples' => 1,
            'frequency' => 'One-time',
            'notify_client' => false,
            'personnel_ids' => [],
            'description' => '',
        ];
        $this->sampleEntries = [];
        $this->contractValidFrom = '';
        $this->contractValidTo = '';
        $this->customerContactOptions = [];
        $this->customerSamplePointOptions = [];
        $this->customerCompanyUnitOptions = [];
        $this->selectedContactsSummary = [];
        $this->editingSchedule = null;
        $this->viewingSchedule = null;
        $this->viewingTrfSchedule = null;
        $this->closeAddSamplePointModal();
    }

    // ── Customer reactivity ──

    public function updatedFormCrmCustomerId($value)
    {
        $this->form['contact_ids'] = [];
        $this->form['sample_point_id'] = '';
        $this->form['location'] = '';
        $this->selectedContactsSummary = [];

        if ($value) {
            $this->loadCustomerDetails($value);
        } else {
            $this->contractValidFrom = '';
            $this->contractValidTo = '';
            $this->customerContactOptions = [];
            $this->customerSamplePointOptions = [];
            $this->customerCompanyUnitOptions = [];
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

        $this->customerContactOptions = CustomerContact::where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => (string) $c->id,
                    'name' => trim(($c->first_name ?? '') . ' ' . ($c->middle_name ?? '') . ' ' . ($c->last_name ?? '')),
                    'email' => $c->email,
                    'telephone' => $c->telephone,
                    'mobile' => $c->mobile,
                ];
            })
            ->values()
            ->toArray();

        $this->refreshCustomerSamplePointOptions((string) $customerId);
        $this->refreshCustomerCompanyUnitOptions((string) $customerId);
    }

    protected function refreshCustomerSamplePointOptions(string $customerId): void
    {
        $this->customerSamplePointOptions = SamplePoint::query()
            ->where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->orderBy('name')
            ->get()
            ->map(fn (SamplePoint $point) => [
                'id' => (string) $point->id,
                'name' => (string) $point->display_name,
            ])
            ->values()
            ->toArray();
    }

    protected function refreshCustomerCompanyUnitOptions(string $customerId): void
    {
        $this->customerCompanyUnitOptions = CRMCompanyUnit::query()
            ->where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($unit) => [
                'id' => (string) $unit->id,
                'name' => (string) $unit->name,
            ])
            ->values()
            ->toArray();
    }

    public function updatedFormContactIds(): void
    {
        $this->form['contact_ids'] = array_values(array_filter(array_map('strval', $this->form['contact_ids'] ?? [])));
        $this->refreshSelectedContactsSummary();
    }

    public function updatedFormSamplePointId($value): void
    {
        $pointId = $value ? (string) $value : '';
        $this->form['sample_point_id'] = $pointId;

        if ($pointId === '') {
            $this->form['location'] = '';

            return;
        }

        $point = collect($this->customerSamplePointOptions)->firstWhere('id', $pointId);
        if ($point) {
            $this->form['location'] = $point['name'] ?? '';

            return;
        }

        $model = SamplePoint::query()->find($pointId);
        $this->form['location'] = $model ? (string) $model->display_name : '';
    }

    protected function refreshSelectedContactsSummary(): void
    {
        $selectedIds = array_values(array_filter(array_map('strval', $this->form['contact_ids'] ?? [])));
        $this->selectedContactsSummary = collect($this->customerContactOptions)
            ->filter(fn ($contact) => in_array((string) ($contact['id'] ?? ''), $selectedIds, true))
            ->map(fn ($contact) => [
                'name' => $contact['name'] ?? '',
                'email' => $contact['email'] ?? '',
                'phone' => $contact['telephone'] ?? $contact['mobile'] ?? '',
            ])
            ->values()
            ->toArray();
    }

    protected function resolveSamplePointIdForSchedule(SamplingSchedule $schedule): string
    {
        if (! empty($schedule->sample_point_id)) {
            return (string) $schedule->sample_point_id;
        }

        $location = trim((string) ($schedule->location ?? ''));
        if ($location === '') {
            return '';
        }

        if (Str::isUuid($location)) {
            $byId = SamplePoint::query()->find($location);
            if ($byId) {
                return (string) $byId->id;
            }
        }

        if (! empty($schedule->crm_customer_id)) {
            $byName = SamplePoint::query()
                ->where('crm_customer_id', $schedule->crm_customer_id)
                ->where('active', 1)
                ->where('name', $location)
                ->first();

            if ($byName) {
                return (string) $byName->id;
            }
        }

        return '';
    }

    protected function resolveCustomerIdForLookups(): ?string
    {
        if (! empty($this->form['crm_customer_id'])) {
            return (string) $this->form['crm_customer_id'];
        }

        if ($this->selectedScheduleId) {
            $scheduleCustomerId = SamplingSchedule::query()
                ->whereKey($this->selectedScheduleId)
                ->value('crm_customer_id');

            return $scheduleCustomerId ? (string) $scheduleCustomerId : null;
        }

        return null;
    }

    /**
     * @return Collection<int, CustomerContact>
     */
    public function getCustomerContactsProperty(): Collection
    {
        $customerId = $this->resolveCustomerIdForLookups();
        if ($customerId === null) {
            return collect();
        }

        return CustomerContact::query()
            ->where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    /**
     * @return Collection<int, SamplePoint>
     */
    public function getCustomerSamplePointsProperty(): Collection
    {
        $customerId = $this->resolveCustomerIdForLookups();
        if ($customerId === null) {
            return collect();
        }

        return SamplePoint::query()
            ->where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, CRMCompanyUnit>
     */
    public function getCustomerCompanyUnitsProperty(): Collection
    {
        $customerId = $this->resolveCustomerIdForLookups();
        if ($customerId === null) {
            return collect();
        }

        return CRMCompanyUnit::query()
            ->where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->orderBy('name')
            ->get();
    }

    public function openAddSamplePointModal(string $assignTarget = 'schedule', string $fieldName = 'sampling_location', ?int $rowIndex = null): void
    {
        $customerId = $this->resolveCustomerIdForLookups();
        if ($customerId === null) {
            $this->addError('form.sample_point_id', 'Select a customer before adding a sample point.');

            return;
        }

        $this->resetAddSamplePointModal();
        $this->samplePointAssignTarget = in_array($assignTarget, ['schedule', 'trf'], true) ? $assignTarget : 'schedule';
        $this->samplePointTargetField = $fieldName !== '' ? $fieldName : 'sampling_location';
        $this->samplePointTargetRowIndex = $rowIndex;
        $this->refreshCustomerCompanyUnitOptions($customerId);
        $this->newSamplePointUnitId = (string) (($this->customerCompanyUnitOptions[0]['id'] ?? '') ?: '');
        $this->showAddSamplePointModal = true;
    }

    public function openWalkInAddPointModal(string $fieldName = 'sampling_location', ?int $rowIndex = null): void
    {
        $this->openAddSamplePointModal('trf', $fieldName, $rowIndex);
    }

    public function closeAddSamplePointModal(): void
    {
        $this->showAddSamplePointModal = false;
        $this->resetAddSamplePointModal();
    }

    public function closeWalkInAddPointModal(): void
    {
        $this->closeAddSamplePointModal();
    }

    protected function resetAddSamplePointModal(): void
    {
        $this->newSamplePointName = '';
        $this->newSamplePointUnitId = '';
        $this->samplePointTargetField = 'sampling_location';
        $this->samplePointTargetRowIndex = null;
        $this->samplePointAssignTarget = 'schedule';
        $this->resetValidation([
            'newSamplePointName',
            'newSamplePointUnitId',
        ]);
    }

    public function saveNewSamplePoint(): void
    {
        $customerId = $this->resolveCustomerIdForLookups();
        if ($customerId === null) {
            $this->addError('newSamplePointName', 'Select a customer first.');

            return;
        }

        $this->validate([
            'newSamplePointName' => 'required|string|max:255',
            'newSamplePointUnitId' => 'required|exists:crm_company_units,id',
        ], [
            'newSamplePointName.required' => 'Sample point name is required.',
            'newSamplePointUnitId.required' => 'Client unit is required.',
        ]);

        $unitBelongsToCustomer = CRMCompanyUnit::query()
            ->where('id', $this->newSamplePointUnitId)
            ->where('crm_customer_id', $customerId)
            ->exists();

        if (! $unitBelongsToCustomer) {
            $this->addError('newSamplePointUnitId', 'Selected unit does not belong to this customer.');

            return;
        }

        $point = SamplePoint::query()->create([
            'crm_customer_id' => $customerId,
            'crm_company_unit_id' => $this->newSamplePointUnitId,
            'name' => trim($this->newSamplePointName),
            'active' => 1,
        ]);

        $this->refreshCustomerSamplePointOptions($customerId);

        if ($this->samplePointAssignTarget === 'trf') {
            $this->assignTrfSamplePointSelection((string) $point->id);
        } else {
            $this->form['sample_point_id'] = (string) $point->id;
            $this->form['location'] = (string) $point->display_name;
        }

        $this->closeAddSamplePointModal();
    }

    public function saveWalkInSamplePoint(): void
    {
        $this->saveNewSamplePoint();
    }

    protected function assignTrfSamplePointSelection(string $pointId): void
    {
        $field = $this->samplePointTargetField !== ''
            ? $this->samplePointTargetField
            : 'sampling_location';
        $rowIndex = $this->samplePointTargetRowIndex;

        if ($rowIndex !== null) {
            if (! isset($this->formData[$field]) || ! is_array($this->formData[$field])) {
                $this->formData[$field] = [];
            }
            $this->formData[$field][$rowIndex] = $pointId;

            return;
        }

        if (array_key_exists($field, $this->formData)) {
            $this->formData[$field] = $pointId;

            return;
        }

        $this->formData[$field] = $pointId;
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
        $this->syncNumberOfSamplesFromEntries();
    }

    public function removeSampleEntry($index)
    {
        unset($this->sampleEntries[$index]);
        $this->sampleEntries = array_values($this->sampleEntries);
        $this->syncNumberOfSamplesFromEntries();
    }

    protected function syncNumberOfSamplesFromEntries(): void
    {
        $this->form['number_of_samples'] = max(1, count($this->sampleEntries));
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

            $this->syncNumberOfSamplesFromEntries();
        }

        if ($field === 'analysis_type_id') {
            $this->sampleEntries[$index]['parameters'] = [];

            if ($value) {
                $this->sampleEntries[$index]['availableParameters'] = $this->loadParametersForAnalysisType($value);
            } else {
                $this->sampleEntries[$index]['availableParameters'] = [];
            }
        }

        if ($field === 'parameters') {
            $params = $this->sampleEntries[$index]['parameters'] ?? [];
            if (! is_array($params)) {
                $params = $params !== null && $params !== '' ? [(string) $params] : [];
            }
            $this->sampleEntries[$index]['parameters'] = array_values(array_unique(array_filter(array_map('strval', $params))));
        }
    }

    /**
     * @param  list<string|int>  $parameterIds
     */
    public function setSampleEntryParameters(int $index, array $parameterIds): void
    {
        if (! isset($this->sampleEntries[$index])) {
            return;
        }

        $this->sampleEntries[$index]['parameters'] = array_values(array_unique(array_filter(array_map('strval', $parameterIds))));
    }

    protected function loadParametersForAnalysisType($analysisTypeId)
    {
        return AnalysisElements::where('analysis_type_id', $analysisTypeId)
            ->where('active', 1)
            ->with('analyte')
            ->get()
            ->filter(fn($el) => $el->analyte)
            ->map(fn($el) => [
                'id' => (string) $el->analyte->id,
                'name' => $el->analyte->name,
            ])
            ->unique('id')
            ->values()
            ->toArray();
    }

    // ── CRUD ──

    public function save()
    {
        $this->syncNumberOfSamplesFromEntries();

        $this->form['contact_ids'] = array_values(array_filter(array_map('strval', $this->form['contact_ids'] ?? [])));
        $this->form['personnel_ids'] = array_values(array_filter(array_map('strval', $this->form['personnel_ids'] ?? [])));

        $this->validate([
            'form.title' => 'required|string|max:255',
            'form.crm_customer_id' => 'required|string',
            'form.sampling_datetime' => 'required|date',
            'form.sample_point_id' => 'required|string',
            'form.number_of_samples' => 'required|integer|min:1',
            'form.frequency' => 'required|string',
            'form.personnel_ids' => 'required|array|min:1',
            'form.personnel_ids.*' => 'required|string',
            'form.contact_ids' => 'nullable|array',
            'form.contact_ids.*' => 'string',
        ], [
            'form.title.required' => 'Schedule title is required.',
            'form.crm_customer_id.required' => 'Client / Customer is required.',
            'form.sampling_datetime.required' => 'Date & time of sampling is required.',
            'form.sample_point_id.required' => 'Location (sample point) is required.',
            'form.frequency.required' => 'Frequency is required.',
            'form.personnel_ids.required' => 'At least one personnel is required.',
            'form.personnel_ids.min' => 'At least one personnel is required.',
        ]);

        if (! empty($this->form['notify_client']) && empty($this->form['contact_ids'])) {
            $this->addError('form.contact_ids', 'Select at least one customer contact to notify.');

            return;
        }

        $samplePoint = SamplePoint::query()
            ->where('id', $this->form['sample_point_id'])
            ->where('crm_customer_id', $this->form['crm_customer_id'])
            ->where('active', 1)
            ->first();

        if (! $samplePoint) {
            $this->addError('form.sample_point_id', 'Select a valid sample point for this customer.');

            return;
        }

        try {
            DB::beginTransaction();

            $companyId = getUserCompany();
            $wasEditing = $this->editingSchedule !== null;
            $historyRecorder = app(SamplingScheduleSamplePlanHistoryRecorder::class);
            $previousPlan = null;

            $previousFrequency = null;
            if ($this->editingSchedule) {
                $schedule = $this->editingSchedule;
                $previousFrequency = trim((string) ($schedule->frequency ?? 'One-time'));
                $previousPlan = $historyRecorder->currentPlanFromSchedule($schedule);
            } else {
                $schedule = new SamplingSchedule();
                $schedule->company_id = $companyId;
            }

            $contactIds = $this->form['contact_ids'];
            $personnelIds = $this->form['personnel_ids'];

            $schedule->title = $this->form['title'];
            $schedule->crm_customer_id = $this->form['crm_customer_id'];
            $schedule->contact_ids = $contactIds !== [] ? $contactIds : null;
            $schedule->contact_id = $contactIds[0] ?? null;
            $schedule->sampling_datetime = $this->form['sampling_datetime'];
            $schedule->sample_point_id = (string) $samplePoint->id;
            $schedule->location = (string) $samplePoint->display_name;
            $schedule->number_of_samples = (int) ($this->form['number_of_samples'] ?: max(1, count($this->sampleEntries)));
            $schedule->frequency = $this->form['frequency'];
            $schedule->notify_client = $this->form['notify_client'] ? true : false;
            $schedule->personnel_ids = $personnelIds;
            $schedule->personnel_id = $personnelIds[0] ?? null;
            $schedule->description = $this->form['description'];

            // Build sample_details from entries
            $sampleDetails = [];
            foreach ($this->sampleEntries as $entry) {
                if (!empty($entry['sample_type_id'])) {
                    $params = array_values(array_unique(array_filter(array_map(
                        'strval',
                        is_array($entry['parameters'] ?? null) ? $entry['parameters'] : []
                    ))));

                    $sampleDetails[] = [
                        'sample_type_id' => $entry['sample_type_id'],
                        'analysis_type_id' => $entry['analysis_type_id'] ?? '',
                        'parameters' => $params,
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

            $becameRecurring = $wasEditing
                && empty($schedule->recurrence_group_id)
                && in_array($previousFrequency, ['', 'One-time'], true)
                && trim((string) $schedule->frequency) !== 'One-time';

            $createdOccurrences = [];
            if (! $wasEditing || $becameRecurring) {
                $createdOccurrences = app(\App\Services\Planner\SamplingScheduleRecurrenceGenerator::class)
                    ->generateFollowingOccurrences($schedule);
            }

            if ($wasEditing && $previousPlan !== null) {
                $historyRecorder->recordIfChanged(
                    $schedule,
                    $previousPlan['sample_details'],
                    $previousPlan['number_of_samples'],
                    $sampleDetails,
                    (int) $schedule->number_of_samples,
                );
            }

            // Send notification email if notify_client is checked
            if ($this->form['notify_client']) {
                $this->sendClientNotification($schedule);
            }

            DB::commit();

            $this->closeModal();
            if ($wasEditing && $becameRecurring && $createdOccurrences !== []) {
                $this->message = 'Schedule updated and converted to a recurring series with '.(count($createdOccurrences) + 1).' occurrences.';
            } elseif ($wasEditing) {
                $this->message = 'Schedule updated successfully!';
            } elseif ($createdOccurrences !== []) {
                $this->message = 'Recurring sampling scheduled as one series with '.(count($createdOccurrences) + 1).' occurrences.';
            } else {
                $this->message = 'Sampling scheduled successfully!';
            }
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

            $schedule = SamplingSchedule::query()->visibleTo()->findOrFail($id);
            $schedule->delete();

            DB::commit();

            if ($this->viewingSchedule && (string) $this->viewingSchedule->id === (string) $id) {
                $this->showViewModal = false;
                $this->viewingSchedule = null;
            }

            $this->message = 'Schedule deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    /**
     * Delete every occurrence of a recurring schedule series.
     */
    public function deleteSeries(string $groupId)
    {
        try {
            DB::beginTransaction();

            $occurrences = SamplingSchedule::query()
                ->visibleTo()
                ->where('company_id', getUserCompany())
                ->where('recurrence_group_id', $groupId)
                ->get();

            foreach ($occurrences as $occurrence) {
                $occurrence->delete();
            }

            DB::commit();

            if ($this->viewingSchedule && (string) $this->viewingSchedule->recurrence_group_id === $groupId) {
                $this->showViewModal = false;
                $this->viewingSchedule = null;
            }

            $this->message = 'Recurring schedule deleted ('.$occurrences->count().' occurrence'.($occurrences->count() === 1 ? '' : 's').').';
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
     *
     * @return list<array{type: string, analysis: string, params_count: int, param_names?: list<string>}>
     */
    public function resolveSampleDetails($schedule)
    {
        $details = $schedule->sample_details;
        if (empty($details) || ! is_array($details)) {
            $info = [];
            if ($schedule->sample_type) {
                $info[] = [
                    'type' => (string) $schedule->sample_type->name,
                    'analysis' => (string) ($schedule->analysis_type->name ?? ''),
                    'params_count' => count($schedule->parameters ?? []),
                ];
            }

            return $info;
        }

        $sampleTypeIds = [];
        $analysisTypeIds = [];
        foreach ($details as $entry) {
            if (! empty($entry['sample_type_id'])) {
                $sampleTypeIds[] = (string) $entry['sample_type_id'];
            }
            if (! empty($entry['analysis_type_id'])) {
                $analysisTypeIds[] = (string) $entry['analysis_type_id'];
            }
        }

        $sampleTypeNames = SampleType::query()
            ->whereIn('id', array_values(array_unique($sampleTypeIds)))
            ->pluck('name', 'id');
        $analysisTypeNames = AnalysisType::query()
            ->whereIn('id', array_values(array_unique($analysisTypeIds)))
            ->pluck('name', 'id');

        $result = [];
        foreach ($details as $entry) {
            $stId = (string) ($entry['sample_type_id'] ?? '');
            $atId = (string) ($entry['analysis_type_id'] ?? '');
            $paramsCount = count(array_values(array_unique(array_filter(array_map(
                'strval',
                $entry['parameters'] ?? []
            )))));

            $stName = trim((string) ($sampleTypeNames[$stId] ?? ''));
            $atName = trim((string) ($analysisTypeNames[$atId] ?? ''));

            if ($stName === '' && $stId !== '') {
                $stName = 'Unknown sample type';
            }
            if ($atName === '' && $atId !== '') {
                $atName = 'Unknown analysis type';
            }

            if ($stName === '' && $atName === '' && $paramsCount === 0) {
                continue;
            }

            $result[] = [
                'type' => $stName !== '' ? $stName : '—',
                'analysis' => $atName,
                'params_count' => $paramsCount,
            ];
        }

        return $result;
    }

    /**
     * Resolve detailed sample info including parameter names for the view modal.
     *
     * @return list<array{type: string, analysis: string, params_count: int, param_names: list<string>}>
     */
    public function resolveDetailedSampleDetails($schedule)
    {
        $details = $schedule->sample_details;
        if (empty($details) || ! is_array($details)) {
            $info = [];
            if ($schedule->sample_type) {
                $parameterIds = array_values(array_unique(array_filter(array_map('strval', $schedule->parameters ?? []))));
                $paramNames = $parameterIds !== []
                    ? \App\Analyte::whereIn('id', $parameterIds)->pluck('name')->toArray()
                    : [];
                $info[] = [
                    'type' => (string) $schedule->sample_type->name,
                    'analysis' => (string) ($schedule->analysis_type->name ?? ''),
                    'params_count' => count($parameterIds),
                    'param_names' => $paramNames,
                ];
            }

            return $info;
        }

        $sampleTypeIds = [];
        $analysisTypeIds = [];
        $allParamIds = [];
        foreach ($details as $entry) {
            if (! empty($entry['sample_type_id'])) {
                $sampleTypeIds[] = (string) $entry['sample_type_id'];
            }
            if (! empty($entry['analysis_type_id'])) {
                $analysisTypeIds[] = (string) $entry['analysis_type_id'];
            }
            foreach (array_values(array_filter(array_map('strval', $entry['parameters'] ?? []))) as $paramId) {
                $allParamIds[] = $paramId;
            }
        }

        $sampleTypeNames = SampleType::query()
            ->whereIn('id', array_values(array_unique($sampleTypeIds)))
            ->pluck('name', 'id');
        $analysisTypeNames = AnalysisType::query()
            ->whereIn('id', array_values(array_unique($analysisTypeIds)))
            ->pluck('name', 'id');
        $paramNameMap = $allParamIds !== []
            ? \App\Analyte::query()->whereIn('id', array_values(array_unique($allParamIds)))->pluck('name', 'id')
            : collect();

        $result = [];
        foreach ($details as $entry) {
            $stId = (string) ($entry['sample_type_id'] ?? '');
            $atId = (string) ($entry['analysis_type_id'] ?? '');
            $parameterIds = array_values(array_unique(array_filter(array_map('strval', $entry['parameters'] ?? []))));
            $paramNames = [];
            foreach ($parameterIds as $paramId) {
                $name = trim((string) ($paramNameMap[$paramId] ?? ''));
                if ($name !== '') {
                    $paramNames[] = $name;
                }
            }

            $stName = trim((string) ($sampleTypeNames[$stId] ?? ''));
            $atName = trim((string) ($analysisTypeNames[$atId] ?? ''));
            if ($stName === '' && $stId !== '') {
                $stName = 'Unknown sample type';
            }
            if ($atName === '' && $atId !== '') {
                $atName = 'Unknown analysis type';
            }

            $result[] = [
                'type' => $stName !== '' ? $stName : '—',
                'analysis' => $atName,
                'params_count' => count($parameterIds),
                'param_names' => $paramNames,
            ];
        }

        return $result;
    }

    /**
     * Send notification emails to all selected customer contacts.
     */
    protected function sendClientNotification($schedule)
    {
        try {
            $contactIds = $schedule->resolvedContactIds();
            if ($contactIds === [] && ! empty($this->form['contact_ids'])) {
                $contactIds = array_values(array_filter(array_map('strval', $this->form['contact_ids'])));
            }

            $emails = CustomerContact::query()
                ->whereIn('id', $contactIds)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->pluck('email')
                ->map(fn ($email) => trim((string) $email))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($emails === []) {
                Log::warning('Cannot send sampling schedule notification: No contact emails found for schedule ' . $schedule->id);
                return;
            }

            Mail::to($emails)->send(new SamplingScheduleNotification($schedule));
            Log::info('Sampling schedule notification sent to: ' . implode(', ', $emails));

        } catch (\Exception $e) {
            Log::error('Failed to send sampling schedule notification: ' . $e->getMessage());
        }
    }

    public function openScheduleFormModal($scheduleId)
    {
        $schedule = SamplingSchedule::query()->visibleTo()->findOrFail($scheduleId);

        $sampleTypeId = null;
        if ($schedule->sample_type_id) {
            $sampleTypeId = (string) $schedule->sample_type_id;
        } elseif (! empty($schedule->sample_details) && is_array($schedule->sample_details)) {
            foreach ($schedule->sample_details as $entry) {
                if (! empty($entry['sample_type_id'])) {
                    $sampleTypeId = (string) $entry['sample_type_id'];
                    break;
                }
            }
        }

        if ($sampleTypeId !== null && $sampleTypeId !== '') {
            $this->redirect(
                route('system-planner.fill-sampling-forms.fill', [
                    'sampleType' => $sampleTypeId,
                    'schedule' => $schedule->id,
                ]),
                navigate: false,
            );

            return;
        }

        $this->redirect(
            route('system-planner.fill-sampling-forms', ['schedule' => $schedule->id]),
            navigate: false,
        );
    }

    public function updatedShowFormModal(bool $value): void
    {
        if ($value) {
            $this->dispatch('schedule-trf-reinit-widgets');
        }
    }

    public function getSelectedSampleTypeProperty()
    {
        if (!$this->selectedSampleTypeId) {
            return null;
        }
        return \App\SampleType::query()
            ->with('sampleTypeCategory')
            ->find($this->selectedSampleTypeId);
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
        if ($this->isWasteWater) {
            return false;
        }

        $st = $this->selectedSampleType;
        if (! $st) {
            return false;
        }

        return app(\App\Services\SubmissionForm\TrfDocumentCodeForSampleType::class)->isWater($st);
    }

    public function getIsWasteWaterProperty()
    {
        $st = $this->selectedSampleType;
        if (! $st) {
            return false;
        }

        return app(\App\Services\SubmissionForm\TrfDocumentCodeForSampleType::class)->isWasteWater($st);
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
                    if (SubmissionFormSchemaHelper::isBuilderHiddenSection($section)) {
                        continue;
                    }

                    $elements = $section->elementHolders->flatMap->elements->sortBy('sort_order')
                        ->reject(fn (SubmissionFormElement $element): bool => SubmissionFormSchemaHelper::shouldOmitFromFillForm($element, $section));

                    if (($section->section_type ?? '') === 'rows_section') {
                        foreach ($elements as $element) {
                            $this->formData[$element->name] = [$this->defaultValueForElement($element)];
                        }
                        continue;
                    }
                    foreach ($elements as $element) {
                        $this->formData[$element->name] = $this->defaultValueForElement($element);
                    }
                }
            }

            if ($this->selectedScheduleId && $submissionForm) {
                $this->applyScheduleCustomerAndCollectionPrefill($submissionForm);
            }
        }

        if ($value) {
            $this->dispatch('schedule-trf-reinit-widgets');
        }
    }

    /**
     * Fill TRF customer/contact/collection fields from the selected sampling schedule.
     */
    private function applyScheduleCustomerAndCollectionPrefill(SubmissionForm $submissionForm): void
    {
        $schedule = SamplingSchedule::query()
            ->with(['client.contacts', 'contact', 'samplePoint'])
            ->find($this->selectedScheduleId);

        if ($schedule === null) {
            return;
        }

        $sync = app(SamplingScheduleTrfSync::class);
        $customerValues = $sync->customerFieldValuesFromSchedule($schedule);
        foreach ($customerValues as $key => $value) {
            if ($value === '' || $value === null) {
                continue;
            }
            if (array_key_exists($key, $this->formData)) {
                $this->formData[$key] = $value;
            }
        }

        foreach (['customer_representative_name', 'customer_rep_name', 'customer_representative_contact', 'customer_rep_contact'] as $repKey) {
            if (! empty($customerValues[$repKey])) {
                $this->formData[$repKey] = $customerValues[$repKey];
            }
        }

        $this->hydrateFormDataFromSchedule($submissionForm);

        // Keep food/water sample rows aligned after schedule hydration.
        if ($this->isFood || $this->isWater || $this->isWasteWater) {
            if (empty($this->formData['sample_rows']) || ! is_array($this->formData['sample_rows'])) {
                $this->formData['sample_rows'] = [$this->getDefaultSampleRow()];
            }
            $this->syncSampleRowsFromIndexedFields($submissionForm);
        }
    }

    public function updated($propertyName, $value): void
    {
        if (preg_match('/^formData\.analysis_type_id(?:\.(\d+))?$/', $propertyName, $matches)) {
            $rowIndex = isset($matches[1]) ? (int) $matches[1] : null;
            $this->refreshScheduleParametersForAnalysisChange($rowIndex);

            return;
        }

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

        // Auto-select all parameters when analysis changes.
        if (in_array($fieldKey, ['analysis_type', 'analysis_types', 'analysis_type_id'], true)) {
            $this->refreshScheduleParametersForAnalysisChange(null);
        }
    }

    public function addSchemaRow(string $sectionId): void
    {
        $form = $this->submissionForm;
        if ($form === null) {
            return;
        }

        $section = $form->sections->firstWhere('id', $sectionId);
        if ($section === null || ($section->section_type ?? '') !== 'rows_section') {
            return;
        }

        foreach ($section->elementHolders->flatMap->elements as $element) {
            if (SubmissionFormSchemaHelper::shouldOmitFromFillForm($element, $section)) {
                continue;
            }

            $existing = $this->formData[$element->name] ?? [];
            if (! is_array($existing)) {
                $existing = [];
            }
            $existing[] = $this->defaultValueForElement($element);
            $this->formData[$element->name] = $existing;
        }

        $this->dispatch('schedule-trf-reinit-widgets');
    }

    public function removeSchemaRow(string $sectionId, int $rowIndex): void
    {
        $form = $this->submissionForm;
        if ($form === null) {
            return;
        }

        $section = $form->sections->firstWhere('id', $sectionId);
        if ($section === null || ($section->section_type ?? '') !== 'rows_section') {
            return;
        }

        foreach ($section->elementHolders->flatMap->elements as $element) {
            $name = (string) ($element->name ?? '');
            if ($name === '' || ! isset($this->formData[$name]) || ! is_array($this->formData[$name])) {
                continue;
            }

            unset($this->formData[$name][$rowIndex]);
            $this->formData[$name] = array_values($this->formData[$name]);
        }
    }

    private function defaultValueForElement(SubmissionFormElement $element): mixed
    {
        if ($element->element_type === 'checkbox') {
            $options = $element->options ?? [];

            if (($element->name ?? '') === 'test_requirements') {
                return '';
            }

            return is_array($options) && $options !== [] ? [] : false;
        }

        if ($element->element_type === 'analysis_elements_select' || ($element->name ?? '') === 'parameters') {
            return [];
        }

        if (
            $element->element_type === 'sample_type_select'
            || in_array((string) ($element->name ?? ''), ['sample_type_id', 'sample_type'], true)
        ) {
            return [];
        }

        if (
            $element->element_type === 'analysis_type_select'
            || in_array((string) ($element->name ?? ''), ['analysis_type_id', 'analysis_type', 'analysis_types'], true)
        ) {
            return [];
        }

        return '';
    }

    /**
     * @return \Illuminate\Support\Collection<int, AnalysisType>
     */
    public function analysisTypesForRow(?int $rowIndex = null): \Illuminate\Support\Collection
    {
        return $this->analysisTypes;
    }

    /**
     * @param  list<string|int|float>  $sampleTypeIds
     */
    public function setWalkInSampleTypes(string $wireKey, array $sampleTypeIds): void
    {
        $relative = preg_replace('/^formData\./', '', $wireKey) ?: 'sample_type_id';
        $ids = array_values(array_unique(array_filter(array_map(
            static fn ($value): string => (string) $value,
            $sampleTypeIds
        ), static fn (string $id): bool => $id !== '')));

        data_set($this->formData, $relative, $ids);

        $rowIndex = null;
        if (preg_match('/^(?:sample_type_id|sample_type)\.(\d+)$/', $relative, $matches)) {
            $rowIndex = (int) $matches[1];
        }

        foreach (['analysis_type_id', 'analysis_type', 'analysis_types', 'parameter', 'parameters'] as $key) {
            if (! array_key_exists($key, $this->formData)) {
                continue;
            }
            if ($rowIndex !== null && is_array($this->formData[$key])) {
                $this->formData[$key][$rowIndex] = in_array($key, ['parameter', 'parameters', 'analysis_type_id', 'analysis_type', 'analysis_types'], true) ? [] : '';
            }
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Analyte>
     */
    public function parametersForRow(?int $rowIndex = null): \Illuminate\Support\Collection
    {
        if (! $this->selectedSampleTypeId) {
            return collect();
        }

        $analysisTypeIds = [];
        foreach (['analysis_type_id', 'analysis_type', 'analysis_types'] as $key) {
            $value = $this->formData[$key] ?? null;
            if ($rowIndex !== null) {
                $value = is_array($value) ? ($value[$rowIndex] ?? null) : null;
            }

            if ($value === null || $value === '') {
                continue;
            }

            if (! is_array($value)) {
                $value = [(string) $value];
            }

            foreach ($value as $item) {
                if (is_scalar($item) && (string) $item !== '') {
                    $analysisTypeIds[] = (string) $item;
                }
            }
        }

        $analysisTypeIds = array_values(array_unique($analysisTypeIds));
        if ($analysisTypeIds === []) {
            return collect();
        }

        $validIds = AnalysisType::query()
            ->where('sample_type_id', $this->selectedSampleTypeId)
            ->where(function ($query) use ($analysisTypeIds): void {
                $query->whereIn('id', $analysisTypeIds)
                    ->orWhereIn('name', $analysisTypeIds);
            })
            ->pluck('id')
            ->map(static fn ($id): string => (string) $id)
            ->all();

        if ($validIds === []) {
            return collect();
        }

        return \App\Analyte::whereHas('analysis_elements', function ($q) use ($validIds): void {
            $q->whereIn('analysis_type_id', $validIds)->where('active', true);
        })->orderBy('name')->get();
    }

    /**
     * @return array{
     *     selected: list<string>,
     *     options: list<array{id: string, name: string}>,
     *     groups: list<array{analysis_type_id: string, analysis_type: string, sample_type: string, tests: list<array{id: string, name: string}>}>
     * }
     */
    public function walkInParameterPickerState(int $rowIndex): array
    {
        $raw = $this->formData['parameters'][$rowIndex] ?? [];
        $selected = is_array($raw)
            ? array_values(array_map('strval', $raw))
            : ($raw !== '' && $raw !== null ? [(string) $raw] : []);

        $groups = $this->parameterGroupsForRow($rowIndex);
        $options = $this->flattenParameterPickerOptions($groups);

        return [
            'selected' => $this->normalizeWalkInParameterSelection($selected, $groups),
            'options' => $options,
            'groups' => $groups,
        ];
    }

    /**
     * @return list<array{value: string, label: string, meta?: string, meta_method?: string, meta_lab?: string}>
     */
    public function walkInParameterSelectOptions(int $rowIndex = 0): array
    {
        $picker = $this->walkInParameterPickerState($rowIndex);
        $base = collect($picker['options'] ?? [])
            ->map(static fn (array $option): array => [
                'value' => (string) ($option['id'] ?? ''),
                'label' => (string) ($option['name'] ?? $option['id'] ?? ''),
            ])
            ->filter(static fn (array $option): bool => $option['value'] !== '')
            ->values()
            ->all();

        return app(WalkInParameterCatalogService::class)->enrichSelectOptions($base);
    }

    /**
     * @return list<string>
     */
    private function resolveSampleTypeIdsForScheduleRow(int $rowIndex): array
    {
        $ids = [];
        foreach (['sample_type_id', 'sample_type'] as $key) {
            $value = $this->formData[$key] ?? null;
            $cell = is_array($value) ? ($value[$rowIndex] ?? null) : null;
            if ($cell === null || $cell === '') {
                continue;
            }
            if (! is_array($cell)) {
                $cell = [(string) $cell];
            }
            foreach ($cell as $item) {
                if (is_scalar($item) && (string) $item !== '') {
                    $ids[] = (string) $item;
                }
            }
        }

        if ($ids === [] && $this->selectedSampleTypeId) {
            $ids[] = (string) $this->selectedSampleTypeId;
        }

        return array_values(array_unique($ids));
    }

    private function walkInParameterCatalog(): WalkInParameterCatalogService
    {
        return app(WalkInParameterCatalogService::class);
    }

    /**
     * @return list<array{analysis_type_id: string, analysis_type: string, sample_type: string, tests: list<array<string, mixed>>}>
     */
    private function parameterGroupsForRow(int $rowIndex): array
    {
        $sampleTypeIds = $this->resolveSampleTypeIdsForScheduleRow($rowIndex);
        if ($sampleTypeIds === []) {
            return [];
        }

        return $this->walkInParameterCatalog()->groupsForSampleTypes($sampleTypeIds);
    }

    /**
     * @param  list<array{tests: list<array{id: string, name: string}>}>  $groups
     * @return list<array{id: string, name: string}>
     */
    private function flattenParameterPickerOptions(array $groups): array
    {
        return $this->walkInParameterCatalog()->flattenGroups($groups);
    }

    /**
     * @param  list<string>  $selected
     * @param  list<array{tests: list<array{id: string, name: string}>}>  $groups
     * @return list<string>
     */
    private function normalizeWalkInParameterSelection(array $selected, array $groups): array
    {
        return $this->walkInParameterCatalog()->normalizeSelection($selected, $groups);
    }

    /**
     * @param  list<string>  $parameterIds
     */
    private function syncDerivedAnalysisTypesForScheduleRow(int $rowIndex, array $parameterIds): void
    {
        $derived = $this->walkInParameterCatalog()->deriveAnalysisTypeIds($parameterIds);

        foreach (['analysis_type_id', 'analysis_type', 'analysis_types'] as $key) {
            if (! array_key_exists($key, $this->formData)) {
                continue;
            }
            if (is_array($this->formData[$key])) {
                $this->formData[$key][$rowIndex] = $derived;
            }
        }
    }

    /**
     * @param  list<string|int|float>  $parameters
     */
    public function setWalkInParameters(int $rowIndex, array $parameters): void
    {
        $groups = $this->parameterGroupsForRow(max(0, $rowIndex));
        $normalized = $this->normalizeWalkInParameterSelection(
            array_values(array_map(static fn ($value): string => (string) $value, $parameters)),
            $groups
        );

        if ($rowIndex < 0) {
            $this->formData['parameters'] = $normalized;
            if (array_key_exists('parameter', $this->formData)) {
                $this->formData['parameter'] = $normalized;
            }

            return;
        }

        $this->formData['parameters'][$rowIndex] = $normalized;
        $this->syncDerivedAnalysisTypesForScheduleRow($rowIndex, $normalized);
    }

    public function toggleWalkInAnalysisType(string $wireKey, string $analysisTypeId): void
    {
        $relative = preg_replace('/^formData\./', '', $wireKey) ?: 'analysis_type_id';
        $id = trim((string) $analysisTypeId);
        if ($id === '') {
            return;
        }

        $current = data_get($this->formData, $relative, []);
        if (! is_array($current)) {
            $current = filled($current) ? [(string) $current] : [];
        }

        $ids = array_values(array_unique(array_filter(array_map(
            static fn ($value): string => (string) $value,
            $current
        ), static fn (string $value): bool => $value !== '')));

        if (in_array($id, $ids, true)) {
            $ids = array_values(array_filter($ids, static fn (string $value): bool => $value !== $id));
        } else {
            $ids[] = $id;
        }

        data_set($this->formData, $relative, $ids);

        $rowIndex = null;
        if (preg_match('/^(?:analysis_type_id|analysis_type|analysis_types)\.(\d+)$/', $relative, $matches)) {
            $rowIndex = (int) $matches[1];
        }

        $this->refreshScheduleParametersForAnalysisChange($rowIndex);
    }

    /**
     * @param  list<string|int|float>  $analysisTypeIds
     */
    public function setWalkInAnalysisTypes(string $wireKey, array $analysisTypeIds): void
    {
        $relative = preg_replace('/^formData\./', '', $wireKey) ?: 'analysis_type_id';
        $ids = array_values(array_unique(array_filter(array_map(
            static fn ($value): string => (string) $value,
            $analysisTypeIds
        ), static fn (string $id): bool => $id !== '')));

        data_set($this->formData, $relative, $ids);

        $rowIndex = null;
        if (preg_match('/^(?:analysis_type_id|analysis_type|analysis_types)\.(\d+)$/', $relative, $matches)) {
            $rowIndex = (int) $matches[1];
        }

        $this->refreshScheduleParametersForAnalysisChange($rowIndex);
    }

    /**
     * Auto-select every parameter available for the selected analysis.
     */
    private function refreshScheduleParametersForAnalysisChange(?int $rowIndex): void
    {
        $effectiveRowIndex = $rowIndex ?? 0;
        $state = $this->walkInParameterPickerState($effectiveRowIndex);
        $options = $state['options'];
        $groups = $state['groups'];
        $selected = array_values(array_map(
            static fn (array $option): string => (string) $option['id'],
            $options
        ));

        if ($rowIndex !== null) {
            $this->formData['parameters'][$rowIndex] = $selected;

            $this->dispatch(
                'schedule-trf-params-row-reset',
                rowIndex: $rowIndex,
                options: $options,
                selected: $selected,
                groups: $groups,
            );

            return;
        }

        foreach (['parameter', 'parameters'] as $paramKey) {
            if (array_key_exists($paramKey, $this->formData)) {
                $this->formData[$paramKey] = $selected;
            }
        }

        if (! array_key_exists('parameters', $this->formData) && ! array_key_exists('parameter', $this->formData)) {
            $this->formData['parameters'] = $selected;
        }

        $this->dispatch(
            'schedule-trf-params-row-reset',
            rowIndex: $effectiveRowIndex,
            options: $options,
            selected: $selected,
        );
    }

    public function getCustomersProperty()
    {
        return CRMCustomer::where('active', 1)->orderBy('name')->get();
    }

    public function getAnalysisTypesProperty()
    {
        if (! $this->selectedSampleTypeId) {
            return collect();
        }

        return AnalysisType::query()
            ->where('sample_type_id', $this->selectedSampleTypeId)
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, SampleType>
     */
    public function getSampleTypesProperty(): Collection
    {
        return SampleType::query()
            ->where('active', 1)
            ->orderBy('name')
            ->get();
    }

    public function getParametersProperty()
    {
        return $this->parametersForRow(null);
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

        // Pull schedule sample/test/collection data into formData before validation/persist.
        $this->hydrateFormDataFromSchedule($submissionForm);

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

        if (! empty($rules)) {
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

            $payload = $this->buildScheduleSubmissionPayload($submissionForm);

            app(SubmissionFormSubmissionService::class)->submitWalkInInstance(
                $submissionForm,
                $payload,
                $schedule->crm_customer_id !== null ? (string) $schedule->crm_customer_id : null,
                (string) $this->selectedSampleTypeId,
                CommercialEnquirySyncService::SOURCE_SCHEDULED,
                (string) $this->selectedScheduleId,
            );

            $schedule->refresh();
            $schedule->load('submissionFormInstances.values.element');
            $progress = app(SamplingScheduleCollectionProgress::class)->refresh($schedule);

            DB::commit();

            $this->showFormModal = false;
            if ($progress['is_complete']) {
                $this->message = 'Form saved. All '.$progress['scheduled'].' scheduled sample(s) are now collected.';
            } else {
                $this->message = 'Form saved. Collection progress: '.$progress['label'].' samples (partial until complete).';
            }
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Schedule TRF save failed: '.$e->getMessage(), [
                'schedule_id' => $this->selectedScheduleId,
                'sample_type_id' => $this->selectedSampleTypeId,
                'trace' => $e->getTraceAsString(),
            ]);
            $this->message = 'Error saving form response: '.$e->getMessage();
            $this->messageType = 'error';
        }
    }

    /**
     * Copy schedule analysis/parameters/qty/datetime into TRF formData so lab request view is populated.
     */
    private function hydrateFormDataFromSchedule(SubmissionForm $submissionForm): void
    {
        if (! $this->selectedScheduleId || ! $this->selectedSampleTypeId) {
            return;
        }

        $schedule = SamplingSchedule::query()->find($this->selectedScheduleId);
        if ($schedule === null) {
            return;
        }

        $this->formData = app(SamplingScheduleTrfSync::class)->mergeIntoFormData(
            $this->formData,
            $schedule,
            (string) $this->selectedSampleTypeId,
            $submissionForm,
        );

        $matchingEntry = $this->matchingScheduleSampleEntry($schedule, (string) $this->selectedSampleTypeId);
        $location = ! empty($schedule->sample_point_id)
            ? (string) $schedule->sample_point_id
            : $this->resolveSamplePointIdForSchedule($schedule);
        if ($location === '') {
            $location = trim((string) ($schedule->location ?? ''));
        }
        $qty = max(1, (int) ($schedule->number_of_samples ?? 1));
        $parameterIds = is_array($matchingEntry['parameters'] ?? null) ? $matchingEntry['parameters'] : [];
        $parameterNames = $this->resolveParameterNames($parameterIds);

        if ($this->isFood || $this->isWater || $this->isWasteWater) {
            $scheduleRowValues = [];

            if ($parameterNames !== []) {
                $scheduleRowValues['parameters'] = $parameterNames;
            }
            if ($matchingEntry !== null && ! empty($matchingEntry['analysis_type_id'])) {
                $scheduleRowValues['analysis_type_id'] = (string) $matchingEntry['analysis_type_id'];
            }
            if ($location !== '') {
                $scheduleRowValues['sampling_point'] = $location;
                $scheduleRowValues['location'] = $location;
            }
            $scheduleRowValues['qty'] = (string) $qty;
            $scheduleRowValues['sample_quantity'] = $qty;

            $scheduleDescription = $this->formData['sample_description'] ?? null;
            if (is_array($scheduleDescription)) {
                $scheduleDescription = $scheduleDescription[0] ?? null;
            }
            if (is_string($scheduleDescription) && trim($scheduleDescription) !== '') {
                $currentDescription = $this->formData['sample_description'][0] ?? null;
                if ($this->isEmptyFormValue($currentDescription)) {
                    $scheduleRowValues['sample_description'] = $scheduleDescription;
                }
            }

            foreach ($scheduleRowValues as $key => $value) {
                $this->assignFormValue((string) $key, $value, $submissionForm);
            }

            // Keep sample_rows aligned with indexed TRF fields (expiration_date, batch_number, etc.).
            $this->syncSampleRowsFromIndexedFields($submissionForm);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildScheduleSubmissionPayload(SubmissionForm $submissionForm): array
    {
        // Final safety net: re-apply schedule values so lab receives collection/qty/tests
        // even if Livewire state drifted after hydrate.
        $this->hydrateFormDataFromSchedule($submissionForm);
        $this->syncSampleRowsFromIndexedFields($submissionForm);

        $normalizer = app(SubmissionFormValueNormalizer::class);
        $payload = array_merge(
            $this->formData,
            $normalizer->toRequestPayload($this->formData),
        );

        // Indexed row fields are the source of truth for processFormData persistence.
        foreach ($this->rowElementNames($submissionForm) as $name) {
            if (! array_key_exists($name, $this->formData) || ! is_array($this->formData[$name])) {
                continue;
            }

            $payload[$name] = $this->formData[$name];
        }

        // Prefer original formData scalars over normalized empties for schedule fields.
        $schedule = SamplingSchedule::query()->find((string) $this->selectedScheduleId);
        if ($schedule !== null) {
            foreach (app(SamplingScheduleTrfSync::class)->fieldValuesFromSchedule(
                $schedule,
                (string) $this->selectedSampleTypeId,
            ) as $name => $value) {
                if ($value === null || $value === '' || $value === []) {
                    continue;
                }

                $fromForm = $this->formData[$name] ?? $value;
                if (is_array($fromForm) && array_keys($fromForm) === range(0, count($fromForm) - 1)) {
                    // Keep indexed row arrays intact for processFormData.
                    $payload[$name] = $fromForm;
                } elseif (is_array($fromForm)) {
                    $payload[$name] = $value;
                } else {
                    $payload[$name] = $fromForm;
                }
            }
        }

        // Rows-section fields must stay as numerically indexed arrays so values
        // persist with array_index (lab request view groups by that).
        foreach ($this->rowElementNames($submissionForm) as $name) {
            if (! array_key_exists($name, $payload)) {
                continue;
            }

            if (! is_array($payload[$name])) {
                $payload[$name] = [$payload[$name]];
            } elseif ($payload[$name] !== [] && array_keys($payload[$name]) !== range(0, count($payload[$name]) - 1)) {
                $payload[$name] = array_values($payload[$name]);
            }
        }

        return $payload;
    }

    /**
     * @return list<string>
     */
    private function rowElementNames(SubmissionForm $submissionForm): array
    {
        $names = [];
        foreach ($submissionForm->sections as $section) {
            if (($section->section_type ?? '') !== 'rows_section') {
                continue;
            }
            foreach ($section->elementHolders as $holder) {
                foreach ($holder->elements as $element) {
                    $name = (string) ($element->name ?? '');
                    if ($name !== '') {
                        $names[] = $name;
                    }
                }
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * @param  array<string, mixed>|null  $matchingEntry
     * @return array{sample_type_id?: string, analysis_type_id?: string, parameters?: list<string>}|null
     */
    private function matchingScheduleSampleEntry(SamplingSchedule $schedule, string $sampleTypeId): ?array
    {
        if (! empty($schedule->sample_details) && is_array($schedule->sample_details)) {
            foreach ($schedule->sample_details as $entry) {
                if (($entry['sample_type_id'] ?? '') === $sampleTypeId) {
                    return is_array($entry) ? $entry : null;
                }
            }

            // Fall back to first schedule sample entry when IDs were remapped/renamed.
            $first = $schedule->sample_details[0] ?? null;
            if (is_array($first) && (! empty($first['analysis_type_id']) || ! empty($first['parameters']))) {
                return $first;
            }
        }

        if ((string) ($schedule->sample_type_id ?? '') === $sampleTypeId
            || (string) ($schedule->sample_type_id ?? '') !== '') {
            return [
                'sample_type_id' => (string) ($schedule->sample_type_id ?? $sampleTypeId),
                'analysis_type_id' => $schedule->analysis_type_id ? (string) $schedule->analysis_type_id : null,
                'parameters' => is_array($schedule->parameters ?? null) ? $schedule->parameters : [],
            ];
        }

        return null;
    }

    /**
     * @param  list<string>  $parameterIds
     * @return list<string>
     */
    private function resolveParameterNames(array $parameterIds): array
    {
        $parameterIds = array_values(array_filter(array_map('strval', $parameterIds)));
        if ($parameterIds === []) {
            return [];
        }

        $byAnalyte = \App\Analyte::query()
            ->whereIn('id', $parameterIds)
            ->pluck('name', 'id');

        $names = [];
        $missing = [];
        foreach ($parameterIds as $id) {
            $name = trim((string) ($byAnalyte[$id] ?? ''));
            if ($name !== '') {
                $names[] = $name;
            } else {
                $missing[] = $id;
            }
        }

        if ($missing !== []) {
            $fromElements = AnalysisElements::query()
                ->with('analyte')
                ->whereIn('id', $missing)
                ->get();

            foreach ($fromElements as $element) {
                $name = trim((string) ($element->analyte?->name ?? $element->method ?? ''));
                if ($name !== '') {
                    $names[] = $name;
                    $idx = array_search((string) $element->id, $missing, true);
                    if ($idx !== false) {
                        unset($missing[$idx]);
                    }
                }
            }
        }

        // Last resort: keep unresolved IDs so display resolvers can still map them.
        foreach ($missing as $id) {
            $names[] = (string) $id;
        }

        return array_values(array_unique(array_filter($names, fn (string $name): bool => $name !== '')));
    }

    private function assignFormValue(string $name, mixed $value, SubmissionForm $submissionForm, bool $textOnly = false): void
    {
        if ($this->isEmptyFormValue($value)) {
            return;
        }

        if ($name === 'parameters' && is_string($value) && $value !== '') {
            $value = array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        $element = null;
        $sectionType = null;

        foreach ($submissionForm->sections as $section) {
            foreach ($section->elementHolders as $holder) {
                foreach ($holder->elements as $el) {
                    if ((string) ($el->name ?? '') === $name) {
                        $element = $el;
                        $sectionType = (string) ($section->section_type ?? '');
                        break 3;
                    }
                }
            }
        }

        if ($element === null) {
            return;
        }

        if ($textOnly && in_array((string) $element->element_type, [
            'customer_sample_point_select',
            'sample_point_select',
            'client_select',
            'client_contact_select',
            'analysis_type_select',
            'analysis_elements_select',
            'sample_type_select',
        ], true)) {
            return;
        }

        $forceRowFields = ['analysis_type_id', 'parameters', 'sample_quantity', 'number_of_samples', 'sampling_point', 'location'];
        $forceScalarFields = ['sampling_date', 'sampling_time', 'date_received', 'sample_quantity', 'number_of_samples', 'sampling_location', 'sampling_point', 'location'];
        $isRowField = $sectionType === 'rows_section';

        if ($isRowField) {
            if (! isset($this->formData[$name]) || ! is_array($this->formData[$name])) {
                $this->formData[$name] = [];
            }

            $current = $this->formData[$name][0] ?? null;
            $isEmpty = $this->isEmptyFormValue($current);
            if ($isEmpty || in_array($name, $forceRowFields, true)) {
                $this->formData[$name][0] = $value;
            }

            return;
        }

        $current = $this->formData[$name] ?? null;
        $isEmpty = $this->isEmptyFormValue($current);
        if ($isEmpty || in_array($name, $forceScalarFields, true)) {
            $this->formData[$name] = $value;
        }
    }

    /**
     * Mirror indexed TRF row fields into sample_rows for enquiry/PDF normalizers.
     */
    private function syncSampleRowsFromIndexedFields(SubmissionForm $submissionForm): void
    {
        $rowCount = $this->schemaRowCount($submissionForm);
        if ($rowCount < 1) {
            return;
        }

        $rows = [];
        foreach (range(0, $rowCount - 1) as $rowIndex) {
            $row = [];
            foreach ($this->rowElementNames($submissionForm) as $name) {
                if (! isset($this->formData[$name]) || ! is_array($this->formData[$name])) {
                    continue;
                }

                if (array_key_exists($rowIndex, $this->formData[$name])) {
                    $row[$name] = $this->formData[$name][$rowIndex];
                }
            }

            if ($row !== []) {
                $rows[] = $row;
            }
        }

        if ($rows !== []) {
            $this->formData['sample_rows'] = $rows;
        }
    }

    private function schemaRowCount(SubmissionForm $submissionForm): int
    {
        $count = 0;
        foreach ($this->rowElementNames($submissionForm) as $name) {
            if (isset($this->formData[$name]) && is_array($this->formData[$name])) {
                $count = max($count, count($this->formData[$name]));
            }
        }

        return max(1, $count);
    }

    private function isEmptyFormValue(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [] || $value === false;
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
