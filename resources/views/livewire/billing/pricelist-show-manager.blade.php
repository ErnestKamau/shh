<div class="container-fluid pricelist-show-page lab-surface-theme ls-admin-page {{ ($showItemModal || $showCloneModal || $showDeleteItemConfirmModal) ? 'modal-active' : '' }}" data-ls-type="plex">
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show shadow-sm" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    @if(!$pricelist)
        <div class="alert alert-danger">Pricelist not found.</div>
    @else
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 page-header-card">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                            <div class="mb-3 mb-lg-0">
                                <div class="page-header-main">
                                <h2 class="mb-0 page-header-title">
                                    <i class="mdi mdi-file-document-edit-outline text-primary mr-2"></i>
                                    Pricelist Details
                                </h2>
                                <span class="status-chip page-title-status">
                                    <span class="mr-2">Status</span>
                                    <strong>{{ strtoupper($pricelist->status ?? 'no-changes') }}</strong>
                                </span>
                                </div>
                                <p class="text-muted mb-0 page-header-subtitle">
                                    {{ $pricelist->code }}{{ $pricelist->description ? ' - ' . $pricelist->description : '' }}
                                </p>
                            </div>
                            <div class="text-lg-right d-flex flex-column align-items-lg-end">
                                <a href="{{ route('view-pricelists') }}" class="btn btn-outline-primary page-back-btn">
                                    <i class="mdi mdi-arrow-left"></i> Back to Pricelists
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm metric-card h-100">
                    <div class="card-body">
                        <div class="metric-label">Currency</div>
                        <div class="metric-value">{{ $pricelist->currency_code ?? 'N/A' }}</div>
                        <div class="metric-meta">{{ $pricelist->currency_description ?? 'No currency description' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm metric-card h-100">
                    <div class="card-body">
                        <div class="metric-label">Revision</div>
                        <div class="metric-value">{{ $pricelist->revision_number ?? '1' }}</div>
                        <div class="metric-meta">Document {{ $pricelist->document_no ?? 'DOC-' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm metric-card h-100">
                    <div class="card-body">
                        <div class="metric-label">Assigned Customers</div>
                        <div class="metric-value">{{ $summary['assigned_customers'] }}</div>
                        <div class="metric-meta">Linked to this pricelist</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm metric-card h-100">
                    <div class="card-body">
                        <div class="metric-label">Pending Changes</div>
                        <div class="metric-value">{{ $summary['changed_items'] }}</div>
                        <div class="metric-meta">Out of {{ $summary['total_items'] }} total items</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm workspace-card">
            <div class="card-body p-0">
                <div class="workspace-tabs mx-3 mx-lg-4 mt-3 mt-lg-4">
                    <button type="button"
                            class="workspace-tab {{ $activeTab === 'customer-assignment' ? 'active' : '' }}"
                            wire:click="setActiveTab('customer-assignment')"
                            aria-selected="{{ $activeTab === 'customer-assignment' ? 'true' : 'false' }}">
                        <i class="mdi mdi-account-multiple-outline mr-1"></i>
                        Customer Assignment
                    </button>
                    <button type="button"
                            class="workspace-tab {{ $activeTab === 'pricelist-items' ? 'active' : '' }}"
                            wire:click="setActiveTab('pricelist-items')"
                            aria-selected="{{ $activeTab === 'pricelist-items' ? 'true' : 'false' }}">
                        <i class="mdi mdi-format-list-bulleted-square mr-1"></i>
                        Pricelist Items
                    </button>
                </div>

                <div class="p-3 p-lg-4">
                    @if($activeTab === 'customer-assignment')
                        <div class="row">
                            <div class="col-xl-7 mb-4">
                                <div class="card border-0 shadow-sm inner-card h-100">
                                    <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1">Pricelist Profile</h5>
                                            <p class="text-muted mb-0">Edit the main pricing metadata before assigning customers.</p>
                                        </div>
                                        <span class="badge badge-light status-chip">{{ $pricelist->code }}</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group mb-3">
                                                    <label class="form-label soft-label">Description <span class="text-danger">*</span></label>
                                                    <input type="text" wire:model="pricelistForm.description" class="form-control modern-input @error('pricelistForm.description') is-invalid @enderror">
                                                    @error('pricelistForm.description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-3">
                                                    <label class="form-label soft-label">Currency <span class="text-danger">*</span></label>
                                                    <select wire:model="pricelistForm.currency_id" class="form-control modern-input @error('pricelistForm.currency_id') is-invalid @enderror">
                                                        <option value="">Select currency</option>
                                                        @foreach($currencies as $currency)
                                                            <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('pricelistForm.currency_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group mb-3">
                                                    <label class="form-label soft-label">Valid Till</label>
                                                    <input type="date" wire:model="pricelistForm.valid_till" class="form-control modern-input @error('pricelistForm.valid_till') is-invalid @enderror">
                                                    @error('pricelistForm.valid_till') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-3">
                                                    <label class="form-label soft-label">Status Tag</label>
                                                    <select wire:model="pricelistForm.status" class="form-control modern-input @error('pricelistForm.status') is-invalid @enderror">
                                                        <option value="no-changes">no-changes</option>
                                                        <option value="has-changes">has-changes</option>
                                                    </select>
                                                    @error('pricelistForm.status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label soft-label d-block">Flags</label>
                                                <div class="d-flex align-items-center flex-wrap switch-stack">
                                                    <div class="form-check form-switch mr-3 mb-2">
                                                        <input type="checkbox" wire:model="pricelistForm.is_master" class="form-check-input" role="switch">
                                                        <label class="form-check-label">Master</label>
                                                    </div>
                                                    <div class="form-check form-switch mb-2">
                                                        <input type="checkbox" wire:model="pricelistForm.active" class="form-check-input" role="switch">
                                                        <label class="form-check-label">Active</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="profile-strip mb-4">
                                            <div class="profile-strip-item">
                                                <span class="profile-strip-label">Code</span>
                                                <strong>{{ $pricelist->code }}</strong>
                                            </div>
                                            <div class="profile-strip-item">
                                                <span class="profile-strip-label">Document</span>
                                                <strong>{{ $pricelist->document_no ?? 'DOC-' }}</strong>
                                            </div>
                                            <div class="profile-strip-item">
                                                <span class="profile-strip-label">Items</span>
                                                <strong>{{ $summary['total_items'] }}</strong>
                                            </div>
                                            <div class="profile-strip-item">
                                                <span class="profile-strip-label">Changed</span>
                                                <strong>{{ $summary['changed_items'] }}</strong>
                                            </div>
                                        </div>

                                        <button type="button" class="btn btn-primary modern-primary-btn" wire:click="savePricelist">
                                            <i class="mdi mdi-content-save-outline"></i> Save Pricelist Details
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-5 mb-4">
                                <div class="card border-0 shadow-sm inner-card h-100">
                                    <div class="card-header border-0 bg-white">
                                        <h5 class="mb-1">Assign Customers</h5>
                                        <p class="text-muted mb-0">Use typeahead and chips to stage multiple customers before assigning.</p>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-3">
                                            <label class="form-label soft-label">Search Customer</label>
                                            <input type="text" wire:model.live.debounce.250ms="customerSearch" class="form-control modern-input" placeholder="Start typing name, code, or email...">
                                        </div>

                                        @if($customerPickerChips->count() > 0)
                                            <div class="chip-list mb-3">
                                                @foreach($customerPickerChips as $chip)
                                                    <span class="picker-chip">
                                                        {{ $chip->name }}
                                                        @if($chip->code)
                                                            <small>{{ $chip->code }}</small>
                                                        @endif
                                                        <button type="button" class="chip-close" wire:click="removeCustomerChip(@js($chip->id))">
                                                            <i class="mdi mdi-close"></i>
                                                        </button>
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="customer-pick-list mb-3">
                                            @forelse($availableCustomers->take(6) as $customer)
                                                <div class="customer-pick-item">
                                                    <div>
                                                        <div class="customer-name">{{ $customer->name }}</div>
                                                        <div class="customer-meta">{{ $customer->code ?: 'No code' }}{{ $customer->email ? ' • ' . $customer->email : '' }}</div>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-light" wire:click="addCustomerChip(@js($customer->id))">
                                                        <i class="mdi mdi-plus"></i> Add
                                                    </button>
                                                </div>
                                            @empty
                                                <div class="empty-state compact-empty">
                                                    <i class="mdi mdi-account-search-outline"></i>
                                                    <span>No matching customers available.</span>
                                                </div>
                                            @endforelse
                                        </div>

                                        <div class="d-flex flex-wrap workspace-actions">
                                            <button type="button" class="btn btn-outline-primary modern-secondary-btn" wire:click="assignCustomerChips">
                                                <i class="mdi mdi-account-multiple-plus-outline"></i> Assign Selected Chips
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary modern-secondary-btn" wire:click="clearCustomerChips">
                                                <i class="mdi mdi-broom"></i> Clear Chips
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="assignment-tabs-wrap mb-4">
                            <div class="assignment-tabs">
                                <button type="button" class="assignment-tab {{ $customerAssignmentTab === 'assigned-customers' ? 'active' : '' }}" wire:click="setCustomerAssignmentTab('assigned-customers')">
                                    Assigned Customers
                                </button>
                                <button type="button" class="assignment-tab {{ $customerAssignmentTab === 'email-pricelist' ? 'active' : '' }}" wire:click="setCustomerAssignmentTab('email-pricelist')">
                                    Email Pricelist to Customers
                                </button>
                                <button type="button" class="assignment-tab {{ $customerAssignmentTab === 'recent-sends' ? 'active' : '' }}" wire:click="setCustomerAssignmentTab('recent-sends')">
                                    Recent Sends
                                </button>
                            </div>
                        </div>

                        @if($customerAssignmentTab === 'assigned-customers')
                            <div class="row">
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm inner-card">
                                        <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-1">Assigned Customers</h5>
                                                <p class="text-muted mb-0">Customers currently using this pricelist.</p>
                                            </div>
                                            <span class="badge badge-light status-chip">{{ $summary['assigned_customers'] }} linked</span>
                                        </div>
                                        <div class="card-body">
                                            @if($assignedCustomers->count() > 0)
                                                <div class="row">
                                                    @foreach($assignedCustomers as $assignment)
                                                        <div class="col-xl-4 col-md-6 mb-3">
                                                            <div class="assigned-customer-card">
                                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                                    <div>
                                                                        <div class="assigned-customer-name">{{ $assignment->customer_name }}</div>
                                                                        <div class="assigned-customer-meta">{{ $assignment->customer_code ?: 'No code available' }}</div>
                                                                    </div>
                                                                    <span class="badge badge-{{ $assignment->active ? 'success' : 'secondary' }}">{{ $assignment->active ? 'Active' : 'Inactive' }}</span>
                                                                </div>
                                                                <div class="assigned-customer-lines">
                                                                    <div><strong>Email:</strong> {{ $assignment->email ?: 'No email address' }}</div>
                                                                    <div><strong>Phone:</strong> {{ $assignment->telephone1 ?: 'No phone number' }}</div>
                                                                    <div><strong>Account Setting:</strong> {{ $assignment->account_setting ?? 'Not set' }}</div>
                                                                </div>
                                                                <button type="button" class="btn btn-sm btn-outline-danger mt-3" wire:click="removeCustomer(@js($assignment->assignment_id))" onclick="return confirm('Remove this customer assignment?')">
                                                                    <i class="mdi mdi-link-off"></i> Remove Assignment
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="empty-state">
                                                    <i class="mdi mdi-account-multiple-outline"></i>
                                                    <h5>No customers assigned</h5>
                                                    <p>Use the customer assignment panel above to link this pricelist to CRM customers.</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($customerAssignmentTab === 'email-pricelist')
                            <div class="row">
                                <div class="col-12 mb-4">
                                    <div class="card border-0 shadow-sm inner-card h-100">
                                        <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-1">Email Pricelist to Customers</h5>
                                                <p class="text-muted mb-0">Compose once and send to selected assigned customers.</p>
                                            </div>
                                            <span class="badge badge-light status-chip">{{ $emailCustomers->count() }} emailable</span>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex flex-wrap workspace-actions mb-3">
                                                <button type="button" class="btn btn-outline-primary btn-sm" wire:click="selectAllEmailCustomers">
                                                    <i class="mdi mdi-checkbox-marked-outline"></i> Select All
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="clearEmailCustomers">
                                                    <i class="mdi mdi-checkbox-blank-outline"></i> Clear All
                                                </button>
                                            </div>

                                            <div class="recipient-chip-list mb-3">
                                                @forelse($emailCustomers as $recipient)
                                                    <button type="button"
                                                            class="recipient-chip {{ in_array($recipient->customer_id, $emailCustomerIds) ? 'active' : '' }}"
                                                            wire:click="toggleEmailCustomer(@js($recipient->customer_id))">
                                                        <span class="recipient-name">{{ $recipient->customer_name }}</span>
                                                        <span class="recipient-email">{{ $recipient->email }}</span>
                                                    </button>
                                                @empty
                                                    <div class="empty-state compact-empty">
                                                        <i class="mdi mdi-email-off-outline"></i>
                                                        <span>No assigned customers with valid email addresses.</span>
                                                    </div>
                                                @endforelse
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label soft-label">Email Subject <span class="text-danger">*</span></label>
                                                        <input type="text" wire:model="emailSubject" class="form-control modern-input @error('emailSubject') is-invalid @enderror">
                                                        @error('emailSubject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label soft-label">Email Message</label>
                                                        <textarea wire:model="emailMessage" rows="4" class="form-control modern-input @error('emailMessage') is-invalid @enderror"></textarea>
                                                        @error('emailMessage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            @error('emailCustomerIds')
                                                <div class="text-danger small mb-3">{{ $message }}</div>
                                            @enderror

                                            <button type="button" class="btn btn-success modern-primary-btn" wire:click="sendPricelistEmail">
                                                <i class="mdi mdi-send"></i> Send Pricelist Email
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($customerAssignmentTab === 'recent-sends')
                            <div class="row">
                                <div class="col-12 mb-4">
                                    <div class="card border-0 shadow-sm inner-card h-100">
                                        <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-1">Recent Sends</h5>
                                                <p class="text-muted mb-0">Latest 12 attempts for this pricelist.</p>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex flex-wrap mb-3" style="gap: 8px;">
                                                <button type="button"
                                                        class="btn btn-sm log-filter-btn {{ $emailLogFilter === 'all' ? 'active' : '' }}"
                                                        wire:click="setEmailLogFilter('all')">
                                                    All ({{ $emailLogCounts['all'] }})
                                                </button>
                                                <button type="button"
                                                        class="btn btn-sm log-filter-btn {{ $emailLogFilter === 'sent' ? 'active' : '' }}"
                                                        wire:click="setEmailLogFilter('sent')">
                                                    Sent ({{ $emailLogCounts['sent'] }})
                                                </button>
                                                <button type="button"
                                                        class="btn btn-sm log-filter-btn {{ $emailLogFilter === 'failed' ? 'active' : '' }}"
                                                        wire:click="setEmailLogFilter('failed')">
                                                    Failed ({{ $emailLogCounts['failed'] }})
                                                </button>
                                            </div>

                                            @if($recentEmailLogs->count() > 0)
                                                <div class="email-log-list">
                                                    @foreach($recentEmailLogs as $log)
                                                        <div class="email-log-item">
                                                            <div class="d-flex justify-content-between align-items-start">
                                                                <div>
                                                                    <div class="email-log-primary">{{ $log->recipient_email }}</div>
                                                                    <div class="email-log-secondary">
                                                                        {{ $log->customer_name ?: ($log->customer_name_ref ?: 'Unknown Customer') }}
                                                                        @if($log->customer_code)
                                                                            • {{ $log->customer_code }}
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <span class="badge badge-{{ $log->status === 'sent' ? 'success' : 'danger' }}">
                                                                    {{ strtoupper($log->status) }}
                                                                </span>
                                                            </div>
                                                            <div class="email-log-meta mt-2">
                                                                <span>{{ $log->subject }}</span>
                                                                <span>•</span>
                                                                <span>{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i') }}</span>
                                                                @if($log->sender_name)
                                                                    <span>•</span>
                                                                    <span>By {{ $log->sender_name }}</span>
                                                                @endif
                                                                @if($log->has_attachment)
                                                                    <span>•</span>
                                                                    <span>Attachment{{ $log->attachment_name ? ': ' . $log->attachment_name : '' }}</span>
                                                                @endif
                                                            </div>
                                                            @if($log->status === 'failed' && $log->error_message)
                                                                <div class="email-log-error mt-1">{{ $log->error_message }}</div>
                                                            @endif

                                                            @if($log->status === 'failed')
                                                                <div class="email-log-actions mt-2">
                                                                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="retryEmailLog(@js($log->id))">
                                                                        <i class="mdi mdi-refresh"></i> Retry
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="empty-state compact-empty">
                                                    <i class="mdi mdi-history"></i>
                                                    <span>No email sends logged for this pricelist yet.</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    @if($activeTab === 'pricelist-items')
                        <div class="card border-0 shadow-sm inner-card">
                            <div class="card-header border-0 bg-white d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                                <div class="mb-3 mb-lg-0">
                                    <h5 class="mb-1">Pricelist Items</h5>
                                    <p class="text-muted mb-0">Manage item pricing, ordering, and revision-ready changes.</p>
                                </div>
                                <div class="d-flex flex-wrap workspace-actions">
                                    <button type="button" class="btn btn-outline-success action-btn" wire:click="applyPriceChanges">
                                        <i class="mdi mdi-check"></i> Apply Price Changes
                                    </button>
                                    <button type="button" class="btn btn-outline-info action-btn" wire:click="showCloneModal">
                                        <i class="mdi mdi-content-copy"></i> Clone Selected
                                    </button>
                                    <button type="button" class="btn btn-primary action-btn" wire:click="showCreateItemModal">
                                        <i class="mdi mdi-plus"></i> Add Item
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($items->count() > 0)
                                    @php
                                        $pendingItemsCount = $items->filter(fn ($i) => (bool) ($i->has_pending_change ?? false))->count();
                                    @endphp
                                    <div class="pricelist-items-toolbar d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap: 10px;" wire:key="pricelist-items-toolbar">
                                        <div class="pricelist-item-legend">
                                            <span class="legend-label">Item state:</span>
                                            <span class="legend-item">
                                                <span class="legend-swatch legend-swatch--pending"></span>
                                                Uncommitted change (not applied)
                                            </span>
                                        </div>

                                        <div class="pricelist-commit-tabs" role="tablist" aria-label="Pricelist item commit filter">
                                            <button type="button"
                                                    role="tab"
                                                    aria-selected="{{ $itemCommitFilter === 'all' ? 'true' : 'false' }}"
                                                    class="pricelist-commit-tab {{ $itemCommitFilter === 'all' ? 'is-active' : '' }}"
                                                    wire:click="setItemCommitFilter('all')">
                                                <i class="mdi mdi-view-list-outline"></i>
                                                <span>All</span>
                                                <span class="pricelist-commit-tab__count">{{ $items->count() }}</span>
                                            </button>
                                            <button type="button"
                                                    role="tab"
                                                    aria-selected="{{ $itemCommitFilter === 'pending' ? 'true' : 'false' }}"
                                                    class="pricelist-commit-tab {{ $itemCommitFilter === 'pending' ? 'is-active' : '' }}"
                                                    wire:click="setItemCommitFilter('pending')">
                                                <i class="mdi mdi-clock-outline"></i>
                                                <span>Pending</span>
                                                <span class="pricelist-commit-tab__count">{{ $pendingItemsCount }}</span>
                                            </button>
                                            <button type="button"
                                                    role="tab"
                                                    aria-selected="{{ $itemCommitFilter === 'applied' ? 'true' : 'false' }}"
                                                    class="pricelist-commit-tab {{ $itemCommitFilter === 'applied' ? 'is-active' : '' }}"
                                                    wire:click="setItemCommitFilter('applied')">
                                                <i class="mdi mdi-check-circle-outline"></i>
                                                <span>Applied</span>
                                                <span class="pricelist-commit-tab__count">{{ $items->count() - $pendingItemsCount }}</span>
                                            </button>
                                        </div>
                                    </div>

                                    @include('livewire.billing.partials.pricelist-items-filter-bar')

                                    @if($filteredItemsCount > 0)
                                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pricelist-items-pagination-meta" style="gap: 8px;">
                                            <span class="text-muted small">
                                                Showing {{ $this->itemsPageMeta['from'] }} to {{ $this->itemsPageMeta['to'] }} of {{ $this->itemsPageMeta['total'] }} items
                                                @if($hasActiveItemFilters)
                                                    <span class="text-muted">(filtered from {{ $items->count() }} total)</span>
                                                @endif
                                            </span>
                                        </div>
                                    @endif

                                    @if($groupedItems->count() > 0)
                                    <div class="grouped-items-layout">
                                        @foreach($groupedItems as $sampleGroup)
                                            <section class="sample-group-card mb-3">
                                                <header class="sample-group-header">
                                                    <div>
                                                        <div class="sample-group-title">{{ $sampleGroup->sample_type_name }}</div>
                                                        @if($sampleGroup->sample_type_code)
                                                            <div class="sample-group-code">{{ $sampleGroup->sample_type_code }}</div>
                                                        @endif
                                                    </div>
                                                </header>

                                                <div class="sample-group-body">
                                                    @foreach($sampleGroup->analysis_groups as $analysisGroup)
                                                        <div class="analysis-collapse mb-2"
                                                             wire:key="analysis-collapse-{{ $sampleGroup->sample_type_id }}-{{ $analysisGroup->analysis_id }}"
                                                             x-data="{ open: true, openPackages: {} }">
                                                            <button type="button"
                                                                    class="analysis-collapse-summary"
                                                                    @click="open = !open"
                                                                    :aria-expanded="open.toString()">
                                                                <div class="analysis-collapse-meta">
                                                                    <strong>{{ $analysisGroup->analysis_type_name }}</strong>
                                                                    @if($analysisGroup->analysis_type_code)
                                                                        <span class="analysis-code">{{ $analysisGroup->analysis_type_code }}</span>
                                                                    @endif
                                                                </div>
                                                                <div class="analysis-collapse-total">
                                                                    <span>Total Amount</span>
                                                                    <strong>{{ number_format((float) $analysisGroup->total_amount, 2) }}</strong>
                                                                </div>
                                                            </button>

                                                            <div class="table-responsive modern-table-wrap mt-2"
                                                                 x-show="open"
                                                                 x-cloak>
                                                                <table class="table table-hover modern-table mb-0">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="fit-col">Select</th>
                                                                            <th class="fit-col">Actions</th>
                                                                            <th>Analyte</th>
                                                                            <th>Cost Price</th>
                                                                            <th>Selling Price</th>
                                                                            <th>Profit</th>
                                                                            <th>Profit Margin</th>
                                                                            <th>VAT</th>
                                                                            <th>Status</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($analysisGroup->rows as $item)
                                                                            @php
                                                                                $isPackage = ! empty($item->is_package);
                                                                                $packageItemId = (string) $item->id;
                                                                                $packageParameters = is_array($item->package_parameters ?? null) ? $item->package_parameters : [];
                                                                                $colspan = 9;
                                                                            @endphp
                                                                            <tr class="{{ $item->has_pending_change ? 'pricelist-item-row--uncommitted' : '' }}"
                                                                                @if($isPackage)
                                                                                    :class="{ 'pricelist-item-row--package-open': !!openPackages[@js($packageItemId)] }"
                                                                                @endif>
                                                                                <td class="fit-col">
                                                                                    <input type="checkbox" wire:model.live="selectedItemIds" value="{{ $item->id }}">
                                                                                </td>
                                                                                <td class="fit-col pricelist-actions-cell">
                                                                                    <div class="pricelist-row-actions">
                                                                                        @if($isPackage)
                                                                                            <button type="button"
                                                                                                    class="btn btn-sm rm-act-btn rm-act-btn--view"
                                                                                                    @click.stop="openPackages[@js($packageItemId)] = !openPackages[@js($packageItemId)]"
                                                                                                    :title="openPackages[@js($packageItemId)] ? 'Hide package parameters' : 'View package parameters'">
                                                                                                <i class="mdi"
                                                                                                   :class="openPackages[@js($packageItemId)] ? 'mdi-chevron-up' : 'mdi-eye'"></i>
                                                                                            </button>
                                                                                        @endif
                                                                                        <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--edit" wire:click="showEditItemModal(@js($item->id))" title="Edit item">
                                                                                            <i class="mdi mdi-pencil-outline"></i>
                                                                                        </button>
                                                                                        <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--delete" wire:click="openDeleteItemConfirmModal(@js($item->id))" title="Delete item">
                                                                                            <i class="mdi mdi-delete"></i>
                                                                                        </button>
                                                                                    </div>
                                                                                </td>
                                                                                <td>
                                                                                    <div class="table-primary-line">
                                                                                        @if($isPackage)
                                                                                            <button type="button"
                                                                                                    class="pricelist-package-toggle"
                                                                                                    @click.stop="openPackages[@js($packageItemId)] = !openPackages[@js($packageItemId)]"
                                                                                                    :title="openPackages[@js($packageItemId)] ? 'Hide package parameters' : 'View package parameters'">
                                                                                                <i class="mdi"
                                                                                                   :class="openPackages[@js($packageItemId)] ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
                                                                                                <span>{{ $item->analyte_name ?? 'N/A' }}</span>
                                                                                            </button>
                                                                                            <span class="badge badge-primary badge-pill ml-1">Package</span>
                                                                                        @else
                                                                                            {{ $item->analyte_name ?? 'N/A' }}
                                                                                        @endif
                                                                                    </div>
                                                                                    @if($item->analyte_code)
                                                                                        <div class="table-secondary-line">{{ $item->analyte_code }}</div>
                                                                                    @endif
                                                                                </td>
                                                                                <td>{{ number_format((float) ($item->cost_price ?? 0), 2) }}</td>
                                                                                <td>
                                                                                    {{ number_format((float) ($item->display_selling_price ?? $item->selling_price ?? 0), 2) }}
                                                                                    @if($item->has_pending_change)
                                                                                        <span class="badge badge-warning badge-pill ml-1" title="Applied price: {{ number_format((float) ($item->applied_selling_price ?? $item->selling_price ?? 0), 2) }}">Pending</span>
                                                                                    @endif
                                                                                </td>
                                                                                <td>{{ number_format((float) ($item->profit ?? 0), 2) }}</td>
                                                                                <td>{{ number_format((float) ($item->profit_margin ?? 0), 2) }}%</td>
                                                                                <td>
                                                                                    <span class="badge badge-{{ $item->vat ? 'info' : 'light' }}">{{ $item->vat ? 'Yes' : 'No' }}</span>
                                                                                </td>
                                                                                <td>
                                                                                    @if($item->has_pending_change)
                                                                                        <span class="badge badge-warning mr-1">Pending</span>
                                                                                    @endif
                                                                                    <span class="badge badge-{{ $item->active ? 'success' : 'danger' }}">{{ $item->active ? 'Active' : 'Inactive' }}</span>
                                                                                </td>
                                                                            </tr>
                                                                            @if($isPackage)
                                                                                <tr class="pricelist-package-params-row"
                                                                                    x-show="!!openPackages[@js($packageItemId)]"
                                                                                    x-cloak>
                                                                                    <td colspan="{{ $colspan }}">
                                                                                        <div class="pricelist-package-params">
                                                                                            <div class="pricelist-package-params__title">
                                                                                                Package parameters ({{ count($packageParameters) }})
                                                                                            </div>
                                                                                            @if($packageParameters !== [])
                                                                                                <ul class="pricelist-package-params__list">
                                                                                                    @foreach($packageParameters as $parameter)
                                                                                                        <li>
                                                                                                            <span class="pricelist-package-params__code">{{ ($parameter['code'] ?? '') !== '' ? $parameter['code'] : ($parameter['name'] ?? 'Parameter') }}</span>
                                                                                                        </li>
                                                                                                    @endforeach
                                                                                                </ul>
                                                                                            @else
                                                                                                <p class="mb-0 text-muted small">No parameters are linked to this package.</p>
                                                                                            @endif
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                            @endif
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </section>
                                        @endforeach
                                    </div>
                                    @else
                                        <div class="compact-empty pricelist-filter-empty">
                                            <i class="mdi mdi-filter-variant"></i>
                                            @if($hasActiveItemFilters)
                                                <p class="mb-2"><strong>No items match your filters.</strong> Try adjusting search or filter criteria.</p>
                                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="clearItemFilters">
                                                    <i class="mdi mdi-filter-off-outline"></i> Clear filters
                                                </button>
                                            @elseif($itemCommitFilter === 'pending')
                                                <p class="mb-0"><strong>No pending items.</strong> Nothing has an uncommitted price change, or adjust prices and save before applying.</p>
                                            @elseif($itemCommitFilter === 'applied')
                                                <p class="mb-0"><strong>No applied-only rows.</strong> All items may still show as pending until you apply price changes.</p>
                                            @else
                                                <p class="mb-0">No line items match the current view.</p>
                                            @endif
                                        </div>
                                    @endif

                                    @if($groupedItems->count() > 0 && $itemPaginator->hasPages())
                                        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pricelist-items-pagination-meta" style="gap: 8px;">
                                            <span class="text-muted small d-none d-md-inline">
                                                Page {{ $itemPaginator->currentPage() }} of {{ $itemPaginator->lastPage() }}
                                            </span>
                                            <nav aria-label="Pricelist items pagination">
                                                <ul class="pagination pagination-sm mb-0">
                                                    <li class="page-item {{ $itemPaginator->onFirstPage() ? 'disabled' : '' }}">
                                                        <button type="button" class="page-link" wire:click="previousItemsPage" @disabled($itemPaginator->onFirstPage())>Previous</button>
                                                    </li>
                                                    @foreach($itemPaginator->getUrlRange(1, $itemPaginator->lastPage()) as $page => $url)
                                                        <li class="page-item {{ $page === $itemPaginator->currentPage() ? 'active' : '' }}">
                                                            <button type="button" class="page-link" wire:click="goToItemsPage({{ $page }})">{{ $page }}</button>
                                                        </li>
                                                    @endforeach
                                                    <li class="page-item {{ $itemPaginator->onLastPage() ? 'disabled' : '' }}">
                                                        <button type="button" class="page-link" wire:click="nextItemsPage" @disabled($itemPaginator->onLastPage())>Next</button>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    @endif
                                @else
                                    <div class="empty-state">
                                        <i class="mdi mdi-format-list-bulleted-square"></i>
                                        <h5>No pricelist items found</h5>
                                        <p>Add the first line item to start building this pricelist.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($showItemModal)
            <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.55);">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content item-modal-content" wire:click.stop>
                        <div class="modal-header item-modal-header border-0">
                            <div>
                                <span class="item-modal-kicker">Pricing Item</span>
                                <h5 class="modal-title mb-1">{{ $editingItem ? 'Edit' : 'Add' }} Pricelist Item</h5>
                                <p class="mb-0 text-muted">Define sample, analysis, and pricing flags in one place.</p>
                            </div>
                            <button type="button" class="btn-close" wire:click="closeItemModal"></button>
                        </div>
                        <div class="modal-body item-modal-body">
                            <div class="item-modal-section mb-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label item-modal-label">Sample Type <span class="text-danger">*</span></label>
                                            <div class="item-tag-select tag-select-container" wire:click="setItemSampleTypeDropdown(true)">
                                                <div class="tag-select-input item-tag-select-input">
                                                    @if(!empty($itemForm['sample_type_id']))
                                                        @php $selectedSt = $sampleTypes->firstWhere('id', $itemForm['sample_type_id']); @endphp
                                                        @if($selectedSt)
                                                            <span class="tag-badge">
                                                                {{ $selectedSt->code }} — {{ $selectedSt->name }}
                                                                <i class="mdi mdi-close-circle" wire:click.stop="clearItemSampleType" role="button" tabindex="0"></i>
                                                            </span>
                                                        @endif
                                                    @else
                                                        <input type="text"
                                                            wire:model.live.debounce.300ms="itemSampleTypeSearch"
                                                            class="tag-input"
                                                            placeholder="Search sample types..."
                                                            autocomplete="off">
                                                    @endif
                                                </div>
                                                @if($showItemSampleTypeDropdown && $this->filteredItemSampleTypes->isNotEmpty())
                                                    <div class="tag-dropdown">
                                                        @foreach($this->filteredItemSampleTypes as $sampleType)
                                                            <div class="tag-dropdown-item" wire:click.stop="selectItemSampleType('{{ $sampleType->id }}')">
                                                                <span class="tag-dropdown-code">{{ $sampleType->code }}</span>
                                                                <span class="tag-dropdown-name">{{ $sampleType->name }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @elseif($showItemSampleTypeDropdown)
                                                    <div class="tag-dropdown tag-dropdown--empty text-muted small">No matching sample types.</div>
                                                @endif
                                            </div>
                                            @error('itemForm.sample_type_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label item-modal-label">Analysis Type <span class="text-danger">*</span></label>
                                            <div class="item-tag-select tag-select-container" wire:click="setItemAnalysisTypeDropdown(true)">
                                                <div class="tag-select-input item-tag-select-input {{ empty($itemForm['sample_type_id']) ? 'item-tag-select-input--disabled' : '' }}">
                                                    @if(empty($itemForm['sample_type_id']))
                                                        <span class="tag-input-placeholder text-muted">Select a sample type first…</span>
                                                    @elseif(!empty($itemForm['analysis_id']))
                                                        @php $selectedAt = $availableAnalysisTypes->firstWhere('id', $itemForm['analysis_id']); @endphp
                                                        @if($selectedAt)
                                                            <span class="tag-badge">
                                                                {{ $selectedAt->code }} — {{ $selectedAt->name }}
                                                                <i class="mdi mdi-close-circle" wire:click.stop="clearItemAnalysisType" role="button" tabindex="0"></i>
                                                            </span>
                                                        @endif
                                                    @else
                                                        <input type="text"
                                                            wire:model.live.debounce.300ms="itemAnalysisTypeSearch"
                                                            class="tag-input"
                                                            placeholder="Search analysis types..."
                                                            autocomplete="off">
                                                    @endif
                                                </div>
                                                @if($showItemAnalysisTypeDropdown && !empty($itemForm['sample_type_id']) && $this->filteredItemAnalysisTypes->isNotEmpty())
                                                    <div class="tag-dropdown">
                                                        @foreach($this->filteredItemAnalysisTypes as $analysisType)
                                                            <div class="tag-dropdown-item" wire:click.stop="selectItemAnalysisType('{{ $analysisType->id }}')">
                                                                <span class="tag-dropdown-code">{{ $analysisType->code }}</span>
                                                                <span class="tag-dropdown-name">{{ $analysisType->name }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @elseif($showItemAnalysisTypeDropdown && !empty($itemForm['sample_type_id']))
                                                    <div class="tag-dropdown tag-dropdown--empty text-muted small">No matching analysis types for this sample type.</div>
                                                @endif
                                            </div>
                                            @error('itemForm.analysis_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="item-modal-section mb-3">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-2" style="gap: 8px;">
                                    <div class="item-element-heading mb-0">
                                        {{ !empty($itemForm['is_package']) ? 'Package Elements' : 'Analysis Elements Pricing' }}
                                    </div>
                                    <div class="flag-tile py-1 px-2 mb-0">
                                        <div class="form-check form-switch mb-0">
                                            <input type="checkbox" wire:model.live="itemForm.is_package" class="form-check-input" role="switch" id="item-form-is-package">
                                            <label class="form-check-label" for="item-form-is-package">Package</label>
                                        </div>
                                    </div>
                                </div>

                                @if(!empty($itemForm['is_package']))
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label item-modal-label">Package Cost Price <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" wire:model.blur="itemForm.package_cost_price" class="form-control item-modal-input @error('itemForm.package_cost_price') is-invalid @enderror">
                                            @error('itemForm.package_cost_price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label item-modal-label">Package Price <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" wire:model.blur="itemForm.package_selling_price" class="form-control item-modal-input @error('itemForm.package_selling_price') is-invalid @enderror">
                                            @error('itemForm.package_selling_price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <div class="flag-tile w-100">
                                                <div class="form-check form-switch mb-0">
                                                    <input type="checkbox" wire:model="itemForm.package_vat" class="form-check-input" role="switch" id="item-form-package-vat">
                                                    <label class="form-check-label" for="item-form-package-vat">Has VAT</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="small text-muted mb-2">Tick the analysis elements covered by this package. Membership is saved immediately; package price stays pending until Apply Price Changes.</p>
                                @endif

                                @if(count($itemElementRows) > 0)
                                    <div class="table-responsive modern-table-wrap">
                                        <table class="table table-hover modern-table mb-0">
                                            <thead>
                                                <tr>
                                                    @if(!empty($itemForm['is_package']))
                                                        <th style="width: 4rem;">Include</th>
                                                        <th>Analyte</th>
                                                    @else
                                                        <th>Analyte</th>
                                                        <th>Cost Price</th>
                                                        <th>Changed Price</th>
                                                        <th>Has VAT</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($itemElementRows as $index => $row)
                                                    <tr>
                                                        @if(!empty($itemForm['is_package']))
                                                            <td>
                                                                <div class="form-check form-switch">
                                                                    <input type="checkbox" wire:model="itemElementRows.{{ $index }}.included" class="form-check-input" role="switch">
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="table-primary-line">{{ $row['analyte_label'] ?: 'N/A' }}</div>
                                                            </td>
                                                        @else
                                                            <td>
                                                                <div class="table-primary-line">{{ $row['analyte_label'] ?: 'N/A' }}</div>
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.01" wire:model.blur="itemElementRows.{{ $index }}.cost_price" class="form-control item-modal-input @error('itemElementRows.' . $index . '.cost_price') is-invalid @enderror">
                                                                @error('itemElementRows.' . $index . '.cost_price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.01" wire:model.blur="itemElementRows.{{ $index }}.selling_price" class="form-control item-modal-input @error('itemElementRows.' . $index . '.selling_price') is-invalid @enderror">
                                                                @error('itemElementRows.' . $index . '.selling_price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                                            </td>
                                                            <td>
                                                                <div class="form-check form-switch">
                                                                    <input type="checkbox" wire:model="itemElementRows.{{ $index }}.vat" class="form-check-input" role="switch">
                                                                </div>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="compact-empty">
                                        <i class="mdi mdi-flask-empty-outline"></i>
                                        <span>Select sample type and analysis type to load analysis elements.</span>
                                    </div>
                                @endif

                                @error('itemElementRows')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="item-modal-section">
                                <label class="form-label item-modal-label d-block">Flags</label>
                                <div class="row">
                                    <div class="col-md-4 col-6 mb-2">
                                        <div class="flag-tile">
                                            <div class="form-check form-switch">
                                                <input type="checkbox" wire:model="itemForm.internal_use" class="form-check-input" role="switch">
                                                <label class="form-check-label">Internal</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-6 mb-2">
                                        <div class="flag-tile">
                                            <div class="form-check form-switch">
                                                <input type="checkbox" wire:model="itemForm.external_view" class="form-check-input" role="switch">
                                                <label class="form-check-label">External</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-6 mb-2">
                                        <div class="flag-tile">
                                            <div class="form-check form-switch">
                                                <input type="checkbox" wire:model="itemForm.active" class="form-check-input" role="switch">
                                                <label class="form-check-label">Active</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="item-modal-note mt-3">
                                <i class="mdi mdi-information-outline"></i>
                                New and updated prices are saved as pending until you use Apply Price Changes on the pricelist items tab.
                            </div>
                        </div>
                        <div class="modal-footer border-0 item-modal-footer flex-column align-items-stretch">
                            @if ($errors->any())
                                <div class="alert alert-danger py-2 px-3 mb-2 w-100 small">
                                    Please fix the highlighted fields before saving.
                                </div>
                            @endif
                            <div class="d-flex justify-content-end w-100" style="gap: 8px;">
                            <button type="button" class="btn btn-light item-modal-cancel-btn" wire:click="closeItemModal">Cancel</button>
                            <button type="button" class="btn btn-primary item-modal-save-btn" wire:click="saveItem" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="saveItem"><i class="mdi mdi-content-save"></i> Save Item</span>
                                <span wire:loading wire:target="saveItem"><span class="spinner-border spinner-border-sm mr-1"></span> Saving…</span>
                            </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($showCloneModal)
            <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Clone Selected Items</h5>
                            <button type="button" class="btn-close" wire:click="$set('showCloneModal', false)"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="form-label">New Pricelist Description <span class="text-danger">*</span></label>
                                <input type="text" wire:model="cloneForm.description" class="form-control @error('cloneForm.description') is-invalid @enderror">
                                @error('cloneForm.description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Currency <span class="text-danger">*</span></label>
                                <select wire:model="cloneForm.currency_id" class="form-select @error('cloneForm.currency_id') is-invalid @enderror">
                                    <option value="">Select currency</option>
                                    @foreach($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->code }} - {{ $currency->description }}</option>
                                    @endforeach
                                </select>
                                @error('cloneForm.currency_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Valid Till</label>
                                <input type="date" wire:model="cloneForm.valid_till" class="form-control @error('cloneForm.valid_till') is-invalid @enderror">
                                @error('cloneForm.valid_till') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-check form-switch">
                                <input type="checkbox" wire:model="cloneForm.is_master" class="form-check-input" role="switch">
                                <label class="form-check-label">Mark as master</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showCloneModal', false)">Cancel</button>
                            <button type="button" class="btn btn-primary" wire:click="cloneSelectedItems"><i class="mdi mdi-content-copy"></i> Clone</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($showDeleteItemConfirmModal)
            @include('livewire.billing.partials.delete-pricelist-item-confirm-modal', [
                'preview' => $pendingDeleteItemPreview,
            ])
        @endif
    @endif

    <style>
        [x-cloak] {
            display: none !important;
        }

        .pricelist-show-page {
            --page-bg: linear-gradient(180deg, #f4f7fb 0%, #eef3f8 100%);
            --panel-border: rgba(148, 163, 184, 0.16);
            --panel-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            --slate-900: #0f172a;
            --slate-700: #334155;
            --slate-500: #64748b;
            --slate-200: #e2e8f0;
            --blue-600: #2563eb;
            --blue-500: #3b82f6;
            --teal-500: #14b8a6;
            --surface: #ffffff;
            background: var(--page-bg);
            border-radius: 22px;
            padding: 8px;
        }

        .modal.show {
            display: block !important;
            position: fixed;
            inset: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        body:has(.pricelist-show-page.modal-active) {
            overflow: hidden;
        }

        .item-modal-content {
            border: 0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(15, 23, 42, 0.3);
        }

        .item-modal-header {
            padding: 20px 24px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
        }

        .item-modal-kicker {
            display: inline-block;
            font-size: 11px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 5px 10px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .item-modal-body {
            padding: 22px 24px 10px;
            background: #f8fafc;
        }

        .item-modal-section {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px 14px 4px;
        }

        .item-modal-label {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .item-modal-input {
            min-height: 44px;
            border-radius: 10px;
            border: 1px solid #dbe3ef;
            box-shadow: none;
        }

        .item-modal-input:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 0.18rem rgba(59, 130, 246, 0.15);
        }

        /* Tag select (Add / Edit pricelist item modal) */
        .item-modal-section .item-tag-select.tag-select-container {
            position: relative;
            cursor: text;
        }

        .item-modal-section .item-tag-select-input {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            min-height: 44px;
            padding: 6px 12px;
            background: #fff;
            border: 1px solid #dbe3ef;
            border-radius: 10px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .item-modal-section .item-tag-select-input:focus-within {
            border-color: #93c5fd;
            box-shadow: 0 0 0 0.18rem rgba(59, 130, 246, 0.12);
        }

        .item-modal-section .item-tag-select-input--disabled {
            background: #f1f5f9;
            cursor: not-allowed;
        }

        .item-modal-section .tag-input-placeholder {
            font-size: 13px;
            padding: 4px 2px;
        }

        .item-modal-section .tag-input {
            flex: 1;
            min-width: 120px;
            border: none;
            outline: none;
            padding: 4px;
            font-size: 14px;
            background: transparent;
        }

        .item-modal-section .tag-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            background: #2563eb;
            color: #fff;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            max-width: 100%;
        }

        .item-modal-section .tag-badge i {
            cursor: pointer;
            font-size: 1rem;
            opacity: 0.85;
        }

        .item-modal-section .tag-badge i:hover {
            opacity: 1;
        }

        .item-modal-section .tag-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #93c5fd;
            border-top: none;
            border-radius: 0 0 10px 10px;
            max-height: 220px;
            overflow-y: auto;
            z-index: 1080;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.1);
            margin-top: -1px;
        }

        .item-modal-section .tag-dropdown--empty {
            padding: 12px 14px;
        }

        .item-modal-section .tag-dropdown-item {
            padding: 10px 14px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 8px;
            transition: background 0.15s ease;
        }

        .item-modal-section .tag-dropdown-item:hover {
            background: #f8fafc;
        }

        .item-modal-section .tag-dropdown-item:last-child {
            border-bottom: none;
        }

        .item-modal-section .tag-dropdown-code {
            font-size: 11px;
            font-weight: 700;
            color: #1d4ed8;
            background: #dbeafe;
            border-radius: 6px;
            padding: 2px 8px;
        }

        .item-modal-section .tag-dropdown-name {
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
        }

        .flag-tile {
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 10px 12px;
            background: #f8fafc;
            min-height: 44px;
        }

        .flag-tile .form-check-label {
            color: #334155;
            font-weight: 600;
        }

        .item-modal-note {
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1e3a8a;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 13px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .item-modal-note i {
            font-size: 16px;
            margin-top: 1px;
        }

        .item-modal-footer {
            background: #f8fafc;
            padding: 14px 24px 20px;
        }

        .item-modal-cancel-btn,
        .item-modal-save-btn {
            border-radius: 10px;
            min-height: 42px;
            font-weight: 600;
            padding: 0 16px;
        }

        .page-header-card {
            border-radius: 15px;
        }

        .page-header-title {
            color: var(--slate-900);
        }

        .page-header-main {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .page-title-status {
            margin-top: 2px;
        }

        .page-header-subtitle {
            margin-top: 4px;
        }

        .page-back-btn {
            border-radius: 12px;
            font-weight: 600;
        }

        .metric-card,
        .workspace-card,
        .inner-card,
        .assigned-customer-card {
            border-radius: 18px;
            border: 1px solid var(--panel-border);
            box-shadow: var(--panel-shadow);
        }

        .metric-card .card-body {
            padding: 22px;
        }

        .metric-label {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--slate-500);
            margin-bottom: 10px;
        }

        .metric-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--slate-900);
            line-height: 1.1;
        }

        .metric-meta {
            margin-top: 8px;
            color: var(--slate-500);
            font-size: 13px;
        }

        .workspace-tabs {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 6px;
            padding: 6px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }

        .workspace-tab {
            display: inline-flex;
            align-items: center;
            border: 1px solid transparent;
            background: transparent;
            color: #64748b;
            padding: 0.6rem 1.1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            line-height: 1.25;
            transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
            cursor: pointer;
        }

        .workspace-tab:hover {
            background: #fff;
            border-color: var(--color-primary-border-soft, #e2b4b4);
            color: var(--color-primary, #6D0A0E);
        }

        .workspace-tab.active {
            background: var(--color-primary, #6D0A0E);
            border-color: var(--color-primary, #6D0A0E);
            color: #fff;
            box-shadow: 0 1px 3px rgb(109 10 14 / 0.28);
        }

        .workspace-tab.active:hover {
            background: var(--color-primary-hover, #8B1E22);
            border-color: var(--color-primary-hover, #8B1E22);
            color: #fff;
        }

        .workspace-tab.active .mdi {
            color: #fff;
        }

        .soft-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--slate-500);
        }

        .modern-input {
            min-height: 46px;
            border-radius: 12px;
            border: 1px solid #dbe3ef;
            box-shadow: none;
        }

        .modern-input:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 0.18rem rgba(59, 130, 246, 0.14);
        }

        .modern-primary-btn,
        .modern-secondary-btn,
        .action-btn {
            min-height: 44px;
            border-radius: 12px;
            font-weight: 600;
        }

        .status-chip {
            border-radius: 999px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 700;
            color: var(--slate-700);
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .profile-strip {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-top: 8px;
        }

        .profile-strip-item {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px;
        }

        .profile-strip-label {
            display: block;
            color: var(--slate-500);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
        }

        .switch-stack .form-check-label {
            font-weight: 600;
            color: var(--slate-700);
        }

        .customer-pick-list {
            display: grid;
            gap: 10px;
        }

        .chip-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .picker-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(180deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e3a8a;
            border: 1px solid #93c5fd;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 700;
        }

        .picker-chip small {
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 600;
        }

        .chip-close {
            border: 0;
            background: transparent;
            color: #1d4ed8;
            padding: 0;
            line-height: 1;
            display: inline-flex;
            align-items: center;
        }

        .customer-pick-item,
        .assigned-customer-card {
            background: #fff;
            padding: 16px;
        }

        .customer-pick-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
        }

        .customer-name,
        .assigned-customer-name,
        .table-primary-line {
            font-weight: 700;
            color: var(--slate-900);
        }

        .customer-meta,
        .assigned-customer-meta,
        .assigned-customer-lines,
        .table-secondary-line {
            color: var(--slate-500);
            font-size: 13px;
        }

        .assigned-customer-lines > div + div {
            margin-top: 4px;
        }

        .doc-panel {
            border: 1px dashed #94a3b8;
            border-radius: 14px;
            padding: 12px;
            background: #f8fafc;
        }

        .doc-panel-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--slate-500);
            margin-bottom: 6px;
            font-weight: 700;
        }

        .doc-panel-name {
            font-weight: 700;
            color: var(--slate-900);
            word-break: break-all;
            margin-bottom: 2px;
        }

        .doc-panel-meta {
            font-size: 12px;
            color: var(--slate-500);
        }

        .recipient-chip-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .recipient-chip {
            border: 1px solid #dbe3ef;
            border-radius: 12px;
            padding: 8px 10px;
            background: #fff;
            text-align: left;
            min-width: 190px;
            transition: all 0.2s ease;
        }

        .recipient-chip.active {
            border-color: #60a5fa;
            background: #eff6ff;
            box-shadow: 0 8px 16px rgba(59, 130, 246, 0.12);
        }

        .recipient-name {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--slate-900);
        }

        .recipient-email {
            display: block;
            font-size: 11px;
            color: var(--slate-500);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .email-log-list {
            display: grid;
            gap: 10px;
            max-height: 320px;
            overflow: auto;
            padding-right: 2px;
        }

        .log-filter-btn {
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #334155;
            font-weight: 600;
        }

        .log-filter-btn.active {
            background: #dbeafe;
            color: #1e3a8a;
            border-color: #93c5fd;
        }

        .email-log-item {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 12px;
            background: #fff;
        }

        .email-log-primary {
            font-size: 13px;
            font-weight: 700;
            color: var(--slate-900);
        }

        .email-log-secondary,
        .email-log-meta {
            color: var(--slate-500);
            font-size: 11px;
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .email-log-error {
            color: #b91c1c;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 6px 8px;
            font-size: 11px;
        }

        .email-log-actions {
            display: flex;
            justify-content: flex-end;
        }

        .workspace-actions {
            gap: 10px;
        }

        .assignment-tabs-wrap {
            padding-bottom: 0;
            border-bottom: none;
        }

        .assignment-tabs {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 6px;
            padding: 4px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }

        .assignment-tab {
            border: 1px solid transparent;
            background: transparent;
            color: #64748b;
            padding: 0.45rem 0.9rem;
            border-radius: 8px;
            font-size: 0.8125rem;
            font-weight: 600;
            transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
            cursor: pointer;
        }

        .assignment-tab:hover {
            background: #fff;
            border-color: var(--color-primary-border-soft, #e2b4b4);
            color: var(--color-primary, #6D0A0E);
        }

        .assignment-tab.active {
            border-color: var(--color-primary, #6D0A0E);
            color: #fff;
            background: var(--color-primary, #6D0A0E);
            box-shadow: 0 1px 3px rgb(109 10 14 / 0.28);
        }

        .assignment-tab.active:hover {
            background: var(--color-primary-hover, #8B1E22);
            border-color: var(--color-primary-hover, #8B1E22);
            color: #fff;
        }

        .modern-table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
        }

        .pricelist-item-legend {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 8px 10px;
            border: 1px solid #f1f5f9;
            border-radius: 10px;
            background: #ffffff;
            font-size: 12px;
            color: #475569;
        }

        .legend-label {
            font-weight: 700;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .legend-swatch {
            display: inline-block;
            width: 14px;
            height: 14px;
            border-radius: 3px;
        }

        .legend-swatch--pending {
            background: #f59e0b;
        }

        .item-filter-tabs,
        .pricelist-commit-tabs {
            display: inline-flex;
            gap: 6px;
            align-items: center;
            flex-wrap: wrap;
            padding: 4px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }

        .item-filter-tab,
        .pricelist-commit-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid transparent;
            background: transparent;
            color: #475569;
            border-radius: 8px;
            font-size: 0.8125rem;
            font-weight: 600;
            line-height: 1.25;
            padding: 0.45rem 0.85rem;
            transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
            cursor: pointer;
        }

        .item-filter-tab:hover,
        .pricelist-commit-tab:hover {
            background: #fff;
            border-color: var(--color-primary-border-soft, #e2b4b4);
            color: var(--color-primary, #6D0A0E);
        }

        .item-filter-tab.active,
        .pricelist-commit-tab.is-active {
            background: var(--color-primary, #6D0A0E);
            border-color: var(--color-primary, #6D0A0E);
            color: #fff;
            box-shadow: 0 1px 3px rgb(109 10 14 / 0.28);
        }

        .item-filter-tab.active:hover,
        .pricelist-commit-tab.is-active:hover {
            background: var(--color-primary-hover, #8B1E22);
            border-color: var(--color-primary-hover, #8B1E22);
            color: #fff;
        }

        .pricelist-commit-tab .mdi {
            font-size: 1rem;
            line-height: 1;
        }

        .item-filter-tab-count,
        .pricelist-commit-tab__count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.35rem;
            padding: 2px 6px;
            border-radius: 999px;
            font-size: 0.6875rem;
            font-weight: 700;
            background: #e2e8f0;
            color: #475569;
        }

        .pricelist-commit-tab.is-active .pricelist-commit-tab__count,
        .item-filter-tab.active .item-filter-tab-count {
            background: rgba(255, 255, 255, 0.22);
            color: #fff;
        }

        .grouped-items-layout {
            display: grid;
            gap: 12px;
        }

        .sample-group-card {
            border: 1px solid #dbe3ef;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .sample-group-header {
            padding: 14px 16px;
            border-bottom: 1px solid #edf2f7;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 16px 16px 0 0;
        }

        .sample-group-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--slate-900);
        }

        .sample-group-code {
            font-size: 12px;
            color: var(--slate-500);
        }

        .sample-group-body {
            padding: 10px 10px 2px;
        }

        .analysis-collapse {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fdfefe;
            overflow: hidden;
        }

        .analysis-collapse-summary {
            list-style: none;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            background: #f8fafc;
            width: 100%;
            border: 0;
            text-align: left;
        }

        .analysis-collapse-summary:focus {
            outline: 2px solid #93c5fd;
            outline-offset: -2px;
        }

        .analysis-collapse-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            color: var(--slate-900);
        }

        .analysis-code {
            font-size: 11px;
            font-weight: 700;
            color: #1d4ed8;
            background: #dbeafe;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            padding: 3px 8px;
        }

        .analysis-collapse-total {
            text-align: right;
            color: var(--slate-500);
            font-size: 12px;
        }

        .analysis-collapse-total strong {
            display: block;
            color: var(--slate-900);
            font-size: 15px;
        }

        .item-element-heading {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--slate-500);
        }

        .modern-table thead th {
            border-top: 0;
            background: #f8fafc;
            color: var(--slate-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 11px;
            font-weight: 700;
        }

        .modern-table td,
        .modern-table th {
            vertical-align: middle;
            border-color: #eef2f7;
        }

        .pricelist-items-toolbar {
            flex-shrink: 0;
        }

        .pricelist-items-filter-bar {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px;
            background: #ffffff;
        }

        .pricelist-items-filter-advanced {
            border-top: 1px solid #f1f5f9;
        }

        .pricelist-items-filter-bar .item-tag-select.tag-select-container {
            position: relative;
            cursor: text;
        }

        .pricelist-items-filter-bar .tag-select-input {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            min-height: 38px;
            padding: 6px 10px;
            background: #fff;
            border: 1px solid #dbe3ef;
            border-radius: 10px;
        }

        .pricelist-items-filter-bar .tag-select-input:focus-within {
            border-color: #93c5fd;
            box-shadow: 0 0 0 0.18rem rgba(59, 130, 246, 0.12);
        }

        .pricelist-items-filter-bar .tag-input {
            flex: 1;
            min-width: 80px;
            border: none;
            outline: none;
            padding: 2px 4px;
            font-size: 13px;
            background: transparent;
        }

        .pricelist-items-filter-bar .tag-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            background: #2563eb;
            color: #fff;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .pricelist-items-filter-bar .tag-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 20;
            background: #fff;
            border: 1px solid #93c5fd;
            border-radius: 0 0 10px 10px;
            max-height: 220px;
            overflow-y: auto;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.1);
        }

        .pricelist-items-filter-bar .tag-dropdown-item {
            padding: 8px 12px;
            cursor: pointer;
            display: flex;
            align-items: baseline;
            gap: 8px;
            border-bottom: 1px solid #f1f5f9;
        }

        .pricelist-items-filter-bar .tag-dropdown-item:hover {
            background: #f8fafc;
        }

        .pricelist-more-filters-btn {
            min-height: 38px;
            font-weight: 600;
        }

        .pricelist-items-pagination-meta .pagination .page-link {
            border-radius: 8px;
            margin: 0 2px;
        }

        .tag-dropdown-item--selected {
            background: #eff6ff;
        }

        .pricelist-filter-tag-select.is-locked {
            cursor: not-allowed;
        }

        .pricelist-items-filter-bar .item-tag-select-input--disabled {
            background: #f1f5f9;
            cursor: not-allowed;
        }

        .pricelist-items-filter-bar .tag-input-placeholder {
            font-size: 13px;
            padding: 4px 2px;
            display: block;
            width: 100%;
        }

        .pricelist-filter-fields-panel {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: linear-gradient(180deg, #fafbfc 0%, #ffffff 100%);
            padding: 14px 16px;
        }

        .pricelist-filter-fields-panel__header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        .pricelist-filter-fields-panel__hint {
            font-size: 12px;
        }

        .pricelist-filter-fields-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        @media (max-width: 991.98px) {
            .pricelist-filter-fields-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .pricelist-filter-fields-grid {
                grid-template-columns: 1fr;
            }
        }

        .pricelist-filter-field-tile {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .pricelist-filter-field-tile__label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #64748b;
            margin: 0;
        }

        .pricelist-filter-field-tile select {
            min-height: 38px;
        }

        .pricelist-item-row--uncommitted {
            background: linear-gradient(90deg, rgba(245, 158, 11, 0.14) 0%, #fffbeb 10px, #fffbeb 100%);
        }

        .pricelist-item-row--uncommitted td {
            border-color: rgba(245, 158, 11, 0.45) !important;
            border-top-width: 1px;
            border-bottom-width: 1px;
        }

        .pricelist-item-row--uncommitted td:first-child {
            box-shadow: inset 4px 0 0 #ea580c;
        }

        .pricelist-actions-cell {
            vertical-align: middle;
        }

        .pricelist-row-actions {
            display: inline-flex;
            flex-wrap: nowrap;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .pricelist-row-actions .rm-act-btn {
            margin-right: 0;
            padding: 2px 6px;
            min-width: 30px;
            line-height: 1;
        }

        .pricelist-row-actions .rm-act-btn .mdi {
            font-size: 16px;
            vertical-align: middle;
        }

        .pricelist-package-toggle {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: 0;
            background: transparent;
            color: inherit;
            font-weight: 700;
            padding: 0;
            cursor: pointer;
            text-align: left;
        }

        .pricelist-package-toggle:hover {
            color: var(--color-primary, #6D0A0E);
        }

        .pricelist-package-toggle .mdi {
            font-size: 1rem;
            line-height: 1;
            color: var(--color-primary, #6D0A0E);
        }

        .pricelist-package-params-row > td {
            background: #f8fafc;
            border-top: 0 !important;
            padding-top: 0.35rem;
            padding-bottom: 0.85rem;
        }

        .pricelist-package-params {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            padding: 0.75rem 0.9rem;
        }

        .pricelist-package-params__title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            margin-bottom: 0.55rem;
        }

        .pricelist-package-params__list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 0.4rem 0.85rem;
        }

        .pricelist-package-params__list li {
            display: flex;
            flex-direction: column;
            gap: 1px;
            padding: 0.4rem 0.55rem;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid #eef2f7;
        }

        .pricelist-package-params__code {
            font-size: 0.8125rem;
            font-weight: 400;
            color: #1e293b;
            word-break: break-all;
        }

        .pricelist-filter-empty {
            margin-top: 0.25rem;
        }

        .pricelist-filter-empty i {
            margin-bottom: 8px;
        }

        .fit-col {
            width: 1%;
            white-space: nowrap;
        }

        .flag-stack {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .empty-state,
        .compact-empty {
            text-align: center;
            padding: 40px 20px;
            color: var(--slate-500);
        }

        .compact-empty {
            padding: 18px 12px;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
        }

        .empty-state i,
        .compact-empty i {
            display: block;
            font-size: 40px;
            color: #94a3b8;
            margin-bottom: 12px;
        }

        .rm-act-btn {
            border-radius: 7px;
            padding: 4px 8px;
            margin-right: 3px;
            font-size: 12px;
        }

        .rm-act-btn:last-child {
            margin-right: 0;
        }

        .rm-act-btn--edit {
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            background: #eff6ff;
        }

        .rm-act-btn--edit:hover {
            background: #dbeafe;
            border-color: #93c5fd;
        }

        .rm-act-btn--delete {
            border: 1px solid #fecdd3;
            color: #e11d48;
            background: #fff5f7;
        }

        .rm-act-btn--delete:hover {
            background: #ffe4e6;
            border-color: #fda4af;
        }

        @media (max-width: 991.98px) {
            .profile-strip {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .analysis-collapse-summary {
                flex-direction: column;
                align-items: flex-start;
            }

            .analysis-collapse-total {
                text-align: left;
            }
        }

        /* Delete confirmation modal (matches equipment calibration log style) */
        .pricelist-show-page .eq-delete-overlay {
            background: rgba(15, 23, 42, 0.52) !important;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 1060;
            overflow-x: hidden;
        }

        .pricelist-show-page .eq-delete-dialog {
            max-width: min(640px, calc(100vw - 1.5rem));
        }

        .pricelist-show-page .eq-delete-shell {
            border-radius: 20px;
            overflow: hidden;
            background: #ffffff;
            box-shadow:
                0 24px 48px rgba(15, 23, 42, 0.18),
                0 0 0 1px rgba(226, 232, 240, 0.9);
        }

        .pricelist-show-page .eq-delete-body {
            position: relative;
            padding: 1.35rem 1.35rem 1.1rem;
            background: linear-gradient(180deg, #fafbfc 0%, #ffffff 42%);
            overflow-x: hidden;
        }

        .pricelist-show-page .eq-delete-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            z-index: 2;
            opacity: 0.45;
            padding: 0.5rem;
            border-radius: 10px;
            transition: opacity 0.15s ease, background-color 0.15s ease;
        }

        .pricelist-show-page .eq-delete-close:hover {
            opacity: 0.85;
            background-color: rgba(15, 23, 42, 0.06);
        }

        .pricelist-show-page .eq-delete-frame {
            position: relative;
            padding: 1.25rem 1.35rem 1.15rem;
            border-radius: 14px;
            border: 2px dashed rgba(220, 38, 38, 0.55);
            background:
                linear-gradient(145deg, rgba(254, 242, 242, 0.65) 0%, rgba(255, 255, 255, 0.92) 38%, #ffffff 100%);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
            overflow: hidden;
        }

        .pricelist-show-page .eq-delete-frame--scroll {
            max-height: min(52vh, 28rem);
            overflow-x: hidden;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .pricelist-show-page .eq-delete-frame__glow {
            position: absolute;
            top: -30%;
            right: 0;
            width: 45%;
            height: 70%;
            background: radial-gradient(ellipse at center, rgba(248, 113, 113, 0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .pricelist-show-page .eq-delete-frame-footer {
            margin-top: 1rem;
            padding-top: 0.85rem;
            border-top: 1px solid rgba(254, 202, 202, 0.5);
        }

        .pricelist-show-page .eq-delete-intro {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            margin-bottom: 1.15rem;
            padding-right: 1.5rem;
        }

        .pricelist-show-page .eq-delete-intro__icon {
            flex-shrink: 0;
            width: 2.75rem;
            height: 2.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.35rem;
            color: #b91c1c;
            background: linear-gradient(135deg, #fff 0%, #fef2f2 100%);
            border: 1px solid rgba(254, 202, 202, 0.9);
            box-shadow: 0 4px 12px rgba(185, 28, 28, 0.1);
        }

        .pricelist-show-page .eq-delete-intro__eyebrow {
            margin: 0 0 0.2rem;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #b91c1c;
        }

        .pricelist-show-page .eq-delete-intro__lead {
            margin: 0;
            font-size: 0.875rem;
            line-height: 1.45;
            color: #64748b;
        }

        .pricelist-show-page .eq-delete-details {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem 1.25rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 480px) {
            .pricelist-show-page .eq-delete-details {
                grid-template-columns: 1fr;
            }
        }

        .pricelist-show-page .eq-delete-field {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            min-width: 0;
        }

        .pricelist-show-page .eq-delete-field--span,
        .pricelist-show-page .eq-delete-field--full {
            grid-column: 1 / -1;
        }

        .pricelist-show-page .eq-delete-field__label {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .pricelist-show-page .eq-delete-field__value {
            font-size: 0.9rem;
            font-weight: 500;
            color: #1e293b;
            line-height: 1.4;
            word-break: break-word;
        }

        .pricelist-show-page .eq-delete-field__value--primary {
            font-size: 1rem;
            font-weight: 600;
            color: #0f172a;
        }

        .pricelist-show-page .eq-delete-field__value--mono {
            font-variant-numeric: tabular-nums;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.875rem;
        }

        .pricelist-show-page .eq-delete-pill {
            display: inline-block;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
            color: #334155;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
        }

        .pricelist-show-page .eq-delete-warning {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            margin-top: 0.15rem;
            padding: 0.75rem 0.9rem;
            border-radius: 10px;
            background: linear-gradient(135deg, #fef2f2 0%, #fff5f5 100%);
            border: 1px solid rgba(254, 202, 202, 0.65);
        }

        .pricelist-show-page .eq-delete-warning__icon {
            flex-shrink: 0;
            font-size: 1.15rem;
            color: #dc2626;
            margin-top: 0.05rem;
        }

        .pricelist-show-page .eq-delete-warning__text {
            font-size: 0.8125rem;
            line-height: 1.45;
            color: #991b1b;
            font-weight: 500;
        }

        .pricelist-show-page .eq-delete-footer {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.5rem;
            padding: 0.85rem 1.35rem 1.25rem;
            background: #fafbfc;
            border-top: 1px solid #eef2f6;
        }

        .pricelist-show-page .eq-delete-btn {
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.5rem 1.15rem;
            transition: transform 0.12s ease, box-shadow 0.12s ease, background-color 0.12s ease;
        }

        .pricelist-show-page .eq-delete-btn--cancel {
            color: #475569;
            background: #ffffff;
            border: 1px solid #d8e0eb;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .pricelist-show-page .eq-delete-btn--cancel:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #334155;
        }

        .pricelist-show-page .eq-delete-btn--confirm {
            color: #ffffff;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            border: none;
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35);
        }

        .pricelist-show-page .eq-delete-btn--confirm:hover:not(:disabled) {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 6px 18px rgba(220, 38, 38, 0.4);
            transform: translateY(-1px);
        }

        .pricelist-show-page .eq-delete-btn--confirm:disabled {
            opacity: 0.72;
        }

        @media (max-width: 767.98px) {
            .pricelist-show-page {
                padding: 0;
                background: transparent;
            }

            .item-modal-header,
            .item-modal-body,
            .item-modal-footer {
                padding-left: 16px;
                padding-right: 16px;
            }

            .page-header-card,
            .metric-card,
            .workspace-card,
            .inner-card,
            .assigned-customer-card {
                border-radius: 16px;
            }

            .workspace-tabs {
                gap: 8px;
            }

            .workspace-tab {
                width: 100%;
                border-radius: 12px;
                text-align: left;
            }

            .profile-strip {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('pricelist-scroll-to-message', function () {
                const alertEl = document.querySelector('.pricelist-show-page .alert[role="alert"]');
                if (alertEl) {
                    alertEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        });
    </script>
</div>
