<div>
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
                            <div class="tag-select-container @error('country_id') is-invalid @enderror"
                                wire:click="$set('showCountryDropdown', true)"
                                wire:click.outside="$set('showCountryDropdown', false)">
                                <div class="tag-select-input">
                                    @if($this->selectedCountry)
                                        <span class="tag-badge">
                                            {{ data_get($this->selectedCountry, 'name') }}
                                            <i class="mdi mdi-close-circle" wire:click.stop="clearCountry"></i>
                                        </span>
                                    @endif

                                    <input type="text"
                                        wire:model.live="countrySearch"
                                        class="tag-input"
                                        placeholder="{{ $this->selectedCountry ? '' : __('crm.select_country') }}"
                                        autocomplete="off">
                                </div>

                                @if($showCountryDropdown)
                                    <div class="tag-dropdown">
                                        @if(count($this->filteredCountries) > 0)
                                            @foreach($this->filteredCountries as $country)
                                                <div class="tag-dropdown-item" wire:click.stop="selectCountry('{{ data_get($country, 'id') }}')">
                                                    {{ data_get($country, 'name') }}
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="tag-dropdown-item text-muted">No countries found</div>
                                        @endif
                                    </div>
                                @endif
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

                    @if(data_get($account_settings, 'id'))
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">{{ __('crm.account_setting') }}:</label>
                            <div class="col-sm-8">
                                <div class="tag-select-container @error('account_status') is-invalid @enderror"
                                    wire:click="$set('showAccountDropdown', true)"
                                    wire:click.outside="$set('showAccountDropdown', false)">
                                    <div class="tag-select-input">
                                        @if($this->selectedAccount)
                                            <span class="tag-badge">
                                                {{ data_get($this->selectedAccount, 'key') }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="clearAccountStatus"></i>
                                            </span>
                                        @endif

                                        <input type="text"
                                            wire:model.live="accountSearch"
                                            class="tag-input"
                                            placeholder="{{ $this->selectedAccount ? '' : __('crm.choose_account_settings') }}"
                                            autocomplete="off">
                                    </div>

                                    @if($showAccountDropdown)
                                        <div class="tag-dropdown">
                                            @if(count($this->filteredAccounts) > 0)
                                                @foreach($this->filteredAccounts as $account)
                                                    <div class="tag-dropdown-item" wire:click.stop="selectAccountStatus('{{ data_get($account, 'id') }}')">
                                                        {{ data_get($account, 'key') }}
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="tag-dropdown-item text-muted">No account settings found</div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
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
                        @if(data_get($account_settings, 'id'))
                            <tr>
                                <th>{{ __('crm.account_setting') }}:</th>
                                <td>
                                    @php
                                        $accountName = data_get($accounts->firstWhere('id', $customer->account_status), 'key', '—');
                                    @endphp
                                    {{ $accountName }}
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <th>Quotation acceptance TAT (avg):</th>
                            <td>
                                @php
                                    $tatMinutes = is_numeric($customer->quotation_acceptance_tat_minutes)
                                        ? max((int) $customer->quotation_acceptance_tat_minutes, 0)
                                        : null;
                                @endphp
                                @if($tatMinutes !== null)
                                    <span class="crm-badge crm-badge-info">{{ number_format($tatMinutes / 60, 2) }} hours</span>
                                    <small class="text-muted d-block mt-1">{{ number_format($tatMinutes) }} minutes</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <style>
        .tag-select-container {
            position: relative;
            width: 100%;
        }

        .tag-select-input {
            min-height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding: 4px 8px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            background-color: #fff;
        }

        .tag-input {
            border: none;
            outline: none;
            flex: 1;
            min-width: 120px;
            font-size: 0.9rem;
        }

        .tag-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0f2f5;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 0.85rem;
        }

        .tag-badge i {
            cursor: pointer;
        }

        .tag-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            max-height: 220px;
            overflow-y: auto;
            z-index: 1100;
        }

        .tag-dropdown-item {
            padding: 8px 10px;
            cursor: pointer;
        }

        .tag-dropdown-item:hover {
            background: #f8f9fa;
        }

        .tag-select-container.is-invalid .tag-select-input {
            border-color: #dc3545;
        }
    </style>
</div>