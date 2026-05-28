<?php

namespace App\Livewire\Equipment\Depreciation;

use App\Services\Equipment\Depreciation\DepreciationReportService;
use Livewire\Component;

class DepreciationReportsManager extends Component
{
    public string $reportType = 'asset_register';

    public string $year = '';

    public string $month = '';

    public ?array $reportData = null;

    /**
     * @return array<string, string>
     */
    public function getReportTypesProperty(): array
    {
        return [
            'asset_register' => 'Asset Register',
            'monthly' => 'Monthly Depreciation',
            'yearly' => 'Yearly Summary',
            'book_value' => 'Book Value',
            'appraisal' => 'Appraisal Adjustments',
            'fully_depreciated' => 'Fully Depreciated',
            'forecast' => 'Forecast',
        ];
    }

    public function mount(): void
    {
        $this->year = (string) now()->year;
        $this->month = (string) now()->month;
        $this->loadReport();
    }

    public function switchReportType(string $type): void
    {
        if (! array_key_exists($type, $this->reportTypes)) {
            return;
        }

        if ($this->reportType === $type) {
            return;
        }

        $this->reportType = $type;
        $this->loadReport();
    }

    public function applyPeriodFilters(): void
    {
        if (in_array($this->reportType, ['monthly', 'yearly'], true)) {
            $this->loadReport();
        }
    }

    public function loadReport(): void
    {
        $service = app(DepreciationReportService::class);

        $this->reportData = match ($this->reportType) {
            'asset_register' => ['rows' => $service->assetRegister()->all()],
            'monthly' => ['rows' => $service->monthlyDepreciation($this->year, $this->month)->all()],
            'yearly' => ['summary' => $service->yearlySummary($this->year)],
            'book_value' => ['rows' => $service->bookValueReport()->all()],
            'appraisal' => ['rows' => $service->appraisalAdjustments()->all()],
            'fully_depreciated' => ['rows' => $service->fullyDepreciated()->all()],
            'forecast' => ['rows' => $service->forecast()->all()],
            default => ['rows' => []],
        };
    }

    public function getRecordCountProperty(): int
    {
        if ($this->reportData === null) {
            return 0;
        }

        if (isset($this->reportData['rows'])) {
            return count($this->reportData['rows']);
        }

        return isset($this->reportData['summary']) ? 1 : 0;
    }

    public function render()
    {
        return view('livewire.equipment.depreciation.depreciation-reports-manager');
    }
}
