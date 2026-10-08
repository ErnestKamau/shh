<?php

namespace App\Livewire\Lab;

use App\Services\Sampleworkflow\AmendmentReportConfigurationService;
use Livewire\Component;

class AmendmentReportConfigurationManager extends Component
{
    public string $amendment_revision_format = '';

    public string $amendment_revision_label = '';

    public string $amendment_reason_label = '';

    public string $amendment_supersedes_text = '';

    public string $amendment_report_number_format = '';

    public string $amendment_sample_number_suffix_format = '';

    public string $message = '';

    public string $messageType = '';

    public int $previewVersion = 2;

    public string $previewJobNumber = '260428001';

    public function mount(AmendmentReportConfigurationService $config): void
    {
        $this->fillFromConfig($config);
    }

    public function save(AmendmentReportConfigurationService $config): void
    {
        $this->validate([
            'amendment_revision_format' => ['required', 'string', 'max:50'],
            'amendment_revision_label' => ['required', 'string', 'max:100'],
            'amendment_reason_label' => ['required', 'string', 'max:100'],
            'amendment_supersedes_text' => ['required', 'string', 'max:500'],
            'amendment_report_number_format' => ['required', 'string', 'max:100'],
            'amendment_sample_number_suffix_format' => ['nullable', 'string', 'max:50'],
            'previewVersion' => ['required', 'integer', 'min:1', 'max:99'],
            'previewJobNumber' => ['required', 'string', 'max:50'],
        ]);

        $config->save([
            AmendmentReportConfigurationService::KEY_REVISION_FORMAT => $this->amendment_revision_format,
            AmendmentReportConfigurationService::KEY_REVISION_LABEL => $this->amendment_revision_label,
            AmendmentReportConfigurationService::KEY_REASON_LABEL => $this->amendment_reason_label,
            AmendmentReportConfigurationService::KEY_SUPERSEDES_TEXT => $this->amendment_supersedes_text,
            AmendmentReportConfigurationService::KEY_REPORT_NUMBER_FORMAT => $this->amendment_report_number_format,
            AmendmentReportConfigurationService::KEY_SAMPLE_NUMBER_SUFFIX_FORMAT => $this->amendment_sample_number_suffix_format,
        ]);

        $this->fillFromConfig($config);
        $this->message = 'Amendment report configuration saved.';
        $this->messageType = 'success';
    }

    public function resetToDefaults(AmendmentReportConfigurationService $config): void
    {
        $config->save(AmendmentReportConfigurationService::defaults());
        $this->fillFromConfig($config);
        $this->message = 'Amendment report configuration reset to defaults.';
        $this->messageType = 'success';
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function getPreviewRevisionProperty(): string
    {
        return $this->previewFormat(
            $this->amendment_revision_format !== ''
                ? $this->amendment_revision_format
                : AmendmentReportConfigurationService::defaults()[AmendmentReportConfigurationService::KEY_REVISION_FORMAT],
            (int) $this->previewVersion
        );
    }

    public function getPreviewReportNumberProperty(): string
    {
        $format = $this->amendment_report_number_format !== ''
            ? $this->amendment_report_number_format
            : AmendmentReportConfigurationService::defaults()[AmendmentReportConfigurationService::KEY_REPORT_NUMBER_FORMAT];

        return $this->previewFormat($format, (int) $this->previewVersion, $this->previewJobNumber);
    }

    public function getPreviewSampleSuffixProperty(): string
    {
        $format = trim($this->amendment_sample_number_suffix_format);
        if ($format === '') {
            return '(none)';
        }

        return $this->previewFormat($format, (int) $this->previewVersion);
    }

    public function render()
    {
        return view('livewire.lab.amendment-report-configuration-manager');
    }

    private function fillFromConfig(AmendmentReportConfigurationService $config): void
    {
        $all = $config->all();
        $this->amendment_revision_format = $all[AmendmentReportConfigurationService::KEY_REVISION_FORMAT];
        $this->amendment_revision_label = $all[AmendmentReportConfigurationService::KEY_REVISION_LABEL];
        $this->amendment_reason_label = $all[AmendmentReportConfigurationService::KEY_REASON_LABEL];
        $this->amendment_supersedes_text = $all[AmendmentReportConfigurationService::KEY_SUPERSEDES_TEXT];
        $this->amendment_report_number_format = $all[AmendmentReportConfigurationService::KEY_REPORT_NUMBER_FORMAT];
        $this->amendment_sample_number_suffix_format = $all[AmendmentReportConfigurationService::KEY_SAMPLE_NUMBER_SUFFIX_FORMAT];
    }

    private function previewFormat(string $format, int $revision, ?string $jobNumber = null): string
    {
        $n = (string) max(1, $revision);
        $nn = str_pad($n, 2, '0', STR_PAD_LEFT);
        $replacements = [
            '{nn}' => $nn,
            '{n}' => $n,
            // Sample demo values so the preview reads naturally — real reports resolve these
            // from the batch's own samples / lab sections.
            '{samples}' => '001 - 045',
            '{sections}' => 'M, C',
        ];
        if ($jobNumber !== null) {
            $replacements['{job}'] = $jobNumber;
        }

        return str_replace(array_keys($replacements), array_values($replacements), $format);
    }
}
