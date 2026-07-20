<div class="equipment-requests-page">
<style>
    .equipment-requests-page {
        width: 100%;
        max-width: 100%;
    }
    .equipment-requests-page .stat-cards-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 16px;
        width: 100%;
        clear: both;
        margin-bottom: 1.5rem;
    }
    .equipment-requests-page .stat-card {
        min-width: 0;
        width: 100%;
    }
    .equipment-requests-page .workflow-board-header .batch-header-bar {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 14px 20px;
        margin-bottom: 0;
    }
    .equipment-requests-page .batch-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .equipment-requests-page .batch-title-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .equipment-requests-page .batch-code-label {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e293b;
    }
    .equipment-requests-page .batch-stage-pill {
        background: var(--color-primary-soft);
        color: var(--color-primary);
        border-radius: 20px;
        padding: 3px 12px;
        font-size: 0.78rem;
        font-weight: 600;
        border: 1px solid var(--color-primary-focus);
     }
    .equipment-requests-page .workflow-receiving-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 4px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        margin-bottom: 1rem;
    }
    .equipment-requests-page .workflow-receiving-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border: 1px solid transparent;
        border-radius: 8px;
        background: transparent;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease, color 0.15s ease;
    }
    .equipment-requests-page .workflow-receiving-tab:hover {
        background: #fff;
        border-color: var(--color-primary-border-soft);
        color: var(--color-primary);
    }
    .equipment-requests-page .workflow-receiving-tab.is-active {
        background: #fff;
        border-color: var(--color-primary);
        color: var(--color-primary);
        box-shadow: 0 1px 4px var(--color-primary-highlight);
    }
    .equipment-requests-page .workflow-receiving-tab-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.35rem;
        padding: 2px 7px;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        background: #e2e8f0;
        color: #475569;
    }
    .equipment-requests-page .workflow-receiving-tab.is-active .workflow-receiving-tab-badge {
        background: var(--color-primary-soft-10);
        color: var(--color-primary);
    }
    .equipment-requests-page .workflow-filters-primary-row {
        align-items: flex-end;
    }
    .equipment-requests-page .workflow-filters-advanced {
        border-top: 1px solid #dee2e6;
        padding-top: 0.75rem;
        margin-top: 0.5rem;
    }
    .equipment-requests-page .workflow-table .rm-act-btn {
        border-radius: 7px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 600;
    }
    .equipment-requests-page .workflow-table .rm-act-btn--view {
        border: 1px solid #bbf7d0;
        color: #15803d;
        background: #f0fdf4;
    }
    .equipment-requests-page .workflow-table .rm-act-btn--view:hover {
        background: #dcfce7;
        border-color: #86efac;
        color: #15803d;
    }
    .equipment-requests-page .workflow-table .rm-act-btn--edit {
        border: 1px solid var(--color-primary-shadow);
        color: var(--color-primary);
        background: var(--color-primary-soft-medium);
    }
    .equipment-requests-page .workflow-table .rm-act-btn--edit:hover {
        background: var(--color-primary-soft-10);
        border-color: var(--color-primary-border-soft);
        color: var(--color-primary);
    }
    .equipment-requests-page .workflow-status-chip {
        border-color: color-mix(in srgb, var(--chip-accent, #64748b) 30%, #e2e8f0);
        background: color-mix(in srgb, var(--chip-accent, #64748b) 10%, #ffffff);
        color: var(--chip-accent, #475569);
    }
    .equipment-requests-page .er-empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: #64748b;
    }
    .equipment-requests-page .er-empty-state .mdi {
        font-size: 3rem;
        color: #cbd5e1;
        margin-bottom: 0.75rem;
    }
    .equipment-requests-page .notifications-dropdown {
        position: absolute;
        right: 0;
        top: calc(100% + 6px);
        width: 360px;
        max-height: 420px;
        z-index: 1050;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12);
        overflow: hidden;
    }
    .er-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        z-index: 1055;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 2rem 1rem;
        overflow-y: auto;
    }
    .er-modal-dialog {
        width: 100%;
        max-width: 720px;
        margin: auto;
    }
    .er-modal-content {
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.18);
    }
    .er-modal-header {
        padding: 1.25rem 1.5rem;
        background: linear-gradient(180deg, var(--color-primary-soft) 0%, #ffffff 100%);
        border-bottom: 1px solid #e2e8f0;
    }
    .er-modal-header h5 {
        margin: 0;
        font-weight: 700;
        color: #1e293b;
    }
    .er-modal-header p {
        margin: 0.25rem 0 0;
        font-size: 0.875rem;
        color: #64748b;
    }
    .er-modal-body {
        padding: 1.25rem 1.5rem;
        max-height: calc(100vh - 12rem);
        overflow-y: auto;
    }
    .er-modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .er-form-section-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin: 0 0 0.5rem;
    }
    .er-form-section {
        margin-bottom: 1.25rem;
    }
    .er-search-dropdown {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-top: 0.35rem;
    }
    .er-search-dropdown .list-group-item {
        border-left: none;
        border-right: none;
        cursor: pointer;
        font-size: 0.875rem;
    }
    .er-selected-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: var(--color-primary-soft-10);
        border: 1px solid var(--color-primary-focus);
        font-size: 0.8125rem;
        color: var(--color-primary);
        font-weight: 600;
    }
    .er-detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .er-detail-grid dt {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 0.15rem;
    }
    .er-detail-grid dd {
        margin: 0;
        font-weight: 600;
        color: #1e293b;
    }
    .equipment-requests-page .batch-header-bar .batch-subtitle {
        flex: 0 0 100%;
        width: 100%;
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid #f1f5f9;
    }
</style>

    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show mb-3" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="$set('message', '')"><span>&times;</span></button>
        </div>
    @endif

    <div class="row workflow-board-header mb-3">
        <div class="col-12">
            <div class="batch-header-bar">
                <div class="batch-header-top">
                    <div class="batch-title-group">
                        <i class="mdi mdi-tools" style="font-size:1.2rem; color:#64748b;"></i>
                        <span class="batch-code-label">Equipment Requests</span>
                        <span class="batch-stage-pill">
                            <i class="mdi mdi-map-marker-radius" style="font-size:0.75rem;"></i>
                            {{ $this->zoneLabel }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                        <div class="position-relative">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-action-sm" wire:click="toggleNotificationsPanel">
                                <i class="mdi mdi-bell-outline"></i>
                                Notifications
                                @if($this->unreadNotificationCount > 0)
                                    <span class="badge badge-danger ml-1">{{ $this->unreadNotificationCount }}</span>
                                @endif
                            </button>
                            @if($showNotificationsPanel)
                                <div class="notifications-dropdown">
                                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                        <strong class="small">Inbox</strong>
                                        @if($this->unreadNotificationCount > 0)
                                            <button type="button" class="btn btn-link btn-sm p-0" wire:click="markAllNotificationsRead">Mark all read</button>
                                        @endif
                                    </div>
                                    <div class="list-group list-group-flush overflow-auto" style="max-height: 340px;">
                                        @forelse($this->notifications as $notification)
                                            <button type="button"
                                                    class="list-group-item list-group-item-action text-left {{ $notification->is_read ? '' : 'font-weight-bold' }}"
                                                    wire:click="markNotificationRead('{{ $notification->id }}')">
                                                <div class="small text-muted">{{ $notification->created_at->diffForHumans() }}</div>
                                                <div>{{ $notification->title }}</div>
                                                <div class="small text-muted">{{ Str::limit($notification->message, 80) }}</div>
                                            </button>
                                        @empty
                                            <div class="list-group-item text-muted text-center small">No notifications</div>
                                        @endforelse
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if($this->canAdd())
                            <button type="button" class="btn btn-sm btn-primary btn-action-sm" wire:click="openCreateModal">
                                <i class="mdi mdi-plus-circle-outline"></i> New Request
                            </button>
                        @endif
                    </div>
                </div>
                <p class="text-muted small mb-0 batch-subtitle">Request equipment usage time and track approvals.</p>
            </div>
        </div>
    </div>

    @php
        $stats = $this->requestStats;
        $tabCounts = $this->tabCounts;
    @endphp
    <div class="stat-cards-row mb-3">
        <div class="stat-card">
            <div class="stat-card-label">Submitted</div>
            <div class="stat-card-content">
                <div class="stat-card-value">{{ $stats['submitted'] }}</div>
                <div class="stat-card-icon"><i class="mdi mdi-inbox-arrow-down"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Approved</div>
            <div class="stat-card-content">
                <div class="stat-card-value">{{ $stats['approved'] }}</div>
                <div class="stat-card-icon"><i class="mdi mdi-check-circle-outline"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Scheduled today</div>
            <div class="stat-card-content">
                <div class="stat-card-value">{{ $stats['scheduled_today'] }}</div>
                <div class="stat-card-icon"><i class="mdi mdi-calendar-today"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Rejected</div>
            <div class="stat-card-content">
                <div class="stat-card-value">{{ $stats['rejected'] }}</div>
                <div class="stat-card-icon"><i class="mdi mdi-close-circle-outline"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Unread notifications</div>
            <div class="stat-card-content">
                <div class="stat-card-value">{{ $stats['unread_notifications'] }}</div>
                <div class="stat-card-icon"><i class="mdi mdi-bell-ring-outline"></i></div>
            </div>
        </div>
    </div>

    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
            <h6><i class="mdi mdi-format-list-bulleted"></i> Request queue</h6>
        </div>

        <div class="workflow-board-panel-body p-0">
            <div class="px-3 pt-3">
                <div class="workflow-receiving-tabs" role="tablist">
                    <button type="button"
                            class="workflow-receiving-tab {{ $activeTab === 'submitted' ? 'is-active' : '' }}"
                            wire:click="$set('activeTab', 'submitted')">
                        Submitted
                        <span class="workflow-receiving-tab-badge">{{ $tabCounts['submitted'] }}</span>
                    </button>
                    <button type="button"
                            class="workflow-receiving-tab {{ $activeTab === 'approved' ? 'is-active' : '' }}"
                            wire:click="$set('activeTab', 'approved')">
                        Approved
                        <span class="workflow-receiving-tab-badge">{{ $tabCounts['approved'] }}</span>
                    </button>
                    <button type="button"
                            class="workflow-receiving-tab {{ $activeTab === 'scheduled_today' ? 'is-active' : '' }}"
                            wire:click="$set('activeTab', 'scheduled_today')">
                        Scheduled today
                        <span class="workflow-receiving-tab-badge">{{ $tabCounts['scheduled_today'] }}</span>
                    </button>
                    <button type="button"
                            class="workflow-receiving-tab {{ $activeTab === 'my_requests' ? 'is-active' : '' }}"
                            wire:click="$set('activeTab', 'my_requests')">
                        My Requests
                        <span class="workflow-receiving-tab-badge">{{ $tabCounts['my_requests'] }}</span>
                    </button>
                    <button type="button"
                            class="workflow-receiving-tab {{ $activeTab === 'rejected' ? 'is-active' : '' }}"
                            wire:click="$set('activeTab', 'rejected')">
                        Rejected
                        <span class="workflow-receiving-tab-badge">{{ $tabCounts['rejected'] }}</span>
                    </button>
                </div>
            </div>

            <div class="px-3 pb-3">
                <div class="bg-light p-3 rounded border-bottom">
                    <div class="row workflow-filters-primary-row">
                        <div class="col-md-4">
                            <div class="form-group mb-3 mb-md-0">
                                <label class="form-label small fw-bold">Search</label>
                                <div class="position-relative">
                                    <input type="text"
                                           wire:model.live.debounce.300ms="search"
                                           class="form-control form-control-sm"
                                           placeholder="Equipment, requester, sample code...">
                                    <div wire:loading wire:target="search" class="position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);">
                                        <span class="spinner-border spinner-border-sm text-primary"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3 mb-md-0">
                                <label class="form-label small fw-bold">Request from</label>
                                <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3 mb-md-0">
                                <label class="form-label small fw-bold">Request to</label>
                                <input type="date" wire:model.live="dateTo" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3 mb-md-0">
                                <label class="form-label small fw-bold">Per page</label>
                                <select wire:model.live="perPage" class="form-control form-control-sm">
                                    @foreach($perPageOptions as $size)
                                        <option value="{{ $size }}">{{ $size }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" wire:click="toggleAdvancedFilters" class="btn btn-outline-secondary btn-sm w-100" style="height: 31px;">
                                <i class="mdi mdi-filter-variant"></i>
                                {{ $showAdvancedFilters ? 'Fewer filters' : 'More filters' }}
                            </button>
                        </div>
                    </div>
                    @if($showAdvancedFilters)
                        <div class="workflow-filters-advanced">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="form-label small fw-bold">Lab</label>
                                        <x-searchable-select
                                            wire:model.live="filterLabId"
                                            :options="$this->filterLabs->map(fn($lab) => ['id' => $lab->id, 'name' => $lab->name])"
                                            placeholder="Search labs..."
                                            empty-label="All labs"
                                            size="sm"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @include('livewire.lab.equipment-requests.partials.requests-table', [
                'requests' => $requests,
                'activeTab' => $activeTab,
            ])
        </div>
    </div>

    {{-- Create modal --}}
    @if($showCreateModal)
        <div class="er-modal-backdrop" wire:keydown.escape="closeCreateModal">
            <div class="er-modal-dialog" role="dialog">
                <div class="er-modal-content">
                    <div class="er-modal-header d-flex justify-content-between align-items-start">
                        <div>
                            <h5><i class="mdi mdi-plus-circle text-primary"></i> New equipment request</h5>
                            <p>Select equipment, attach samples, and propose a usage window.</p>
                        </div>
                        <button type="button" class="close" wire:click="closeCreateModal"><span>&times;</span></button>
                    </div>
                    <form wire:submit.prevent="submitRequest">
                        <div class="er-modal-body">
                            <div class="er-form-section">
                                <p class="er-form-section-label">Equipment</p>
                                @if($equipment_id)
                                    <span class="er-selected-chip mb-2 d-inline-flex">
                                        <i class="mdi mdi-wrench"></i> {{ $selectedEquipmentName }}
                                    </span>
                                    <button type="button" class="btn btn-link btn-sm p-0 ml-2" wire:click="clearEquipment">Change</button>
                                @else
                                    <input type="text" class="form-control @error('equipment_id') is-invalid @enderror"
                                           wire:model.live.debounce.300ms="equipmentSearch"
                                           placeholder="Search by name or equipment number...">
                                    @error('equipment_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    @if(trim($equipmentSearch) !== '')
                                        <div class="er-search-dropdown list-group">
                                            @forelse($this->filteredEquipment as $equipment)
                                                <button type="button" class="list-group-item list-group-item-action"
                                                        wire:click="selectEquipment('{{ $equipment->id }}')">
                                                    {{ $equipment->name }} ({{ $equipment->equipment_number }})
                                                </button>
                                            @empty
                                                <div class="list-group-item text-muted small">No equipment found</div>
                                            @endforelse
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <div class="row er-form-section">
                                <div class="col-md-6">
                                    <p class="er-form-section-label">Proposed start</p>
                                    <input type="datetime-local" class="form-control @error('proposed_start_at') is-invalid @enderror" wire:model="proposed_start_at">
                                    @error('proposed_start_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <p class="er-form-section-label">Proposed end</p>
                                    <input type="datetime-local" class="form-control @error('proposed_end_at') is-invalid @enderror" wire:model="proposed_end_at">
                                    @error('proposed_end_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="er-form-section">
                                <p class="er-form-section-label">Samples
                                    @if(count($sample_detail_ids) > 0)
                                        <span class="badge badge-primary ml-1">{{ count($sample_detail_ids) }} selected</span>
                                    @endif
                                </p>
                                <input type="text" class="form-control mb-2" wire:model.live.debounce.300ms="sampleSearch" placeholder="Search sample or batch code...">
                                @error('sample_detail_ids') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                                <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto; background: #f8fafc;">
                                    @forelse($this->filteredSamples as $sample)
                                        <div class="custom-control custom-checkbox py-1" wire:key="sample-{{ $sample->id }}">
                                            <input type="checkbox" class="custom-control-input" id="sample-{{ $sample->id }}"
                                                   @checked(in_array($sample->id, $sample_detail_ids, true))
                                                   wire:click="toggleSample('{{ $sample->id }}')">
                                            <label class="custom-control-label" for="sample-{{ $sample->id }}">
                                                {{ $sample->sample_code }}
                                                @if(! empty($sample->batch_code))
                                                    <small class="text-muted">({{ $sample->batch_code }})</small>
                                                @endif
                                            </label>
                                        </div>
                                    @empty
                                        <p class="text-muted small mb-0 px-1">No samples found.</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="er-form-section mb-0">
                                <p class="er-form-section-label">Comment</p>
                                <textarea class="form-control" rows="2" wire:model="request_comment" placeholder="Optional notes for approvers"></textarea>
                            </div>
                        </div>
                        <div class="er-modal-footer">
                            <button type="button" class="btn btn-outline-secondary btn-action-sm" wire:click="closeCreateModal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-action-sm" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="submitRequest"><i class="mdi mdi-send"></i> Submit request</span>
                                <span wire:loading wire:target="submitRequest">Submitting...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Detail modal --}}
    @if($showDetailModal && $selectedRequest)
        <div class="er-modal-backdrop" wire:keydown.escape="closeDetailModal">
            <div class="er-modal-dialog">
                <div class="er-modal-content">
                    <div class="er-modal-header d-flex justify-content-between align-items-start">
                        <div>
                            <h5><i class="mdi mdi-file-document-outline text-primary"></i> Request details</h5>
                            <p>Submitted {{ $selectedRequest->created_at?->format('Y-m-d H:i') }}</p>
                        </div>
                        <button type="button" class="close" wire:click="closeDetailModal"><span>&times;</span></button>
                    </div>
                    <div class="er-modal-body">
                        <dl class="er-detail-grid">
                            <div>
                                <dt>Equipment</dt>
                                <dd>{{ $selectedRequest->equipment?->name }}</dd>
                            </div>
                            <div>
                                <dt>Equipment #</dt>
                                <dd>{{ $selectedRequest->equipment?->equipment_number ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt>Requester</dt>
                                <dd>{{ $selectedRequest->requester?->name }}</dd>
                            </div>
                            <div>
                                <dt>Status</dt>
                                <dd>@include('livewire.lab.equipment-requests.partials.status-badge', ['status' => $selectedRequest->status])</dd>
                            </div>
                        </dl>

                        <p class="er-form-section-label">Proposed window</p>
                        <p class="mb-3">{{ $selectedRequest->proposed_start_at?->format('Y-m-d H:i') }} &ndash; {{ $selectedRequest->proposed_end_at?->format('Y-m-d H:i') }}</p>

                        @if($selectedRequest->approved_start_at || $selectedRequest->approved_end_at)
                            <p class="er-form-section-label">Approved window</p>
                            <p class="mb-3">{{ $selectedRequest->approved_start_at?->format('Y-m-d H:i') }} &ndash; {{ $selectedRequest->approved_end_at?->format('Y-m-d H:i') }}</p>
                        @endif

                        @if($selectedRequest->request_comment)
                            <p class="er-form-section-label">Request comment</p>
                            <p class="bg-light p-2 rounded small mb-3" style="white-space: pre-wrap;">{{ $selectedRequest->request_comment }}</p>
                        @endif

                        @if($selectedRequest->approval_comment)
                            <p class="er-form-section-label">Approval comment</p>
                            <p class="bg-light p-2 rounded small mb-3" style="white-space: pre-wrap;">{{ $selectedRequest->approval_comment }}</p>
                        @endif

                        @if($selectedRequest->helpingAnalyst)
                            <p class="mb-2"><strong>Helping analyst:</strong> {{ $selectedRequest->helpingAnalyst->name }}</p>
                        @endif

                        @if($selectedRequest->approver)
                            <p class="mb-2"><strong>Approved by:</strong> {{ $selectedRequest->approver->name }}
                                @if($selectedRequest->approved_at) ({{ $selectedRequest->approved_at->format('Y-m-d H:i') }}) @endif
                            </p>
                        @endif

                        @if($selectedRequest->rejector)
                            <p class="mb-3"><strong>Rejected by:</strong> {{ $selectedRequest->rejector->name }}
                                @if($selectedRequest->rejected_at) ({{ $selectedRequest->rejected_at->format('Y-m-d H:i') }}) @endif
                            </p>
                        @endif

                        <p class="er-form-section-label">Samples</p>
                        <ul class="mb-0 pl-3">
                            @foreach($selectedRequest->sampleDetails as $sample)
                                <li>{{ $sample->sample_code }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="er-modal-footer">
                        @if($selectedRequest->isPending() && $this->canApprove())
                            <button type="button" class="btn btn-primary btn-action-sm" wire:click="openApprovalModal('{{ $selectedRequest->id }}')">
                                <i class="mdi mdi-check-decagram"></i> Review
                            </button>
                        @endif
                        @if($selectedRequest->isPending() && (string) $selectedRequest->requester_id === (string) auth()->id())
                            <button type="button" class="btn btn-outline-danger btn-action-sm"
                                    wire:click="cancelRequest('{{ $selectedRequest->id }}')"
                                    wire:confirm="Cancel this request?">
                                Cancel request
                            </button>
                        @endif
                        <button type="button" class="btn btn-outline-secondary btn-action-sm" wire:click="closeDetailModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Approval modal --}}
    @if($showApprovalModal && $selectedRequest)
        <div class="er-modal-backdrop" wire:keydown.escape="closeApprovalModal">
            <div class="er-modal-dialog">
                <div class="er-modal-content">
                    <div class="er-modal-header d-flex justify-content-between align-items-start">
                        <div>
                            <h5><i class="mdi mdi-check-decagram text-primary"></i> Review request</h5>
                            <p>{{ $selectedRequest->equipment?->name }} — {{ $selectedRequest->requester?->name ?? 'Requester' }}</p>
                        </div>
                        <button type="button" class="close" wire:click="closeApprovalModal"><span>&times;</span></button>
                    </div>
                    <form wire:submit.prevent="submitApproval">
                        <div class="er-modal-body">
                            <div class="workflow-receiving-tabs mb-3" style="max-width: 320px;">
                                <button type="button"
                                        class="workflow-receiving-tab {{ $approval_decision === 'approve' ? 'is-active' : '' }}"
                                        wire:click="$set('approval_decision', 'approve')">
                                    Approve
                                </button>
                                <button type="button"
                                        class="workflow-receiving-tab {{ $approval_decision === 'reject' ? 'is-active' : '' }}"
                                        wire:click="$set('approval_decision', 'reject')">
                                    Reject
                                </button>
                            </div>

                            @if($approval_decision === 'approve')
                                <div class="row er-form-section">
                                    <div class="col-md-6">
                                        <p class="er-form-section-label">Approved start</p>
                                        <input type="datetime-local" class="form-control @error('approved_start_at') is-invalid @enderror" wire:model="approved_start_at">
                                        @error('approved_start_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <p class="er-form-section-label">Approved end</p>
                                        <input type="datetime-local" class="form-control @error('approved_end_at') is-invalid @enderror" wire:model="approved_end_at">
                                        @error('approved_end_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="er-form-section">
                                    <p class="er-form-section-label">Helping analyst (optional)</p>
                                    @if($helping_analyst_id)
                                        <span class="er-selected-chip">{{ $analystSearch }}</span>
                                        <button type="button" class="btn btn-link btn-sm p-0 ml-2" wire:click="clearHelpingAnalyst">Clear</button>
                                    @else
                                        <input type="text" class="form-control" wire:model.live.debounce.300ms="analystSearch" placeholder="Search analyst...">
                                        @if(trim($analystSearch) !== '')
                                            <div class="er-search-dropdown list-group">
                                                @forelse($this->filteredAnalysts as $analyst)
                                                    <button type="button" class="list-group-item list-group-item-action"
                                                            wire:click="selectHelpingAnalyst('{{ $analyst->id }}')">
                                                        {{ $analyst->name }}
                                                    </button>
                                                @empty
                                                    <div class="list-group-item text-muted small">No analysts found</div>
                                                @endforelse
                                            </div>
                                        @endif
                                    @endif
                                </div>

                                <div class="er-form-section mb-0">
                                    <p class="er-form-section-label">Comment</p>
                                    <textarea class="form-control" rows="2" wire:model="approval_comment"></textarea>
                                </div>
                            @else
                                <div class="er-form-section mb-0">
                                    <p class="er-form-section-label">Rejection reason</p>
                                    <textarea class="form-control @error('approval_comment') is-invalid @enderror" rows="3" wire:model="approval_comment"></textarea>
                                    @error('approval_comment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            @endif
                        </div>
                        <div class="er-modal-footer">
                            <button type="button" class="btn btn-outline-secondary btn-action-sm" wire:click="closeApprovalModal">Cancel</button>
                            <button type="submit" class="btn btn-{{ $approval_decision === 'reject' ? 'danger' : 'primary' }} btn-action-sm" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="submitApproval">
                                    {{ $approval_decision === 'reject' ? 'Reject request' : 'Approve request' }}
                                </span>
                                <span wire:loading wire:target="submitApproval">Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
