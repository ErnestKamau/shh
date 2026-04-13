<?php

namespace App\Livewire\Crm\Customer\Tabs;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\CRMCustomer;

/*
 * Configurations tab disabled — original implementation commented out.
 * Uncomment the block below and remove the stub class at the end to restore.
 *
use App\Models\CRMCustomerReportInfoColumn;
use App\Standards;

class ConfigurationsManager extends BaseCrmComponent
{
    public CRMCustomer $customer;

    public ?string $labelLimits = '';

    public ?string $labelTestConformance = '';

    public ?string $showLimits = null;

    public ?string $showLod = null;

    public ?string $showTestConformance = null;

    public ?string $showGrade = null;

    public bool $showStandardsBelowLimits = false;

    public array $standardsToShow = [];

    public array $infoColumns = [];

    public string $newInfoSource = '';

    public string $newInfoLabel = '';

    public int $maxInfoFields = 25;

    public function mount(CRMCustomer $customer): void
    {
        $this->customer = $customer->load('reportInfoColumns');

        $rc = $this->customer->report_columns_config ?? [];
        $this->labelLimits = $rc['label_limits'] ?? '';
        $this->labelTestConformance = $rc['label_test_cponformance'] ?? '';
        $this->showLimits = isset($rc['show_limits']) ? ($rc['show_limits'] ? '1' : '0') : null;
        $this->showLod = isset($rc['show_lod']) ? ($rc['show_lod'] ? '1' : '0') : null;
        $this->showTestConformance = isset($rc['show_test_conformance']) ? ($rc['show_test_conformance'] ? '1' : '0') : null;
        $this->showGrade = isset($rc['show_grade']) ? ($rc['show_grade'] ? '1' : '0') : null;
        $this->showStandardsBelowLimits = $rc['show_standards_below_limits'] ?? false;
        $this->standardsToShow = $rc['standards_to_show'] ?? [];

        foreach ($this->customer->reportInfoColumns as $col) {
            $this->infoColumns[] = [
                'source' => $col->source,
                'source_key' => $col->source_key,
                'display_label' => $col->getRawOriginal('display_label') ?? '',
            ];
        }
    }

    public function addInfoField(): void
    {
        if (empty($this->newInfoSource)) {
            $this->addError('newInfoSource', 'Please select a source field first.');

            return;
        }

        if (count($this->infoColumns) >= $this->maxInfoFields) {
            $this->showError("Maximum {$this->maxInfoFields} info fields allowed. Remove one to add more.");

            return;
        }

        $parts = explode(':', $this->newInfoSource, 3);
        if (count($parts) >= 2) {
            $this->infoColumns[] = [
                'source' => $parts[0],
                'source_key' => $parts[1],
                'display_label' => trim($this->newInfoLabel) ?: ($parts[2] ?? ''),
            ];
            $this->newInfoSource = '';
            $this->newInfoLabel = '';
        }
    }

    public function removeInfoField(int $index): void
    {
        unset($this->infoColumns[$index]);
        $this->infoColumns = array_values($this->infoColumns);
    }

    public function save(): void
    {
        $infoColumnsRaw = array_map(function ($col) {
            $label = $col['display_label'] ?? '';

            return $col['source'].':'.$col['source_key'].':'.$label;
        }, $this->infoColumns);

        $this->validate([
            'showLimits' => ['nullable', 'integer', 'in:0,1'],
            'showLod' => ['nullable', 'integer', 'in:0,1'],
            'showTestConformance' => ['nullable', 'integer', 'in:0,1'],
            'showGrade' => ['nullable', 'integer', 'in:0,1'],
            'labelLimits' => ['nullable', 'string', 'max:255'],
            'labelTestConformance' => ['nullable', 'string', 'max:255'],
            'showStandardsBelowLimits' => ['nullable', 'boolean'],
            'standardsToShow' => ['nullable', 'array'],
            'standardsToShow.*' => ['integer'],
        ], [], [
            'showLimits' => 'show limits',
            'showLod' => 'show LOD',
            'showTestConformance' => 'show test conformance',
            'showGrade' => 'show grade',
            'labelLimits' => 'limits label',
            'labelTestConformance' => 'test conformance label',
        ]);

        $customer = $this->customer;

        $reportConfig = [];
        foreach (['show_limits' => 'showLimits', 'show_lod' => 'showLod', 'show_test_conformance' => 'showTestConformance', 'show_grade' => 'showGrade'] as $key => $prop) {
            $val = $this->{$prop} ?? null;
            if ($val !== null && $val !== '') {
                $reportConfig[$key] = (bool) (int) $val;
            }
        }
        $reportConfig['label_limits'] = $this->labelLimits ?: null;
        $reportConfig['label_test_conformance'] = $this->labelTestConformance ?: null;
        $reportConfig['show_standards_below_limits'] = $this->showStandardsBelowLimits;
        $reportConfig['standards_to_show'] = is_array($this->standardsToShow)
            ? array_map('intval', array_filter($this->standardsToShow))
            : [];

        $customer->report_columns_config = $reportConfig !== [] ? $reportConfig : null;
        $customer->save();

        $parsed = [];
        foreach ($infoColumnsRaw as $raw) {
            $parts = explode(':', $raw, 3);
            if (count($parts) >= 2) {
                $parsed[] = [
                    'source' => $parts[0],
                    'source_key' => $parts[1],
                    'display_label' => $parts[2] ?? null,
                ];
            }
        }

        CRMCustomerReportInfoColumn::where('crm_customer_id', $customer->id)->delete();
        foreach ($parsed as $idx => $item) {
            CRMCustomerReportInfoColumn::create([
                'crm_customer_id' => $customer->id,
                'source' => $item['source'],
                'source_key' => $item['source_key'],
                'display_label' => $item['display_label'] ?: null,
                'display_order' => $idx,
            ]);
        }

        $this->showSuccess('Configurations saved.');
    }

    public function getStandardsProperty()
    {
        return Standards::where('status', 1)->orderBy('code')->get();
    }

    public function placeholder(): string
    {
        return '<div class="d-flex justify-content-center align-items-center p-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>';
    }

    public function render()
    {
        return view('livewire.crm.customer.tabs.configurations-manager');
    }
}
*/

/** Stub: Configurations tab disabled. Uncomment block above to restore. */
class ConfigurationsManager extends BaseCrmComponent
{
    public CRMCustomer $customer;

    public function mount(CRMCustomer $customer): void
    {
        $this->customer = $customer;
    }

    public function render()
    {
        return view('livewire.crm.customer.tabs.configurations-manager');
    }
}
