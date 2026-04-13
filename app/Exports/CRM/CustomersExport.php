<?php

namespace App\Exports\CRM;

use App\Models\CRM\CRMCustomer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = CRMCustomer::query()
            ->where('company_id', $this->filters['company_id'])
            ->with('country');

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($this->filters['activeFilter'] !== '') {
            $query->where('active', $this->filters['activeFilter']);
        }

        if (!empty($this->filters['startDate'])) {
            $query->where('created_at', '>=', $this->filters['startDate']);
        }

        if (!empty($this->filters['endDate'])) {
            $query->where('created_at', '<=', $this->filters['endDate'] . ' 23:59:59');
        }

        if (!empty($this->filters['selectedCustomers'])) {
            $query->whereIn('id', $this->filters['selectedCustomers']);
        }

        return $query->orderBy($this->filters['sortField'], $this->filters['sortDirection']);
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Email',
            'Phone 1',
            'Phone 2',
            'Physical Address',
            'Postal Address',
            'Country',
            'Website',
            'Fax',
            'Status',
            'Created At',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->code,
            $customer->name,
            $customer->email,
            $customer->phone1,
            $customer->phone2,
            $customer->physical_address,
            $customer->postal_address,
            $customer->country->name ?? 'N/A',
            $customer->website,
            $customer->fax,
            $customer->active ? 'Active' : 'Inactive',
            $customer->created_at ? $customer->created_at->format('Y-m-d H:i') : '',
        ];
    }
}
