<div>
    @section('title2')
        <title>Complaint Registry | CRM</title>
    @endsection

    <main>
        <div class="container-fluid">
            <x-crm.page-header
                :breadcrumbItems="$this->breadcrumbItems"
                :title="$pageTitle ?? $stage"
                :subtitle="$pageSubtitle ?? 'Tracking, resolution, and compliance status of all reported complaints.'"
                icon="mdi-comment-alert"
            >
                <x-slot:actions>
                    <button type="button" class="btn btn-outline-success btn-sm mr-2 crm-btn-export crm-outline-btn-sm" wire:click="exportToExcel" wire:loading.attr="disabled">
                        <i class="fa fa-file-excel mr-1"></i> Export to Excel
                    </button>
                    @if($this->getStageName($this->stage) === 'Open Complaints' && $this->canAddComplaint)
                        <button type="button" class="btn btn-outline-primary btn-sm crm-outline-btn-sm" wire:click="addComplaint">
                            <i class="mdi mdi-plus"></i> Add New Complaint
                        </button>
                    @endif
                </x-slot:actions>
            </x-crm.page-header>

            <div class="card tab-card">
                <div class="card-body p-0">
        <x-crm.filter-bar :title="__('crm.filters')" class="crm-filter-bar-sticky p-3 border-bottom">
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="crm-search-wrapper w-100" style="max-width: 100%;">
                        <i class="mdi mdi-magnify crm-search-icon"></i>
                        <input type="text" class="form-control w-100" placeholder="Search by ID, description..."
                            wire:model.live.debounce.300ms="search">
                    </div>
                </div>

                <!-- Filters & Show Entries on Right -->
                <div class="col-md-9 d-flex justify-content-md-end align-items-center flex-wrap filter-row">
                    @if($stage == 'All Complaints')
                        <div class="d-flex align-items-center mr-3 mb-2 mb-md-0">
                            <select class="crm-select custom-select-sm no-select2" style="width: 220px;"
                                wire:model.live="activeTab" wire:key="complaint-status-filter">
                                <option value="all">All Complaint Workflow Stages</option>
                                @foreach(getComplaintWorkflow() as $id => $name)
                                    @if($id > 0)
                                        <option value="{{ $id }}">Stage {{ $id }}: {{ $name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="d-flex align-items-center mr-3 mb-2 mb-md-0">
                        <select class="crm-select custom-select-sm no-select2" style="width: 180px;"
                            wire:model.live="typeFilter" wire:key="filter-type">
                            <option value="">All Complaint Categories</option>
                            @foreach($complaintTypes as $type)
                                <option value="{{ $type->name }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex align-items-center mr-3 mb-2 mb-md-0">
                        <select class="crm-select custom-select-sm no-select2" style="width: 180px;"
                            wire:model.live="priorityFilter" wire:key="filter-priority">
                            <option value="">All Severity Levels</option>
                            @foreach(\App\Constants\CRM\CrmConstants::getPriorities() as $priority)
                                <option value="{{ $priority }}">{{ ucfirst($priority) }} Priority</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Show Entries -->
                    <div class="d-flex align-items-center mb-2 mb-md-0">
                        <label class="mb-0 mr-2 crm-filter-label text-nowrap">Show</label>
                        <select wire:model.live="perPage" wire:key="per-page-select"
                            class="crm-select custom-select-sm no-select2" style="width: 70px;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <label class="mb-0 ml-2 crm-filter-label text-nowrap">entries</label>
                    </div>
                </div>
        </x-crm.filter-bar>

        <x-crm.data-table wire:loading.class="opacity-50" class="p-3 crm-loading-overlay" plain-rows>
                <x-slot:header>
                        <tr>
                            <th style="width: 100px;">Actions</th>
                            <th>Date Received</th>
                            <th nowrap>Serial No.</th>
                            <th>Organization / Customer</th>
                            <th>Contact Person</th>
                            <th>Mode</th>
                            <th>Received By</th>
                            <th>Test Item</th>
                            <th>Lab Related?</th>
                            <th>Date Closed</th>
                            <th>Closed By</th>
                        </tr>
                </x-slot:header>
                        @forelse($complaints as $complaint)
                            <tr wire:key="complaint-{{ $complaint->id }}">
                                <td nowrap>
                                    <div class="d-flex flex-nowrap">
                                        <a href="{{ route('complaint-show', ['id' => $complaint->id]) }}"
                                            class="btn btn-sm rm-act-btn rm-act-btn--view" title="View Details">
                                            <i class="mdi mdi-eye-outline"></i>
                                        </a>
                                    </div>
                                </td>
                                <td>{{ $complaint->date ? $complaint->date->format('d-M-Y') : '-' }}</td>
                                <td>{{ $complaint->complaint_id }}</td>
                                <td>
                                    {{ $complaint->organization_name ?? $complaint->received_from }}
                                    @if($complaint->client)
                                        <br><small class="text-primary" style="font-weight:600;">Customer</small>
                                    @else
                                        <br><small class="text-muted">{{ $complaint->received_from_type ?? 'Other Party' }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $complaint->contact_name ?? '-' }}
                                    @if($complaint->title_position)
                                        <br><small class="text-muted">{{ $complaint->title_position }}</small>
                                    @endif
                                </td>
                                <td>{{ $complaint->mode_of_delivery ?? '-' }}</td>
                                <td>{{ $complaint->received_by ?? '-' }}</td>
                                <td>{{ $complaint->test_item ?? $complaint->test_item_report_serial_no ?? '-' }}</td>
                                <td>
                                    @if($complaint->is_lab_related)
                                        <span class="badge badge-soft-danger"><i class="mdi mdi-flask mr-1"></i>Yes</span>
                                    @else
                                        <span class="badge badge-soft-secondary">No</span>
                                    @endif
                                </td>
                                <td>{{ $complaint->date_closed ? $complaint->date_closed->format('d-M-Y') : '-' }}</td>
                                <td>{{ $complaint->closedBy->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12">
                                    <x-crm.empty-state
                                        icon="mdi-comment-check-outline"
                                        message="No complaints found"
                                        help="Try adjusting your filters or search — or this stage may be clear of incidents."
                                    />
                                </td>
                            </tr>
                        @endforelse
        </x-crm.data-table>

            <x-crm.pagination :summary="'Showing ' . ($complaints->firstItem() ?? 0) . ' to ' . ($complaints->lastItem() ?? 0) . ' of ' . $complaints->total() . ' results'">
                {{ $complaints->links() }}
            </x-crm.pagination>
                </div>
            </div>

        </div>
        @if($showForm)
            <div class="modal fade show"
                style="display: flex; align-items: flex-start; overflow-y: auto; background-color: rgba(0,0,0,0.5); padding-top: 30px; padding-bottom: 30px;"
                tabindex="-1" role="dialog" aria-hidden="true" wire:ignore.self>
                <div class="modal-dialog modal-xl" role="document">
                    @livewire(\App\Livewire\Crm\Complaint\ComplaintForm::class, ['complaintId' => $editingComplaintId], 'complaint-form-' . ($editingComplaintId ?? 'new'))
                </div>
            </div>
        @endif

        <!-- View Description Modal -->
        <template x-teleport="body">
            <div wire:ignore.self class="modal fade" id="descriptionModal"
                x-on:click.self="$('#descriptionModal').modal('hide')" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $selectedComplaint->complaint_id ?? '' }} Description</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="p-3 bg-light border rounded">
                                {!! $selectedComplaint->description ?? 'No description available.' !!}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </main>

    <script src="/tinymce/tinymce.min.js"></script>
    <script>
        (function () {
            var complaintFormSelect2Bound = false;

            function bindComplaintFormSelect2Events() {
                if (complaintFormSelect2Bound) return;
                if (!window.Livewire) return;

                complaintFormSelect2Bound = true;

                // Move modals to body once
                $('#complaintModal').appendTo("body");
                $('#descriptionModal').appendTo("body");

                Livewire.on('show-complaint-modal', () => {
                    $('#complaintModal').modal('show');
                    // Select2 initialization is now handled by the Alpine.js component in complaint-form
                });

                Livewire.on('close-complaint-modal', () => {
                    $('#complaintModal').modal('hide');
                });

                Livewire.on('show-description-modal', () => {
                    $('#descriptionModal').modal('show');
                });
            }

            // Initialize on load
            document.addEventListener('livewire:initialized', function () {
                bindComplaintFormSelect2Events();
            });

            // Fallback for immediate load
            if (window.Livewire) {
                bindComplaintFormSelect2Events();
            }
        })();
    </script>
</div>