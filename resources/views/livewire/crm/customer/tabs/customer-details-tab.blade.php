<div x-data="{
    initSelect2() {
        setTimeout(() => {
            let accountSelect = $('#account-status-select');
            if (accountSelect.length) {
                if (accountSelect.hasClass('select2-hidden-accessible')) {
                    accountSelect.select2('destroy');
                }
                accountSelect.select2({
                    placeholder: 'Choose Account Settings',
                    allowClear: true,
                    width: '100%',
                    closeOnSelect: true
                }).on('change', function (e) {
                    @this.set('account_status', $(this).val());
                });
                
                let initialAccount = @this.get('account_status');
                if (initialAccount) {
                    accountSelect.val(initialAccount).trigger('change');
                }
            }

            let countrySelect = $('#country-select-details');
            if (countrySelect.length) {
                if (countrySelect.hasClass('select2-hidden-accessible')) {
                    countrySelect.select2('destroy');
                }
                countrySelect.select2({
                    placeholder: 'Select Country',
                    allowClear: true,
                    width: '100%',
                    closeOnSelect: true
                }).on('change', function (e) {
                    @this.set('country_id', $(this).val());
                });
                
                let initialCountry = @this.get('country_id');
                if (initialCountry) {
                    countrySelect.val(initialCountry).trigger('change');
                }
            }
        }, 100);
    }
}" x-init="$watch('$wire.isEditing', value => { if(value) { setTimeout(() => initSelect2(), 100); } })">
    <style>
        .select2-container {
            z-index: 100000 !important;
        }

        .select2-dropdown {
            z-index: 100000 !important;
        }
    </style>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:28px;height:28px;background:#eef2ff;">
                <i class="mdi mdi-domain text-primary" style="font-size:1rem;"></i>
            </span>
            <div>
                <small class="font-weight-bold text-dark" style="font-size:0.82rem;">Client Account Profile</small>
                <small class="text-muted d-block" style="font-size:0.67rem;">Core account data, contact info &amp;
                    billing settings</small>
            </div>
        </div>
        @if(!$isEditing)
            <button class="btn btn-sm btn-outline-primary" wire:click="edit">
                <i class="mdi mdi-pencil-outline"></i> Edit Profile
            </button>
        @endif
    </div>

    @if($isEditing)
        <!-- Edit Form -->
        <form wire:submit.prevent="save" class="crm-form-container">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row align-items-center">
                        <label class="col-sm-4 col-form-label">Client Code:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control-plaintext" value="{{ $customer->code }}" readonly>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Organisation Name: <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="Enter name...">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Email Address: <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                wire:model="email" placeholder="example@domain.com">
                            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Primary Phone: <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control @error('telephone1') is-invalid @enderror"
                                wire:model="telephone1" placeholder="Primary phone...">
                            @error('telephone1') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Secondary Phone:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control @error('telephone2') is-invalid @enderror"
                                wire:model="telephone2" placeholder="Secondary phone...">
                            @error('telephone2') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Fax Number:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control @error('fax') is-invalid @enderror" wire:model="fax" placeholder="Fax...">
                            @error('fax') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Website URL:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control @error('website') is-invalid @enderror"
                                wire:model="website" placeholder="https://...">
                            @error('website') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Country:</label>
                        <div class="col-sm-8">
                            <div wire:ignore>
                            <select class="form-control" id="country-select-details">
                                <option value="">Select Country</option>
                                    @foreach($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('country_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Physical Address: <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control @error('physical_address') is-invalid @enderror"
                                wire:model="physical_address" placeholder="Physical address...">
                            @error('physical_address') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Postal Address:</label>
                        <div class="col-sm-8">
                            <textarea class="form-control @error('postal_address') is-invalid @enderror"
                                wire:model="postal_address" rows="2" placeholder="Postal address..."></textarea>
                            @error('postal_address') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Credit Days:</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control @error('credit_days') is-invalid @enderror"
                                wire:model="credit_days" placeholder="0">
                            @error('credit_days') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @if(isset($account_settings->id))
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Account Setting:</label>
                            <div class="col-sm-8" wire:ignore>
                            <select class="form-control no-select2" id="account-status-select">
                                <option value="">Choose Account Settings</option>
                                    @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->key }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('account_status') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div class="form-group row align-items-center">
                        <label class="col-sm-4 col-form-label">Account Settings:</label>
                        <div class="col-sm-8">
                            <div class="form-check form-check-inline mr-3">
                                <input class="form-check-input" type="checkbox" wire:model="active" id="activeCheck">
                                <label class="form-check-label" for="activeCheck">Active</label>
                            </div>
                            <div class="form-check form-check-inline mr-3">
                                <input class="form-check-input" type="checkbox" wire:model="lpos_required" id="lpoCheck">
                                <label class="form-check-label" for="lpoCheck">LPO Required</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" wire:model="is_internal" id="internalCheck">
                                <label class="form-check-label" for="internalCheck">Internal</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4 pt-3 border-top">
                <div class="col-12 text-right">
                    <button type="button" class="btn btn-outline-secondary mr-2" wire:click="cancel">
                        <i class="mdi mdi-close"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="mdi mdi-content-save-outline mr-1"></i> Save Profile Changes
                    </button>
                </div>
            </div>
        </form>
    @else
        <!-- Read Only View -->
        <div class="row">
            <div class="col-md-6">
                <div class="crm-table-wrap">
                    <table class="table crm-table crm-table-details">
                        <tbody>
                        <tr>
                            <th style="width:35%;" scope="row">Client Code:</th>
                            <td><span class="font-weight-bold"
                                    style="font-family:monospace;">{{ $customer->code ?? '—' }}</span></td>
                        </tr>
                        <tr>
                            <th>Organisation Name:</th>
                            <td>{{ $customer->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Email Address:</th>
                            <td>
                                @if($customer->email)
                                    <a href="mailto:{{ $customer->email }}" class="text-dark">{{ $customer->email }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Primary Phone:</th>
                            <td>{{ $customer->telephone1 ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Secondary Phone:</th>
                            <td>{{ $customer->telephone2 ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>Fax:</th>
                            <td>{{ $customer->fax ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>Website:</th>
                            <td>
                                @if($customer->website)
                                    <a href="{{ $customer->website }}" target="_blank"
                                    class="text-dark">{{ $customer->website }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-6">
                <div class="crm-table-wrap">
                    <table class="table crm-table crm-table-details">
                        <tbody>
                        <tr>
                            <th style="width:35%;" scope="row">Country:</th>
                            <td>{{ $customer->country->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Physical Address:</th>
                            <td>{{ $customer->physical_address ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Postal Address:</th>
                            <td>{{ $customer->postal_address ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>Credit Terms (Days):</th>
                            <td>{{ $customer->credit_days ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Account Status:</th>
                            <td>
                                @if($customer->active == 1)
                                    <span class="crm-badge crm-badge-success">Active</span>
                                @else
                                    <span class="crm-badge crm-badge-neutral">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>LPO Required:</th>
                            <td>
                                @if($customer->lpos_required == 1)
                                    <span class="crm-badge crm-badge-warning">Required</span>
                                @else
                                    <span class="crm-badge crm-badge-neutral">Not Required</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Client Type:</th>
                            <td>
                                @if($customer->is_internal ?? false)
                                    <span class="crm-badge crm-badge-info">Internal</span>
                                @else
                                    <span class="crm-badge crm-badge-neutral">External</span>
                                @endif
                            </td>
                        </tr>
                        @if(isset($account_settings->id))
                            <tr>
                                <th>Account Setting:</th>
                                <td>
                                    @php
                                        $accountName = $accounts->firstWhere('id', $customer->account_status)->key ?? '—';
                                    @endphp
                                    {{ $accountName }}
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>