<?php

namespace App\Exports\CRM;

use App\Models\CRM\Complaint;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ComplaintsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Complaint::query()
            ->with(['client']);

        $stage_map = [
            'All Complaints' => 0,
            'Log & Intake' => 1,
            'Active Investigations' => 2,
            'Verification Review & CAPA' => 3,
            'Pending Closure' => 4,
            'Closed' => 5,
            'Cancelled' => 6
        ];

        $stage_value = 0;
        if (is_numeric($this->filters['stage'])) {
            $stage_value = $this->filters['stage'];
        } elseif (is_string($this->filters['stage'])) {
            $decoded_name = urldecode($this->filters['stage']);
            if (isset($stage_map[$decoded_name])) {
                $stage_value = $stage_map[$decoded_name];
            }
        }

        if ($stage_value > 0) {
            $query->where('complaint_workflow', $stage_value);
        } else {
            if ($this->filters['activeTab'] == 'approved') {
                $query->where('complaint_workflow', '!=', 6);
            } elseif ($this->filters['activeTab'] == 'rejected') {
                $query->where('complaint_workflow', 6);
            }
        }

        if ($this->filters['search']) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('complaint_id', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('received_from', 'like', '%' . $search . '%');
            });
        }

        if ($this->filters['typeFilter']) {
            $query->where('type', $this->filters['typeFilter']);
        }

        if ($this->filters['priorityFilter']) {
            $query->where('priority', $this->filters['priorityFilter']);
        }

        return $query->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return [
            'Complaint ID',
            'Organization / Customer',
            'Contact Person',
            'Title / Position',
            'Type',
            'Priority',
            'Stage',
            'Test Item',
            'Report Serial No',
            'Date Received',
            'Registered By',
            'Created At',
        ];
    }

    public function map($complaint): array
    {
        $stageName = getComplaintWorkflow()[$complaint->complaint_workflow] ?? (string) $complaint->complaint_workflow;

        return [
            $complaint->complaint_id,
            $complaint->organization_name ?? $complaint->received_from,
            $complaint->contact_name,
            $complaint->title_position,
            $complaint->type,
            ucfirst($complaint->priority),
            $stageName,
            $complaint->test_item ?? $complaint->test_item_report_serial_no,
            $complaint->report_serial_no,
            $complaint->date ? $complaint->date->format('Y-m-d') : '',
            $complaint->registered_by,
            $complaint->created_at ? $complaint->created_at->format('Y-m-d H:i') : '',
        ];
    }
}
