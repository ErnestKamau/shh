<?php

namespace App\Livewire\Batch\Tabs;

use App\SampleHeader;
use App\Services\Sampleworkflow\BatchWorkflowStageSyncService;
use Livewire\Component;
use Livewire\WithPagination;

class ChainOfCustody extends Component
{
    use WithPagination;

    public SampleHeader $batch;
    public string $search = '';
    public int $perPage = 10;

    protected $listeners = ['custodyUpdated' => '$refresh'];
    protected $paginationTheme = 'bootstrap';

    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;

        // Heal rows skipped when workflow status changed without CoC (e.g. Livewire verification).
        if (app(BatchWorkflowStageSyncService::class)->ensureOpenCustodyMatchesWorkflow($this->batch)) {
            $this->batch->refresh();
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function getCustodyRecordsProperty()
    {
        $events = collect();

        $batch = $this->batch;
        $instance = $batch->submissionFormInstance;

        // 1. Audit Logs from instance (Portal actions, API actions)
        if ($instance) {
            $auditLogs = $instance->auditLogs()->with('user')->latest()->get();
            foreach ($auditLogs as $log) {
                // Determine user name. If user is null, it might be a portal customer.
                $userName = $log->user ? $log->user->name : 'Customer (Portal)';
                
                $title = method_exists($log, 'getActionDisplayName') ? $log->getActionDisplayName() : ucfirst($log->event);
                
                // Portal submission specific check
                if ($log->event === 'updated' && isset($log->new_values['status']) && $log->new_values['status'] === 'submitted') {
                    $title = 'Request Submitted (Portal)';
                } elseif ($log->event === 'created') {
                    $title = 'Request Drafted (Portal)';
                }

                // Filter by search term
                $searchable = strtolower($title . ' ' . $userName);
                if ($this->search && !str_contains($searchable, strtolower($this->search))) {
                    continue;
                }

                $badge = method_exists($log, 'getActionBadgeColor') ? $log->getActionBadgeColor() : ($log->event === 'created' ? 'info' : 'success');

                $events->push((object) [
                    'source' => 'audit',
                    'title' => $title,
                    'subtitle' => 'Status: ' . ($log->new_values['status'] ?? $instance->status),
                    'user_name' => $userName,
                    'occurred_at' => \Carbon\Carbon::parse($log->created_at),
                    'badge' => $badge,
                    'comment' => $log->notes ?? null,
                ]);
            }

            // 2. Intrays from instance
            $intrays = $instance->intrays()
                ->with(['fromUser', 'toUser', 'assignedBy', 'completedByUser'])
                ->orderByDesc('created_at')
                ->get();

            foreach ($intrays as $intray) {
                $userName = $intray->assignedBy ? $intray->assignedBy->name : 'System';
                $title = $intray->status === 'completed' ? 'Intray completed' : 'Intray assigned';
                
                $subtitle = '';
                if ($intray->toUser) {
                    $subtitle = $intray->fromUser
                        ? 'From '.$intray->fromUser->name.' → '.$intray->toUser->name
                        : 'Assigned to '.$intray->toUser->name;
                }

                $searchable = strtolower($title . ' ' . $subtitle . ' ' . $userName . ' ' . $intray->comment);
                if ($this->search && !str_contains($searchable, strtolower($this->search))) {
                    continue;
                }

                $events->push((object) [
                    'source' => 'intray',
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'user_name' => $userName,
                    'occurred_at' => \Carbon\Carbon::parse($intray->completed_at ?? $intray->created_at),
                    'badge' => $intray->status === 'completed' ? 'success' : 'warning',
                    'comment' => $intray->comment,
                ]);
            }
        }

        // 3. Batch Custody records
        $custodyRecords = \App\ChainOfCustody::query()
            ->with(['started_by', 'completed_by', 'tracking_stage'])
            ->where('sample_header_id', $batch->id)
            ->orderByDesc('created_at')
            ->get();

        foreach ($custodyRecords as $custody) {
            $userName = $custody->started_by ? $custody->started_by->name : 'System';
            $title = $custody->workflow_stage ?? 'Batch custody';
            $subtitle = 'Tracking Stage: ' . ($custody->tracking_stage->name ?? 'N/A');

            $searchable = strtolower($title . ' ' . $subtitle . ' ' . $userName . ' ' . $custody->comments);
            if ($this->search && !str_contains($searchable, strtolower($this->search))) {
                continue;
            }

            $events->push((object) [
                'source' => 'batch',
                'title' => $title,
                'subtitle' => $subtitle,
                'user_name' => $userName,
                'occurred_at' => \Carbon\Carbon::parse($custody->created_at),
                'badge' => 'primary',
                'comment' => $custody->comments,
                'is_completed' => !empty($custody->moved_out_date),
                'completed_by' => $custody->completed_by ? $custody->completed_by->name : 'System',
                'completed_at' => $custody->moved_out_date,
            ]);
        }

        // Sort by occurred_at desc
        $sorted = $events->sortByDesc(function ($event) {
            return $event->occurred_at;
        })->values();

        // Paginate manually using collection
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $perPage = $this->perPage;
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $sorted->forPage($currentPage, $perPage),
            $sorted->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.tabs.chain-of-custody', [
            'custodyRecords' => $this->custodyRecords
        ]);
    }
}
