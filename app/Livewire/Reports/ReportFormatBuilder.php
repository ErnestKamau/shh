<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\ReportFormat;
use App\ReportFormatSection;
use App\ReportFormatDetail;
use Illuminate\Support\Facades\DB;

class ReportFormatBuilder extends Component
{
    public $reportFormat;
    public $reportFormatId;

    // Configurable Settings
    public $resultsDisplayType = 'grid'; // Default

    // Details
    public $details = [];
    public $availableDetailsKeys = [
        'header_disclaimer' => 'Header Disclaimer',
        'methodology_statement' => 'Methodology Statement',
        'footer_disclaimer' => 'Footer Disclaimer'
    ];

    // Sections
    public $sections = [];
    public $availableSections = [
        'Header' => 'Header',
        'ReportTitle' => 'Report Title',
        'SampleInfo' => 'Sample Information',
        'TestMethods' => 'Test Methods',
        'Results' => 'Results',
        'Methodology' => 'Methodology Statement',
        'Signatures' => 'Signatures/Approvals',
        'Footer' => 'Footer'
    ];

    // UI State
    public $message = '';
    public $messageType = '';

    protected $rules = [
        'resultsDisplayType' => 'required|string',
        'details.*' => 'nullable|string',
        'sections.*.is_visible' => 'boolean',
        'sections.*.custom_title' => 'nullable|string|max:255',
        'sections.*.order' => 'required|integer',
    ];

    public function mount($reportFormatId)
    {
        $this->reportFormatId = $reportFormatId;
        $this->loadReportFormat();
    }

    public function loadReportFormat()
    {
        $this->reportFormat = ReportFormat::with(['sections', 'details'])->findOrFail($this->reportFormatId);

        $this->resultsDisplayType = $this->reportFormat->results_display_type ?? 'grid';

        // Load existing details
        foreach ($this->availableDetailsKeys as $key => $label) {
            $this->details[$key] = $this->reportFormat->getDetail($key);
        }

        // Load existing sections or initialize them
        $existingSections = $this->reportFormat->sections->keyBy('section_name');

        $order = 0;
        foreach ($this->availableSections as $name => $label) {
            if ($existingSections->has($name)) {
                $section = $existingSections->get($name);
                $this->sections[$name] = [
                    'is_visible' => $section->is_visible,
                    'custom_title' => $section->custom_title,
                    'order' => $section->order,
                ];
            } else {
                $this->sections[$name] = [
                    'is_visible' => true,
                    'custom_title' => null,
                    'order' => $order,
                ];
            }
            $order++;
        }

        // Sort sections array by order
        uasort($this->sections, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });
    }

    public function moveSectionUp($sectionName)
    {
        $currentOrder = $this->sections[$sectionName]['order'];
        if ($currentOrder > 0) {
            // Find section just above and swap
            foreach ($this->sections as $name => $data) {
                if ($data['order'] == $currentOrder - 1) {
                    $this->sections[$name]['order'] = $currentOrder;
                    $this->sections[$sectionName]['order'] = $currentOrder - 1;
                    break;
                }
            }
        }
        $this->sortSections();
    }

    public function moveSectionDown($sectionName)
    {
        $currentOrder = $this->sections[$sectionName]['order'];
        if ($currentOrder < count($this->sections) - 1) {
            // Find section just below and swap
            foreach ($this->sections as $name => $data) {
                if ($data['order'] == $currentOrder + 1) {
                    $this->sections[$name]['order'] = $currentOrder;
                    $this->sections[$sectionName]['order'] = $currentOrder + 1;
                    break;
                }
            }
        }
        $this->sortSections();
    }

    private function sortSections()
    {
        uasort($this->sections, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });
    }

    public function saveConfiguration()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // Update Report Format generic config
            $this->reportFormat->update([
                'results_display_type' => $this->resultsDisplayType
            ]);

            // Save details
            foreach ($this->details as $key => $value) {
                if ($value) {
                    ReportFormatDetail::updateOrCreate(
                        ['report_format_id' => $this->reportFormat->id, 'key_name' => $key],
                        ['text_value' => $value]
                    );
                } else {
                    ReportFormatDetail::where('report_format_id', $this->reportFormat->id)
                        ->where('key_name', $key)
                        ->delete();
                }
            }

            // Save sections
            foreach ($this->sections as $name => $data) {
                ReportFormatSection::updateOrCreate(
                    ['report_format_id' => $this->reportFormat->id, 'section_name' => $name],
                    [
                        'order' => $data['order'],
                        'is_visible' => $data['is_visible'],
                        'custom_title' => $data['custom_title']
                    ]
                );
            }

            DB::commit();

            $this->message = 'Report configuration saved successfully.';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error saving configuration: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.reports.report-format-builder');
    }
}
