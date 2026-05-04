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
                    placeholder: @js(__('crm.select_country')),
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
                <small class="font-weight-bold text-dark" style="font-size:0.82rem;">{{ __('crm.client_account_profile') }}</small>
                <small class="text-muted d-block" style="font-size:0.67rem;">{{ __('crm.client_account_profile_subtitle') }}</small>
            </div>
        </div>
        @if(!$isEditing)
            <button class="btn btn-sm btn-outline-primary" wire:click="edit">
                <i class="mdi mdi-pencil-outline"></i> {{ __('crm.edit_profile') }}
            </button>
        @endif
    </div>

    @if($isEditing)
        <!-- Edit Form -->
        <form wire:submit.prevent="save" class="crm-form-container">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row align-items-center">
                        <label class="col-sm-4 col-form-label">{{ __('crm.client_code') }}:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control-plaintext" value="{{ $customer->code }}" readonly>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">{{ __('crm.organisation_name') }}: <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="{{ __('crm.enter_name') }}...">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">{{ __('crm.email_address') }}: <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                wire:model="email" placeholder="example@domain.com">
                            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">{{ __('crm.primary_phone') }}: <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control @error('telephone1') is-invalid @enderror"
                                wire:model="telephone1" placeholder="{{ __('crm.primary_phone') }}...">
                            @error('telephone1') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">{{ __('crm.secondary_phone') }}:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control @error('telephone2') is-invalid @enderror"
                                wire:model="telephone2" placeholder="{{ __('crm.secondary_phone') }}...">
                            @error('telephone2') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">{{ __('crm.fax_number') }}:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control @error('fax') is-invalid @enderror" wire:model="fax" placeholder="{{ __('crm.fax') }}...">
                            @error('fax') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">{{ __('crm.website_url') }}:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control @error('website') is-invalid @enderror"
                                wire:model="website" placeholder="https://...">
                            @error('website') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">{{ __('crm.country') }}:</label>
                        <div class="col-sm-8">
                            <div wire:ignore>
                            <select class="form-control" id="country-select-details">
                                <option value="">{{ __('crm.select_country') }}</option>
                                    @foreach($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('country_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">{{ __('crm.physical_address') }}: <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control @error('physical_address') is-invalid @enderror"
                                wire:model="physical_address" placeholder="{{ __('crm.physical_address') }}...">
                            @error('physical_address') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">{{ __('crm.postal_address') }}:</label>
                        <div class="col-sm-8">
                            <textarea class="form-control @error('postal_address') is-invalid @enderror"
                                wire:model="postal_address" rows="2" placeholder="{{ __('crm.postal_address') }}..."></textarea>
                            @error('postal_address') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">{{ __('crm.credit_days') }}:</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control @error('credit_days') is-invalid @enderror"
                                wire:model="credit_days" placeholder="0">
                            @error('credit_days') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @if(isset($account_settings->id))
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">{{ __('crm.account_setting') }}:</label>
                            <div class="col-sm-8" wire:ignore>
                            <select class="form-control no-select2" id="account-status-select">
                                <option value="">{{ __('crm.choose_account_settings') }}</option>
                                    @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->key }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('account_status') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div class="form-group row align-items-center">
                        <label class="col-sm-4 col-form-label">{{ __('crm.account_settings') }}:</label>
                        <div class="col-sm-8">
                            <div class="form-check form-check-inline mr-3">
                                <input class="form-check-input" type="checkbox" wire:model="active" id="activeCheck">
                                <label class="form-check-label" for="activeCheck">{{ ucfirst(__('crm.active')) }}</label>
                            </div>
                            <div class="form-check form-check-inline mr-3">
                                <input class="form-check-input" type="checkbox" wire:model="lpos_required" id="lpoCheck">
                                <label class="form-check-label" for="lpoCheck">{{ __('crm.lpo_required_label') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" wire:model="is_internal" id="internalCheck">
                                <label class="form-check-label" for="internalCheck">{{ __('crm.internal') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4 pt-3 border-top">
                <div class="col-12 text-right">
                    <button type="button" class="btn btn-outline-secondary mr-2" wire:click="cancel">
                        <i class="mdi mdi-close"></i> {{ __('crm.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="mdi mdi-content-save-outline mr-1"></i> {{ __('crm.save_profile_changes') }}
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
                            <th style="width:35%;" scope="row">{{ __('crm.client_code') }}:</th>
                            <td><span class="font-weight-bold"
                                    style="font-family:monospace;">{{ $customer->code ?? '—' }}</span></td>
                        </tr>
                        <tr>
                            <th>{{ __('crm.organisation_name') }}:</th>
                            <td>{{ $customer->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('crm.email_address') }}:</th>
                            <td>
                                @if($customer->email)
                                    <a href="mailto:{{ $customer->email }}" class="text-dark">{{ $customer->email }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('crm.primary_phone') }}:</th>
                            <td>{{ $customer->telephone1 ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('crm.secondary_phone') }}:</th>
                            <td>{{ $customer->telephone2 ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('crm.fax') }}:</th>
                            <td>{{ $customer->fax ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('crm.website') }}:</th>
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
                            <th style="width:35%;" scope="row">{{ __('crm.country') }}:</th>
                            <td>{{ $customer->country->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('crm.physical_address') }}:</th>
                            <td>{{ $customer->physical_address ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('crm.postal_address') }}:</th>
                            <td>{{ $customer->postal_address ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('crm.credit_terms_days') }}:</th>
                            <td>{{ $customer->credit_days ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('crm.account_status') }}:</th>
                            <td>
                                @if($customer->active == 1)
                                    <span class="crm-badge crm-badge-success">{{ ucfirst(__('crm.active')) }}</span>
                                @else
                                    <span class="crm-badge crm-badge-neutral">{{ __('crm.inactive') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('crm.lpo_required_label') }}:</th>
                            <td>
                                @if($customer->lpos_required == 1)
                                    <span class="crm-badge crm-badge-warning">{{ __('crm.required') }}</span>
                                @else
                                    <span class="crm-badge crm-badge-neutral">{{ __('crm.not_required') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('crm.client_type') }}:</th>
                            <td>
                                @if($customer->is_internal ?? false)
                                    <span class="crm-badge crm-badge-info">{{ __('crm.internal') }}</span>
                                @else
                                    <span class="crm-badge crm-badge-neutral">{{ __('crm.external') }}</span>
                                @endif
                            </td>
                        </tr>
                        @if(isset($account_settings->id))
                            <tr>
                                <th>{{ __('crm.account_setting') }}:</th>
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