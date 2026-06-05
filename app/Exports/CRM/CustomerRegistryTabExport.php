<?php

namespace App\Exports\CRM;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\Complaint;
use App\Models\CRM\CustomerFeedback;
use App\Models\CRM\CustomerCertification;
use App\Models\CRM\CrmCustomerAttachment;
use App\Models\CRM\CompanyProduct;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\SamplePoint;
use App\SampleHeader;

class CustomerRegistryTabExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $customerId;
    protected $tabType;
    protected $search;

    public function __construct($customerId, $tabType, $search = '')
    {
        $this->customerId = $customerId;
        $this->tabType = $tabType;
        $this->search = $search;
    }

    public function query()
    {
        switch ($this->tabType) {
            case 'contacts':
                return CustomerContact::query()
                    ->where('crm_customer_id', $this->customerId)
                    ->when($this->search, function($query) {
                        $query->where(function($q) {
                            $q->where('first_name', 'like', '%' . $this->search . '%')
                              ->orWhere('last_name', 'like', '%' . $this->search . '%')
                              ->orWhere('email', 'like', '%' . $this->search . '%');
                        });
                    });

            case 'units':
                return CRMCompanyUnit::query()
                    ->where('crm_customer_id', $this->customerId)
                    ->when($this->search, function($query) {
                        $query->where('name', 'like', '%' . $this->search . '%');
                    });

            case 'complaints':
                return Complaint::query()
                    ->where('client_id', $this->customerId)
                    ->when($this->search, function($query) {
                        $query->where('complaint_id', 'like', '%' . $this->search . '%')
                              ->orWhere('complaint_title', 'like', '%' . $this->search . '%');
                    });

            case 'feedbacks':
                return CustomerFeedback::query()
                    ->where('customer_id', $this->customerId)
                    ->when($this->search, function($query) {
                        $term = '%' . $this->search . '%';
                        $query->where(function($q) use ($term) {
                            $q->where('code', 'like', $term)
                              ->orWhere('service_reference_no', 'like', $term)
                              ->orWhere('iso_concerns_description', 'like', $term)
                              ->orWhere('suggestions', 'like', $term)
                              ->orWhere('issue_description', 'like', $term)
                              ->orWhere('received_from', 'like', $term);
                        });
                    })
                    ->orderBy('id', 'desc');

            case 'certifications':
                return CustomerCertification::query()
                    ->where('customer_id', $this->customerId)
                    ->where('status', 0)
                    ->when($this->search, function($query) {
                        $query->where('certification_body', 'like', '%' . $this->search . '%');
                    });

            case 'documents':
                return CrmCustomerAttachment::query()
                    ->where('crm_customer_id', $this->customerId)
                    ->where('is_delete', '!=', 1)
                    ->when($this->search, function ($query) {
                        $query->where(function ($q) {
                            $q->where('title', 'like', '%' . $this->search . '%')
                                ->orWhere('type', 'like', '%' . $this->search . '%')
                                ->orWhere('description', 'like', '%' . $this->search . '%');
                        });
                    })
                    ->orderBy('created_at', 'desc');

            case 'products':
                return CompanyProduct::query()
                    ->whereHas('unit', function ($query) {
                        $query->where('crm_customer_id', $this->customerId);
                    })
                    ->when($this->search, function($query) {
                        $query->where('name', 'like', '%' . $this->search . '%');
                    });

            case 'orders':
                return SampleHeader::query()
                    ->join('sample_types as st', 'st.id', '=', 'sample_headers.sample_type_id')
                    ->select('sample_headers.*', 'st.name as sample_type')
                    ->withCount('samples')
                    ->where('sample_headers.crm_customer_id', $this->customerId)
                    ->whereNotIn('sample_headers.status', ['Completed'])
                    ->when($this->search, function($query) {
                        $query->where(function($q) {
                            $q->where('sample_headers.batch_code', 'like', '%' . $this->search . '%')
                              ->orWhere('sample_headers.reference_number', 'like', '%' . $this->search . '%')
                              ->orWhere('sample_headers.document_number', 'like', '%' . $this->search . '%');
                        });
                    })
                    ->orderBy('sample_headers.id', 'desc');

            case 'reports':
                return SampleHeader::query()
                    ->join('sample_types as st', 'st.id', '=', 'sample_headers.sample_type_id')
                    ->select('sample_headers.*', 'st.name as sample_type')
                    ->where('sample_headers.crm_customer_id', $this->customerId)
                    ->where('sample_headers.status', 'Completed')
                    ->when($this->search, function($query) {
                        $query->where(function($q) {
                            $q->where('sample_headers.batch_code', 'like', '%' . $this->search . '%')
                              ->orWhere('sample_headers.reference_number', 'like', '%' . $this->search . '%')
                              ->orWhere('sample_headers.description', 'like', '%' . $this->search . '%');
                        });
                    })
                    ->orderBy('sample_headers.id', 'desc');

            case 'sample_points':
                return SamplePoint::query()
                    ->whereHas('unit', function ($query) {
                        $query->where('crm_customer_id', $this->customerId);
                    })
                    ->when($this->search, function($query) {
                        $query->where('name', 'like', '%' . $this->search . '%');
                    });

            default:
                // Fallback to empty query if tab type is unknown
                return CustomerContact::query()->whereRaw('1 = 0');
        }
    }

    public function headings(): array
    {
        switch ($this->tabType) {
            case 'contacts':
                return ['First Name', 'Last Name', 'Email', 'Telephone', 'Job Title', 'Active'];
            case 'units':
                return ['Unit Name', 'Active', 'Created At'];
            case 'complaints':
                return ['Complaint ID', 'Title', 'Type', 'Stage', 'Date Received'];
            case 'feedbacks':
                return ['Reference', 'Comments', 'Rating', 'Date Received'];
            case 'certifications':
                return ['Certification Body', 'Certification Name', 'Description', 'Expiry Date'];
            case 'documents':
                return ['Title', 'Type', 'Description', 'Posted By', 'File Path', 'Uploaded At'];
            case 'products':
                return ['Product Name', 'Unit', 'Active'];
            case 'orders':
                return ['Report Number', 'Date Collected', 'Reference No', 'Document No', 'Sample Analysis', 'Sample Count', 'Status'];
            case 'reports':
                return ['Report Number', 'Client Unit', 'Ref. No.', 'Sample Analysis', 'Reason', 'Lab Date', 'Collected Date', 'Description'];
            case 'sample_points':
                return ['Sample Point Name', 'Unit', 'Active'];
            default:
                return ['Data'];
        }
    }

    public function map($row): array
    {
        switch ($this->tabType) {
            case 'contacts':
                return [
                    $row->first_name,
                    $row->last_name,
                    $row->email,
                    $row->telephone,
                    $row->job_occupation,
                    $row->active == 1 ? 'Yes' : 'No'
                ];
            case 'units':
                return [
                    $row->name,
                    $row->active == 1 ? 'Yes' : 'No',
                    $row->created_at->format('Y-m-d')
                ];
            case 'complaints':
                return [
                    $row->complaint_id,
                    $row->complaint_title,
                    $row->complaint_type,
                    $row->stage,
                    $row->date_received
                ];
            case 'feedbacks':
                return [
                    $row->code ?? ('FB' . str_pad($row->id, 4, '0', STR_PAD_LEFT)),
                    $row->suggestions ?? $row->issue_description ?? '',
                    $row->rating_overall ?? '',
                    $row->created_at ? $row->created_at->format('Y-m-d') : ''
                ];
            case 'certifications':
                return [
                    $row->certification_body,
                    $row->name, // Appended attribute
                    $row->description, // Appended attribute
                    $row->expiry_date
                ];
            case 'documents':
                return [
                    $row->title,
                    $row->type,
                    $row->description ?? '',
                    $row->posted_by,
                    $row->file_path,
                    $row->created_at ? $row->created_at->format('Y-m-d H:i') : '',
                ];
            case 'products':
                return [
                    $row->name,
                    $row->unit->name ?? 'N/A',
                    $row->active == 1 ? 'Yes' : 'No'
                ];
            case 'orders':
                return [
                    $row->batch_code,
                    $row->date_collected ?? '',
                    $row->reference_number ?? '-',
                    $row->document_number ?? '-',
                    $row->sample_type ?? '-',
                    $row->samples_count ?? 0,
                    $row->status ?? ''
                ];
            case 'reports':
                $reasonIds = array_filter(explode(',', $row->reason_for_submission ?? ''));
                $reasons = $reasonIds ? \App\RequestType::whereIn('id', $reasonIds)->pluck('name')->implode(', ') : '-';
                return [
                    $row->batch_code,
                    $row->crm_unit_name ?? '-',
                    $row->reference_number ?? '-',
                    $row->sample_type ?? '-',
                    $reasons,
                    $row->created_at ? $row->created_at->format('Y-m-d') : '',
                    $row->date_collected ?? '',
                    $row->description ?? '-'
                ];
            case 'sample_points':
                return [
                    $row->name,
                    $row->unit->name ?? 'N/A',
                    $row->active == 1 ? 'Yes' : 'No'
                ];
            default:
                return [$row->id];
        }
    }
}
