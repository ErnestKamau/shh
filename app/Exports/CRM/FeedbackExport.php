<?php

namespace App\Exports\CRM;

use App\Models\CRM\CustomerFeedback;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FeedbackExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = CustomerFeedback::query()
            ->with(['customer', 'contact']);

        if ($this->filters['search']) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                    ->orWhere('service_reference_no', 'like', '%' . $search . '%')
                    ->orWhere('iso_concerns_description', 'like', '%' . $search . '%');
            });
        }

        if ($this->filters['activeTab'] == 'submitted') {
            $query->where('status', CustomerFeedback::STATUS_SUBMITTED);
        } elseif ($this->filters['activeTab'] == 'pending') {
            $query->where('status', CustomerFeedback::STATUS_PENDING);
        }

        if ($this->filters['showRisksOnly']) {
            $query->where(function($q) {
                $q->where('iso_impartiality', 'No')
                  ->orWhere('iso_confidentiality', 'No');
            });
        }

        return $query->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return [
            'Code',
            'Customer',
            'Contact',
            'Service Type',
            'Service Ref',
            'Overall Rating',
            'Communication',
            'Turnaround',
            'Technical',
            'Accuracy',
            'Status',
            'Date Submitted',
        ];
    }

    public function map($feedback): array
    {
        return [
            $feedback->code ?? ('FB' . str_pad($feedback->id, 4, '0', STR_PAD_LEFT)),
            $feedback->customer->name ?? $feedback->received_from,
            $feedback->contact->name ?? $feedback->registered_by,
            $feedback->service_type,
            $feedback->service_reference_no,
            $feedback->rating_overall,
            $feedback->rating_communication,
            $feedback->rating_turnaround,
            $feedback->rating_technical,
            $feedback->rating_accuracy,
            $feedback->status == CustomerFeedback::STATUS_SUBMITTED ? 'Submitted' : 'Pending',
            $feedback->created_at ? $feedback->created_at->format('Y-m-d') : '',
        ];
    }
}
