@php
    $showRequesterColumn = $activeTab !== 'my_requests';
    $showReviewAction = $activeTab === 'submitted' && $this->canApprove();
    $colspan = $showRequesterColumn ? 7 : 6;
@endphp

<div class="table-responsive">
    <table class="table table-hover mb-0 workflow-table">
        <thead>
            <tr>
                @if($showRequesterColumn)
                    <th>Requester</th>
                @endif
                <th>Equipment</th>
                <th>{{ $activeTab === 'scheduled_today' ? 'Approved window' : 'Proposed window' }}</th>
                <th>Samples</th>
                <th>Status</th>
                <th>Submitted</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $request)
                <tr wire:key="request-{{ $activeTab }}-{{ $request->id }}">
                    @if($showRequesterColumn)
                        <td>{{ $request->requester?->name ?? '—' }}</td>
                    @endif
                    <td>
                        <strong>{{ $request->equipment?->name }}</strong>
                        @if($request->equipment?->equipment_number)
                            <br><small class="text-muted">{{ $request->equipment->equipment_number }}</small>
                        @endif
                    </td>
                    <td class="small">
                        @if($activeTab === 'scheduled_today')
                            {{ $request->approved_start_at?->format('Y-m-d H:i') }}
                            &ndash;
                            {{ $request->approved_end_at?->format('Y-m-d H:i') }}
                        @else
                            {{ $request->proposed_start_at?->format('Y-m-d H:i') }}
                            &ndash;
                            {{ $request->proposed_end_at?->format('Y-m-d H:i') }}
                        @endif
                    </td>
                    <td>{{ $request->sampleDetails->count() }}</td>
                    <td>@include('livewire.lab.equipment-requests.partials.status-badge', ['status' => $request->status])</td>
                    <td class="small">{{ $request->created_at?->format('Y-m-d H:i') }}</td>
                    <td class="text-center text-nowrap">
                        <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--view {{ $showReviewAction ? 'mr-1' : '' }}" wire:click="viewRequest('{{ $request->id }}')">
                            <i class="mdi mdi-eye"></i>@if(! $showReviewAction) View @endif
                        </button>
                        @if($showReviewAction)
                            <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--edit" wire:click="openApprovalModal('{{ $request->id }}')">
                                <i class="mdi mdi-check-decagram"></i> Review
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $colspan }}">
                        <div class="er-empty-state">
                            @if($activeTab === 'submitted')
                                <i class="mdi mdi-inbox-arrow-down d-block"></i>
                                <h6 class="text-muted">No submitted requests</h6>
                                <p class="small mb-0">There are no pending requests from other users in your zones.</p>
                            @elseif($activeTab === 'approved')
                                <i class="mdi mdi-check-circle-outline d-block"></i>
                                <h6 class="text-muted">No approved requests</h6>
                                <p class="small mb-0">Approved equipment usage requests will appear here.</p>
                            @elseif($activeTab === 'scheduled_today')
                                <i class="mdi mdi-calendar-today d-block"></i>
                                <h6 class="text-muted">Nothing scheduled today</h6>
                                <p class="small mb-0">Approved requests with a start date of today will appear here.</p>
                            @elseif($activeTab === 'rejected')
                                <i class="mdi mdi-close-circle-outline d-block"></i>
                                <h6 class="text-muted">No rejected requests</h6>
                                <p class="small mb-0">Rejected or cancelled requests in your zones will appear here.</p>
                            @else
                                <i class="mdi mdi-tools d-block"></i>
                                <h6 class="text-muted">No requests found</h6>
                                <p class="small mb-3">Submit a request to reserve equipment for your samples.</p>
                                @if($this->canAdd())
                                    <button type="button" class="btn btn-primary btn-action-sm" wire:click="openCreateModal">
                                        <i class="mdi mdi-plus"></i> New Request
                                    </button>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($requests->hasPages())
    <div class="p-3 border-top d-flex justify-content-center">
        {{ $requests->links('pagination::bootstrap-4') }}
    </div>
@endif
