<div>
    <style>
        .modern-search-bar {
            background: #ffffff;
            border-bottom: 1px solid #e8eaed;
            padding: 16px 24px;
        }

        .modern-search-input {
            border: 1px solid #dadce0;
            border-radius: 24px;
            padding: 10px 16px 10px 44px;
            font-size: 14px;
            transition: all 0.2s ease;
            width: 100%;
        }

        .modern-search-input:focus {
            outline: none;
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
        }

        .modern-search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #5f6368;
            font-size: 18px;
        }

        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #ffffff;
        }

        .modern-table thead {
            background: #f8f9fa;
        }

        .modern-table thead th {
            padding: 12px 24px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #5f6368;
            border-bottom: 1px solid #e8eaed;
            white-space: nowrap;
        }

        .modern-table tbody tr {
            border-bottom: 1px solid #f1f3f4;
            transition: background-color 0.15s ease;
        }

        .modern-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .modern-table tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        .modern-table tbody tr:hover {
            background: #ffeaea !important;
        }

        .modern-table tbody td {
            padding: 16px 24px;
            font-size: 14px;
            color: #202124;
            vertical-align: middle;
        }

        .modern-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .modern-action-btn {
            border: 1px solid #dadce0;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 13px;
            transition: all 0.2s ease;
            margin-right: 4px;
        }

        .modern-action-btn:hover {
            background: #f1f3f4;
            border-color: #dc3545;
        }

        .modern-empty-state {
            padding: 60px 20px;
            text-align: center;
        }

        .modern-empty-state i {
            font-size: 64px;
            color: #dee2e6;
            margin-bottom: 16px;
        }

        .modern-pagination {
            padding: 16px 24px;
            border-top: 1px solid #e8eaed;
            background: #ffffff;
        }
    </style>

    <!-- Search Bar -->
    <div class="modern-search-bar">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="position-relative">
                    <i class="mdi mdi-magnify modern-search-icon"></i>
                    <input wire:model.live.debounce.300ms="search" 
                           type="text" 
                           class="modern-search-input" 
                           placeholder="Search risks by number, title...">
                </div>
            </div>
            <div class="col-md-3">
                <select wire:model.live="perPage" class="form-control">
                    <option value="15">15 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
            </div>
            <div class="col-md-3 text-right">
                <button wire:click="$toggle('showFilters')" class="btn btn-sm btn-outline-secondary">
                    <i class="mdi mdi-filter-variant"></i> {{ $showFilters ? 'Hide' : 'Show' }} Filters
                </button>
            </div>
        </div>

        @if($showFilters)
        <div class="row mt-3">
            <div class="col-md-3">
                <select wire:model.live="filters.category_id" class="form-control form-control-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select wire:model.live="filters.source_id" class="form-control form-control-sm">
                    <option value="">All Sources</option>
                    @foreach($sources as $source)
                    <option value="{{ $source->id }}">{{ $source->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select wire:model.live="filters.risk_level" class="form-control form-control-sm">
                    <option value="">All Levels</option>
                    <option value="Critical">Critical</option>
                    <option value="High">High</option>
                    <option value="Medium">Medium</option>
                    <option value="Low">Low</option>
                </select>
            </div>
            <div class="col-md-2">
                <input wire:model.live="filters.date_from" type="date" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <input wire:model.live="filters.date_to" type="date" class="form-control form-control-sm">
            </div>
        </div>
        @endif
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="modern-table">
            <thead>
                <tr>
                    <th wire:click="sortBy('risk_number')" style="cursor: pointer;">
                        Risk #
                        @if($sortField === 'risk_number')
                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </th>
                    <th wire:click="sortBy('title')" style="cursor: pointer;">
                        Title
                        @if($sortField === 'title')
                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </th>
                    <th>Category</th>
                    <th>Risk Level</th>
                    <th>Status</th>
                    <th wire:click="sortBy('date_identified')" style="cursor: pointer;">
                        Date Identified
                        @if($sortField === 'date_identified')
                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </th>
                    <th>Next Review</th>
                    <th style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($risks as $risk)
                <tr>
                    <td style="font-weight: 500; color: #dc3545;">
                        <a href="{{ route('risk.risks.show', $risk->id) }}" style="color: #dc3545; text-decoration: none;">
                            <strong>{{ $risk->risk_number }}</strong>
                        </a>
                    </td>
                    <td>{{ Str::limit($risk->title, 50) }}</td>
                    <td>{{ $risk->category->name ?? 'N/A' }}</td>
                    <td>
                        @php
                            $badgeClass = 'secondary';
                            if ($risk->risk_level === 'Critical') $badgeClass = 'danger';
                            elseif ($risk->risk_level === 'High') $badgeClass = 'warning';
                            elseif ($risk->risk_level === 'Medium') $badgeClass = 'info';
                            elseif ($risk->risk_level === 'Low') $badgeClass = 'success';
                        @endphp
                        <span class="badge badge-{{ $badgeClass }}">{{ $risk->risk_level ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <span class="modern-badge badge-info status-hover-badge" 
                              data-toggle="popover"
                              data-placement="bottom"
                              data-trigger="hover"
                              data-html="true"
                              data-content-id="risk-details-{{ $risk->id }}"
                              data-container="body"
                              style="cursor: pointer;">
                            {{ $risk->display_status_name ?? 'N/A' }}
                        </span>
                    </td>
                    <td>{{ $risk->date_identified ? $risk->date_identified->format('Y-m-d') : 'N/A' }}</td>
                    <td>
                        @if($risk->next_review_date)
                            @if($risk->next_review_date < now())
                                <span class="badge badge-danger">{{ $risk->next_review_date->format('Y-m-d') }}</span>
                            @else
                                {{ $risk->next_review_date->format('Y-m-d') }}
                            @endif
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('risk.risks.show', $risk->id) }}" class="modern-action-btn" title="View" style="color: #1a73e8;">
                                <i class="mdi mdi-eye"></i>
                            </a>
                            <a href="{{ route('risk.risks.edit', $risk->id) }}" class="modern-action-btn" title="Edit" style="color: #d97706;">
                                <i class="mdi mdi-pencil"></i>
                            </a>
                            <button wire:click="confirmDelete({{ $risk->id }})" class="modern-action-btn" title="Delete" style="color: #c33;">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="modern-empty-state">
                        <i class="mdi mdi-alert-octagon-outline"></i>
                        <p>No risks found. Click "New Risk" to create one.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Hidden content for risk details popovers -->
    <div style="display: none !important; visibility: hidden; position: absolute; left: -9999px; width: 0; height: 0; overflow: hidden;">
        @foreach($risks as $risk)
        <div id="risk-details-{{ $risk->id }}">
            @include('livewire.risk-module.partials.risk-details-popover', ['risk' => $risk])
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="modern-pagination">
        {{ $risks->links() }}
    </div>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="close text-white" wire:click="$set('showDeleteModal', false)">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this risk? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showDeleteModal', false)">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteRisk">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Status Change Modal -->
    @if($showStatusModal)
    <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="changeStatus">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Change Risk Status</h5>
                        <button type="button" class="close text-white" wire:click="$set('showStatusModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Current Workflow Step: <strong>Step {{ $currentWorkflowStep }}</strong></label>
                        </div>
                        <div class="form-group">
                            <label>New Status <span class="text-danger">*</span></label>
                            <select wire:model="newStatus" class="form-control" required>
                                @foreach($availableWorkflowSteps as $stepNum => $stepName)
                                <option value="{{ $stepName }}">Step {{ $stepNum }}: {{ $stepName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea wire:model="statusNotes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showStatusModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Change Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
<style>
    /* Force popover width increase */
    .popover {
        max-width: 800px !important;
        width: 800px !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize popovers with proper settings
        function initPopovers() {
            // Destroy existing popovers to prevent duplicates/flickering
            $('.status-hover-badge[data-toggle="popover"]').popover('dispose');

            $('.status-hover-badge[data-toggle="popover"]').each(function() {
                var $this = $(this);
                var contentId = $this.data('content-id');
                
                // Get content from hidden div
                var content = $('#' + contentId).html();
                
                if (content) {
                    $this.popover({
                        container: 'body',
                        placement: 'auto', // Allow it to flip if space is tight
                        trigger: 'manual', // Changed to manual for better control
                        html: true,
                        content: content,
                        sanitize: false, // Important for complex HTML
                        boundary: 'viewport'
                    })
                    .on('mouseenter', function () {
                        var _this = this;
                        $(this).popover('show');
                        $('.popover').on('mouseleave', function () {
                            $(_this).popover('hide');
                        });
                    })
                    .on('mouseleave', function () {
                        var _this = this;
                        setTimeout(function () {
                            if (!$('.popover:hover').length) {
                                $(_this).popover('hide');
                            }
                        }, 100);
                    });
                }
            });
        }
        
        // Initialize on page load
        initPopovers();
        
        // Re-initialize after Livewire updates
        document.addEventListener('livewire:load', function() {
            initPopovers();
        });
        
        document.addEventListener('livewire:update', function() {
            initPopovers();
        });
    });
</script>
</div>


