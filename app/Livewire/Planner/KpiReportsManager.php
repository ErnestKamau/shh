<?php

namespace App\Livewire\Planner;

use App\AnalysisType;
use App\Analyte;
use App\Exports\Planner\PlannerKpiReportExport;
use App\Models\CRM\CustomerContact;
use App\SampleType;
use App\Services\Planner\PlannerKpiReportService;
use Illuminate\Support\Collection;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KpiReportsManager extends Component
{
    public string $search = '';

    public bool $showMoreFilters = false;

    public string $filterDateFrom = '';

    public string $filterDateTo = '';

    public string $filterClientId = '';

    public string $filterContactId = '';

    public string $filterContractValidFrom = '';

    public string $filterContractValidTo = '';

    public string $filterSampleTypeId = '';

    public string $filterAnalysisTypeId = '';

    public string $filterFrequency = '';

    public string $filterScheduledMin = '';

    public string $filterScheduledMax = '';

    public string $filterCollectedMin = '';

    public string $filterCollectedMax = '';

    public string $filterParameterId = '';

    public string $filterCollectionStatus = 'all';

    public string $message = '';

    public string $messageType = '';

    /** @var list<string> */
    public array $frequencies = ['One-time', 'Daily', 'Weekly', 'Monthly', 'Quarterly', 'Annually'];

    public function mount(): void
    {
        $this->filterDateFrom = now()->subDays(30)->toDateString();
        $this->filterDateTo = now()->toDateString();
    }

    /**
     * @return array<string, mixed>
     */
    private function filterPayload(): array
    {
        return [
            'search' => $this->search,
            'date_from' => $this->filterDateFrom,
            'date_to' => $this->filterDateTo,
            'client_id' => $this->filterClientId,
            'contact_id' => $this->filterContactId,
            'contract_valid_from' => $this->filterContractValidFrom,
            'contract_valid_to' => $this->filterContractValidTo,
            'sample_type_id' => $this->filterSampleTypeId,
            'analysis_type_id' => $this->filterAnalysisTypeId,
            'frequency' => $this->filterFrequency,
            'scheduled_min' => $this->filterScheduledMin,
            'scheduled_max' => $this->filterScheduledMax,
            'collected_min' => $this->filterCollectedMin,
            'collected_max' => $this->filterCollectedMax,
            'parameter_id' => $this->filterParameterId,
            'collection_status' => $this->filterCollectionStatus,
        ];
    }

    public function getReportRowsProperty(): Collection
    {
        return app(PlannerKpiReportService::class)->getReportRows($this->filterPayload());
    }

    /**
     * @return array{total: int, collected: int, pending: int, partial: int, collection_rate: float}
     */
    public function getSummaryProperty(): array
    {
        return app(PlannerKpiReportService::class)->summarize($this->reportRows);
    }

    public function getClientsProperty(): Collection
    {
        return collect(app(PlannerKpiReportService::class)->activeClients());
    }

    public function getContactsProperty(): Collection
    {
        $query = CustomerContact::query()
            ->where('active', 1)
            ->orderBy('first_name');

        if ($this->filterClientId !== '') {
            $query->where('crm_customer_id', $this->filterClientId);
        }

        return $query->get();
    }

    public function getSampleTypesProperty(): Collection
    {
        return SampleType::query()->where('active', 1)->orderBy('name')->get();
    }

    public function getAnalysisTypesProperty(): Collection
    {
        $query = AnalysisType::query()->where('active', 1);

        if ($this->filterSampleTypeId !== '') {
            $query->where('sample_type_id', $this->filterSampleTypeId);
        }

        return $query->orderBy('name')->get();
    }

    public function getParametersProperty(): Collection
    {
        return Analyte::query()->where('active', 1)->orderBy('name')->get();
    }

    public function updatedFilterClientId(): void
    {
        $this->filterContactId = '';
    }

    public function toggleMoreFilters(): void
    {
        $this->showMoreFilters = ! $this->showMoreFilters;
    }

    public function getActiveMoreFiltersCountProperty(): int
    {
        $count = 0;

        if ($this->filterContactId !== '') {
            $count++;
        }
        if ($this->filterContractValidFrom !== '') {
            $count++;
        }
        if ($this->filterContractValidTo !== '') {
            $count++;
        }
        if ($this->filterSampleTypeId !== '') {
            $count++;
        }
        if ($this->filterAnalysisTypeId !== '') {
            $count++;
        }
        if ($this->filterFrequency !== '') {
            $count++;
        }
        if ($this->filterScheduledMin !== '') {
            $count++;
        }
        if ($this->filterScheduledMax !== '') {
            $count++;
        }
        if ($this->filterCollectedMin !== '') {
            $count++;
        }
        if ($this->filterCollectedMax !== '') {
            $count++;
        }
        if ($this->filterParameterId !== '') {
            $count++;
        }
        if ($this->filterCollectionStatus !== 'all') {
            $count++;
        }

        return $count;
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterDateFrom = now()->subDays(30)->toDateString();
        $this->filterDateTo = now()->toDateString();
        $this->filterClientId = '';
        $this->filterContactId = '';
        $this->filterContractValidFrom = '';
        $this->filterContractValidTo = '';
        $this->filterSampleTypeId = '';
        $this->filterAnalysisTypeId = '';
        $this->filterFrequency = '';
        $this->filterScheduledMin = '';
        $this->filterScheduledMax = '';
        $this->filterCollectedMin = '';
        $this->filterCollectedMax = '';
        $this->filterParameterId = '';
        $this->filterCollectionStatus = 'all';
        $this->showMoreFilters = false;
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function exportToExcel(): mixed
    {
        try {
            $rows = $this->reportRows;

            if ($rows->isEmpty()) {
                $this->message = 'No data to export.';
                $this->messageType = 'error';

                return null;
            }

            $filename = 'planner_kpi_report_'.now()->format('Y-m-d_H-i-s').'.xlsx';

            return Excel::download(new PlannerKpiReportExport($rows), $filename);
        } catch (\Throwable $e) {
            $this->message = 'Error generating Excel: '.$e->getMessage();
            $this->messageType = 'error';

            return null;
        }
    }

    public function exportToCsv(): StreamedResponse
    {
        $rows = $this->reportRows;
        $filename = 'planner_kpi_report_'.now()->format('Y-m-d_H-i-s').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Date',
                'Client Name',
                'Contract Validity',
                'Contact Details',
                'Sample Categories (Scheduled)',
                'Sample Categories (Collected)',
                'Sample Details (Scheduled)',
                'Sample Details (Collected)',
                'No. Samples Scheduled',
                'No. Samples Collected',
                'Frequency',
                'Parameters (Scheduled)',
                'Parameters (Collected)',
                'Collection Status',
                'Collected At',
                'Location',
                'Personnel',
            ]);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row['date'],
                    $row['client_name'],
                    $row['contract_validity'],
                    $row['contact_details'],
                    $row['scheduled_categories'],
                    $row['collected_categories'],
                    $row['scheduled_details'],
                    $row['collected_details'],
                    $row['scheduled_samples'],
                    $row['collected_samples'],
                    $row['frequency'],
                    $row['scheduled_parameters'],
                    $row['collected_parameters'],
                    ucfirst($row['status']),
                    $row['collected_at'] ?? '',
                    $row['location'],
                    $row['personnel'],
                ]);
            }

            fclose($file);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function render()
    {
        return view('livewire.planner.kpi-reports-manager');
    }
}
