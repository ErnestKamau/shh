<?php

namespace App\Repositories\Registry;

use App\DTOs\Registry\RegistryFilterDTO;
use App\Models\Registry\RegistryRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class RegistryRequestRepository
{
    public function queryFiltered(RegistryFilterDTO $filter): Builder
    {
        $query = RegistryRequest::query()
            ->forCompany()
            ->with(['category', 'assignee', 'workflowDefinition']);

        if ($filter->status !== null) {
            $query->where('status', $filter->status);
        }

        if ($filter->categoryId !== null) {
            $query->where('request_category_id', $filter->categoryId);
        }

        if ($filter->direction !== null) {
            $query->where('direction', $filter->direction);
        }

        if ($filter->priority !== null) {
            $query->where('priority', $filter->priority);
        }

        if ($filter->assignedTo !== null) {
            $query->where('assigned_to', $filter->assignedTo);
        }

        if ($filter->search !== null && $filter->search !== '') {
            $search = '%' . $filter->search . '%';
            $query->where(function (Builder $q) use ($search) {
                $q->where('reference_no', 'like', $search)
                    ->orWhere('subject', 'like', $search)
                    ->orWhere('submitting_party', 'like', $search);
            });
        }

        if ($filter->startDate !== null) {
            $query->whereDate('created_at', '>=', $filter->startDate);
        }

        if ($filter->endDate !== null) {
            $query->whereDate('created_at', '<=', $filter->endDate);
        }

        return $query->orderByDesc('created_at');
    }

    public function paginate(RegistryFilterDTO $filter, int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryFiltered($filter)->paginate($perPage);
    }

    public function referenceExists(string $referenceNo, ?string $excludeId = null): bool
    {
        $query = RegistryRequest::query()->where('reference_no', $referenceNo);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function findOrFail(string $id): RegistryRequest
    {
        return RegistryRequest::query()
            ->forCompany()
            ->with([
                'category.workflowDefinition.steps',
                'assignee',
                'actions.performer',
                'assignments.assignee',
                'documents',
                'statusLogs',
            ])
            ->findOrFail($id);
    }
}
