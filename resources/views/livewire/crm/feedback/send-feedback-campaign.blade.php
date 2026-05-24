<div>

    @teleport('body')
    <div class="modal fade" id="sendCampaignModal" tabindex="-1" role="dialog" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-email-multiple-outline text-primary"></i> Send Feedback
                    </h5>
                    <button type="button" class="close" wire:click="close" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if($sending || $completed)
                        <div wire:poll.2s="checkProgress">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">
                                    @if($completed) 
                                        <i class="mdi mdi-check-circle text-success"></i> Sending Completed 
                                    @else 

                                        <i class="mdi mdi-loading mdi-spin text-primary"></i> Sending Feedback... 
                                    @endif
                                </h4>
                                <span class="crm-badge crm-badge-primary">
                                    {{ $progressStats['sent'] + $progressStats['failed'] }} / {{ $progressStats['total'] }}
                                </span>
                            </div>

                            @php 
                                $percent = $progressStats['total'] > 0 ? (($progressStats['sent'] + $progressStats['failed']) / $progressStats['total']) * 100 : 0; 
                            @endphp
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar @if($completed) bg-success @else progress-bar-striped progress-bar-animated @endif" 
                                     role="progressbar" style="width: {{ $percent }}%"></div>
                            </div>

                            <div class="list-group list-group-flush border rounded" style="max-height: 300px; overflow-y: auto;">
                                @foreach($feedbackProgress as $item)
                                    <div class="list-group-item d-flex justify-content-between align-items-center p-2">
                                        <div class="text-truncate mr-2">
                                            <strong>{{ $item['name'] }}</strong> <br>
                                            <small class="text-muted">{{ $item['email'] }} • {{ $item['customer'] }}</small>
                                        </div>
                                        <div style="min-width: 100px; text-align: right;">
                                            @if($item['status'] == 'pending')
                                                <span class="crm-badge crm-badge-neutral"><i class="mdi mdi-clock-outline"></i> Pending</span>
                                            @elseif(in_array($item['status'], ['processing', 'generating_report']))
                                                <span class="crm-badge crm-badge-primary"><i class="mdi mdi-loading mdi-spin"></i> Generating Report...</span>
                                            @elseif($item['status'] == 'sending_email')
                                                <span class="crm-badge crm-badge-primary"><i class="mdi mdi-loading mdi-spin"></i> Sending Email...</span>
                                            @elseif($item['status'] == 'sent')
                                                <span class="crm-badge crm-badge-success"><i class="mdi mdi-check"></i> Sent</span>
                                            @elseif($item['status'] == 'failed')
                                                <span class="crm-badge crm-badge-danger"><i class="mdi mdi-alert-circle"></i> Failed</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        @if($errors->has('recipients'))
                            <div class="alert alert-danger">
                                {{ $errors->first('recipients') }}
                            </div>
                        @endif

                        <div class="form-group">
                            <label class="font-weight-bold">Select Customers (Multi-Select)</label>
                            <div class="tag-select-container" wire:click.outside="$set('showCustomerDropdown', false)">
                                <div class="tag-select-input">
                                    <div class="selected-tags">
                                        @foreach($this->selectedCustomersList as $customer)
                                            <span class="selected-tag">
                                                {{ $customer->name }}
                                                <i class="mdi mdi-close" wire:click.stop="removeCustomer('{{ $customer->id }}')"></i>
                                            </span>
                                        @endforeach
                                    </div>
                                    <input type="text" class="tag-input" placeholder="Search customers..."
                                        wire:model.live.debounce.200ms="customerSearch"
                                        wire:focus="$set('showCustomerDropdown', true)" />
                                    @if(!empty($selectedCustomers))
                                        <i class="mdi mdi-close-circle clear-icon" wire:click="clearCustomers"></i>
                                    @endif
                                </div>
                                @if($showCustomerDropdown)
                                    <div class="tag-dropdown">
                                        @forelse($this->filteredCustomerOptions as $customer)
                                            <div class="tag-dropdown-item" wire:click="toggleCustomer('{{ $customer->id }}')">
                                                {{ $customer->name }}
                                            </div>
                                        @empty
                                            <div class="tag-dropdown-empty">No customers found</div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Recipients Preview -->
                        <div class="form-group">
                            <label class="font-weight-bold d-flex justify-content-between">
                                <span>Recipients Preview</span>
                                <span class="crm-badge crm-badge-primary">{{ count($recipients) }} Found</span>
                            </label>
                            <div class="border rounded p-2 bg-light" style="max-height: 150px; overflow-y: auto;">
                                @forelse($recipients as $recipient)
                                    <div class="small mb-1 border-bottom pb-1 d-flex align-items-center">
                                        <div class="custom-control custom-checkbox mr-2">
                                            <input type="checkbox" class="custom-control-input"
                                                id="recipient_{{ $recipient->id }}" value="{{ $recipient->id }}"
                                                wire:model="selectedRecipients">
                                            <label class="custom-control-label" for="recipient_{{ $recipient->id }}"></label>
                                        </div>

                                        <div>
                                            <i class="mdi mdi-account-circle text-muted"></i>
                                            <strong>{{ $recipient->customer->name ?? 'Unknown Customer' }}</strong> -
                                            {{ $recipient->first_name }} {{ $recipient->last_name }}
                                            <span class="text-muted">&lt;{{ $recipient->email }}&gt;</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3">
                                        <i class="mdi mdi-account-search-outline h4"></i>
                                        <p class="mb-0">Select customers to load opted-in contacts.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    @if($sending || $completed)
                        <button type="button" class="btn btn-secondary" wire:click="close" @if(!$completed) disabled @endif>
                            @if($completed) Close @else Please Wait... @endif
                        </button>
                    @else
                        <button type="button" class="btn btn-secondary" wire:click="close">Close</button>
                        <button type="button" class="btn btn-success" wire:click="send" wire:loading.attr="disabled">
                            <i class="mdi mdi-send" wire:loading.remove></i>
                            <span wire:loading><i class="mdi mdi-loading mdi-spin"></i> Processing...</span>
                            Send Feedback
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endteleport

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('show-campaign-modal', () => {
                $('#sendCampaignModal').modal('show');
            });

            Livewire.on('close-campaign-modal', () => {
                $('#sendCampaignModal').modal('hide');
            });

            Livewire.on('campaign-modal-closed', () => {
                $('#sendCampaignModal').modal('hide');
            });
        });
    </script>

    <style>
        .tag-select-container { position: relative; }
        .tag-select-input {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            min-height: 42px;
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
        }
        .selected-tags { display: inline-flex; flex-wrap: wrap; gap: 6px; }
        .selected-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #e8f1ff;
            color: #1f2937;
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 12px;
        }
        .selected-tag i { cursor: pointer; font-size: 14px; }
        .tag-input { border: none; outline: none; flex: 1 1 180px; min-width: 120px; }
        .clear-icon { cursor: pointer; color: #9ca3af; font-size: 18px; }
        .tag-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            max-height: 220px;
            overflow-y: auto;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
            z-index: 1070;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }
        .tag-dropdown-item { padding: 8px 10px; cursor: pointer; }
        .tag-dropdown-item:hover { background: #f3f4f6; }
        .tag-dropdown-empty { padding: 8px 10px; color: #6b7280; font-size: 13px; }
    </style>
</div>