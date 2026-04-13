<?php

namespace App\Exports\CRM;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FeedbackInsightsExport implements FromArray, WithHeadings
{
    protected $insights;
    protected $actionableInsights;

    public function __construct(array $insights, array $actionableInsights = [])
    {
        $this->insights = $insights;
        $this->actionableInsights = $actionableInsights;
    }

    public function headings(): array
    {
        return [];
    }

    public function array(): array
    {
        $pulse = $this->insights['pulse'] ?? [];
        $rows = [
            ['Feedback Insights Export', now()->format('d M Y H:i')],
            [''],
            ['Summary'],
            ['Total Feedback', $pulse['total'] ?? 0],
            ['Recommendation Rate %', ($pulse['nps_percent'] ?? 0) . '%'],
            ['ISO Risk Count', $pulse['risk_count'] ?? 0],
            [''],
            ['Actionable Insights'],
        ];
        foreach ($this->actionableInsights as $i) {
            $rows[] = [$i['message']];
        }
        $rows[] = [''];
        $rows[] = ['Service Quality Spectrum', 'Average', 'Total', 'Excellent', 'Good', 'Fair', 'Poor'];
        foreach ($this->insights['ratings'] ?? [] as $key => $data) {
            $seg = $data['segments'] ?? [];
            $rows[] = [
                ucfirst($key),
                $data['average'] ?? 0,
                $data['total'] ?? 0,
                $seg['excellent']['count'] ?? 0,
                $seg['good']['count'] ?? 0,
                $seg['fair']['count'] ?? 0,
                $seg['poor']['count'] ?? 0,
            ];
        }
        $rows[] = [''];
        $rows[] = ['Service Type', 'Count', 'Avg Rating', 'NPS %'];
        foreach ($this->insights['service_types'] ?? [] as $type => $data) {
            $rows[] = [
                ($type === '__pending__' || $type === '' || $type === null) ? 'Unknown (Pending)' : $type,
                $data['count'] ?? 0,
                $data['avg_rating'] ?? '-',
                isset($data['nps_percent']) ? ($data['nps_percent'] . '%') : '-',
            ];
        }
        return $rows;
    }
}
