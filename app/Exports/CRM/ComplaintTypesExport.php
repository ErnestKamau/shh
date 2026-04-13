<?php

namespace App\Exports\CRM;

use App\Models\CRM\Complaint_Type;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ComplaintTypesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Complaint_Type::query();

        if (($this->filters['activeTab'] ?? '') === 'active') {
            $query->where(function ($q) {
                $q->where('status', 1)->orWhere('status', 'active');
            });
        } elseif (($this->filters['activeTab'] ?? '') === 'archived') {
            $query->where(function ($q) {
                $q->where('status', 0)->orWhereIn('status', ['archive', 'archived']);
            });
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        return $query->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Name',
            'Description',
            'Status',
            'Created At',
        ];
    }

    public function map($type): array
    {
        $isActive = in_array($type->status, [1, '1', 'active'], true);
        return [
            $type->name,
            $type->description,
            $isActive ? 'Active' : 'Archived',
            $type->created_at ? $type->created_at->format('Y-m-d H:i') : '',
        ];
    }
}
