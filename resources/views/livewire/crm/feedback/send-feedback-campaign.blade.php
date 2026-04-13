<div x-data="{
    initializeSelect2() {
        let select = $('#campaignCustomerSelect');
        
        if (select.length) {
            // Destroy existing instance if present
            if (select.hasClass('select2-hidden-accessible')) {
                select.select2('destroy');
            }
            
            // Initialize with configuration matching contact-form.blade.php
            select.select2({
                placeholder: 'Select Customers',
                allowClear: true,
                width: '100%',
                multiple: true,
                closeOnSelect: true
            }).on('select2:select', function (e) {
                var self = $(this);
                setTimeout(function() {
                    self.select2('close');
                }, 50);
            }).on('change', function (e) {
                var data = $(this).val();
                $wire.set('selectedCustomers', data);
            });
            
            // Load initial values from Livewire state
            let initialCustomers = $wire.get('selectedCustomers');
            if (initialCustomers && initialCustomers.length > 0) {
                select.val(initialCustomers).trigger('change');
            }
        }
    }
}">

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

                        <div class="form-group" wire:ignore x-init="initializeSelect2()">
                            <label class="font-weight-bold">Select Customers (Multi-Select)</label>
                            <select id="campaignCustomerSelect" class="form-control select2" multiple>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Recipients Preview -->
                        <div class="form-group">
                            <label class="font-weight-bold d-flex justify-content-between">
                                <span>Recipients Preview (Opt-in Only)</span>
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

            Livewire.on('campaign-modal-closed', (event) => {
                $('#sendCampaignModal').modal('hide');
                let detail = Array.isArray(event) ? event[0] : (event.detail || event);
                
                if (detail && detail.cleanup) {
                    // Clean up Select2 instance when modal closes
                    let select = $('#campaignCustomerSelect');
                    if (select.hasClass('select2-hidden-accessible')) {
                        select.select2('destroy');
                    }
                }
            });
        });

        function initializeSelect2() {
            let select = $('#campaignCustomerSelect');

            if (select.length) {
                // Destroy existing instance if present
                if (select.hasClass('select2-hidden-accessible')) {
                    select.select2('destroy');
                }

                // Initialize with configuration matching contact-form.blade.php
                select.select2({
                    placeholder: 'Select Customers',
                    allowClear: true,
                    width: '100%',
                    multiple: true,
                    closeOnSelect: true
                }).on('select2:select', function (e) {
                    var self = $(this);
                    setTimeout(function () {
                        self.select2('close');
                    }, 50);
                }).on('change', function (e) {
                    var data = $(this).val();
                    $wire.set('selectedCustomers', data);
                });

                // Load initial values from Livewire state
                let initialCustomers = $wire.get('selectedCustomers');
                if (initialCustomers && initialCustomers.length > 0) {
                    select.val(initialCustomers).trigger('change');
                }
            }
        }
    </script>
</div>