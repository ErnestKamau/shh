<?php

namespace App\Exports\CRM;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Models\CRM\Chain_of_Custody_Complaint;
use App\Models\CRM\Complaintnotes;
use App\Models\CRM\Complaintattachment;
use App\Models\CRM\Complaintsresolutions;

class ComplaintTabExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $complaintId;
    protected $tabType;

    public function __construct($complaintId, $tabType)
    {
        $this->complaintId = $complaintId;
        $this->tabType = $tabType;
    }

    public function query()
    {
        switch ($this->tabType) {
            case 'workflow':
                return Chain_of_Custody_Complaint::query()
                    ->where('complaint_id', $this->complaintId)
                    ->orderBy('id', 'desc');

            case 'notes':
                return Complaintnotes::query()
                    ->where('complaint_id', $this->complaintId)
                    ->where('is_delete', '!=', 1)
                    ->orderBy('id', 'desc');

            case 'attachments':
                return Complaintattachment::query()
                    ->where('complaint_id', $this->complaintId)
                    ->where('is_delete', '!=', 1)
                    ->orderBy('id', 'desc');

            case 'resolutions':
                return Complaintsresolutions::query()
                    ->where('complaint_id', $this->complaintId)
                    ->orderBy('id', 'desc');

            default:
                return Chain_of_Custody_Complaint::query()->whereRaw('1 = 0');
        }
    }

    public function headings(): array
    {
        switch ($this->tabType) {
            case 'workflow':
                return ['Action', 'Action Taker', 'Workflow Stage', 'Comments', 'Date'];
            case 'notes':
                return ['Note', 'Type', 'Public', 'Posted By', 'Date'];
            case 'attachments':
                return ['Title', 'Type', 'Description', 'Public', 'Posted By', 'Date'];
            case 'resolutions':
                return ['CAR No', 'Findings', 'Root Cause Analysis', 'Corrective Action', 'Preventive Action', 'Officer Responsible', 'Registered By', 'Date'];
            default:
                return ['Data'];
        }
    }

    public function map($row): array
    {
        switch ($this->tabType) {
            case 'workflow':
                return [
                    $row->action,
                    $row->actionTaker->name ?? 'N/A',
                    $row->workflow_stage,
                    $row->comments,
                    $row->created_at->format('Y-m-d H:i:s')
                ];
            case 'notes':
                return [
                    $row->notes,
                    $row->type,
                    $row->is_public ? 'Yes' : 'No',
                    $row->created_by,
                    $row->created_at->format('Y-m-d H:i:s')
                ];
            case 'attachments':
                return [
                    $row->title,
                    $row->type,
                    $row->description,
                    $row->is_public ? 'Yes' : 'No',
                    $row->posted_by,
                    $row->created_at->format('Y-m-d H:i:s')
                ];
            case 'resolutions':
                return [
                    $row->car_no,
                    strip_tags($row->findings ?? ''),
                    strip_tags($row->root_cause_analysis ?? ''),
                    strip_tags($row->corrective_action_taken ?? ''),
                    strip_tags($row->preventive_action ?? ''),
                    $row->officer_responsible,
                    $row->registered_by,
                    $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '',
                ];
            default:
                return [$row->id];
        }
    }
}
