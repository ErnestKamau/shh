<?php

namespace App\Exports;

use App\Models\SamplingSchedule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class SamplingSchedulesExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    protected $schedules;

    public function __construct($schedules)
    {
        $this->schedules = $schedules;
    }

    public function collection()
    {
        return $this->schedules;
    }

    public function headings(): array
    {
        return [
            '#',
            'Title',
            'Client',
            'Contact',
            'Sampling Date',
            'Sampling Time',
            'Location',
            'Sample Type',
            'Analysis Type',
            'Parameters',
            'No. of Samples',
            'Frequency',
            'Personnel',
            'Notify Client',
            'Description',
        ];
    }

    public function map($schedule): array
    {
        // Build sample details string
        $sampleDetailsList = [];
        if (!empty($schedule->sample_details) && is_array($schedule->sample_details)) {
            foreach ($schedule->sample_details as $entry) {
                $st = \App\SampleType::find($entry['sample_type_id'] ?? null);
                $at = \App\AnalysisType::find($entry['analysis_type_id'] ?? null);
                $parameterIds = $entry['parameters'] ?? [];
                $paramNames = [];
                if (!empty($parameterIds)) {
                    $paramNames = \App\Analyte::whereIn('id', $parameterIds)->pluck('name')->toArray();
                }
                if ($st) {
                    $sampleDetailsList[] = [
                        'sample_type' => $st->name,
                        'analysis_type' => $at ? $at->name : '',
                        'parameters' => implode(', ', $paramNames),
                    ];
                }
            }
        } elseif ($schedule->sample_type) {
            $parameterIds = $schedule->parameters ?? [];
            $paramNames = [];
            if (!empty($parameterIds)) {
                $paramNames = \App\Analyte::whereIn('id', $parameterIds)->pluck('name')->toArray();
            }
            $sampleDetailsList[] = [
                'sample_type' => $schedule->sample_type->name,
                'analysis_type' => $schedule->analysis_type ? $schedule->analysis_type->name : '',
                'parameters' => implode(', ', $paramNames),
            ];
        }

        $sampleTypes = implode('; ', array_column($sampleDetailsList, 'sample_type'));
        $analysisTypes = implode('; ', array_filter(array_column($sampleDetailsList, 'analysis_type')));
        $allParameters = implode('; ', array_filter(array_column($sampleDetailsList, 'parameters')));

        return [
            '',  // Row number will be added in collection or can be handled differently
            $schedule->title,
            $schedule->client->name ?? 'N/A',
            $schedule->contact ? trim(($schedule->contact->first_name ?? '') . ' ' . ($schedule->contact->last_name ?? '')) : 'N/A',
            $schedule->sampling_datetime ? $schedule->sampling_datetime->format('Y-m-d') : 'N/A',
            $schedule->sampling_datetime ? $schedule->sampling_datetime->format('H:i') : 'N/A',
            $schedule->location ?? 'N/A',
            $sampleTypes ?: 'N/A',
            $analysisTypes ?: 'N/A',
            $allParameters ?: 'N/A',
            $schedule->number_of_samples ?? 1,
            $schedule->frequency ?? 'One-time',
            $schedule->personnel->name ?? 'N/A',
            $schedule->notify_client ? 'Yes' : 'No',
            $schedule->description ?? '',
        ];
    }
}
