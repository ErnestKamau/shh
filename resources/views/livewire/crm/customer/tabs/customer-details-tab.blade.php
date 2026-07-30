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
                        <label class="col-sm-4 col-form-label">{{ __('crm.contact_person') }}:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control @error('contact_person') is-invalid @enderror"
                                wire:model="contact_person" placeholder="{{ __('crm.contact_person_placeholder') }}">
                            @error('contact_person') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">{{ __('crm.designation') }}:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control @error('designation') is-invalid @enderror"
                                wire:model="designation" placeholder="{{ __('crm.designation_placeholder') }}">
                            @error('designation') <span class="text-danger small">{{ $message }}</span> @enderror
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

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">{{ __('crm.customer_logo') }}:</label>
                        <div class="col-sm-8">
                            <input type="file" class="form-control @error('logoFile') is-invalid @enderror"
                                wire:model="logoFile" accept="image/*,.svg">
                            @error('logoFile') <span class="text-danger small">{{ $message }}</span> @enderror
                            <div wire:loading wire:target="logoFile" class="text-muted small mt-1">{{ __('crm.uploading') }}...</div>
                            <small class="text-muted d-block mt-1">{{ __('crm.customer_logo_help') }}</small>
                            @if($logoFile)
                                @php
                                    $logoExt = strtolower((string) $logoFile->getClientOriginalExtension());
                                @endphp
                                @if(! in_array($logoExt, ['svg'], true))
                                    <img src="{{ $logoFile->temporaryUrl() }}" alt="Logo preview" class="mt-2"
                                         style="max-height:48px;max-width:140px;object-fit:contain;border:1px solid #e5e7eb;border-radius:6px;padding:3px;background:#fff;">
                                @else
                                    <small class="text-muted d-block mt-1">{{ $logoFile->getClientOriginalName() }}</small>
                                @endif
                            @elseif($customer->logoUrl())
                                <div class="mt-2 d-flex align-items-center" style="gap:10px;">
                                    <img src="{{ $customer->logoUrl() }}" alt="{{ $customer->name }}"
                                         style="max-height:48px;max-width:140px;object-fit:contain;border:1px solid #e5e7eb;border-radius:6px;padding:3px;background:#fff;">
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeLogo">
                                        {{ __('crm.remove_logo') }}
                                    </button>
                                </div>
                            @endif
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
                        <label class="col-sm-4 col-form-label">{{ __('crm.company_address') }}: <span class="text-danger">*</span></label>
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
                        <label class="col-sm-4 col-form-label">{{ __('crm.billing_address') }}:</label>
                        <div class="col-sm-8">
                            <textarea class="form-control @error('billing_address') is-invalid @enderror"
                                wire:model="billing_address" rows="2" placeholder="{{ __('crm.billing_address_placeholder') }}"></textarea>
                            @error('billing_address') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">{{ __('crm.vat_registration') }}:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control @error('vat_no') is-invalid @enderror"
                                wire:model="vat_no" placeholder="{{ __('crm.vat_number_placeholder') }}">
                            @error('vat_no') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">{{ __('crm.trade_license') }}:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control @error('trade_license') is-invalid @enderror"
                                wire:model="trade_license" placeholder="{{ __('crm.trade_license_placeholder') }}">
                            @error('trade_license') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">{{ __('crm.vat_registration_certificate') }}:</label>
                        <div class="col-sm-8">
                            <input type="file" class="form-control @error('vatRegistrationCertificateFile') is-invalid @enderror"
                                wire:model="vatRegistrationCertificateFile" accept=".pdf,image/*">
                            @error('vatRegistrationCertificateFile') <span class="text-danger small">{{ $message }}</span> @enderror
                            <div wire:loading wire:target="vatRegistrationCertificateFile" class="text-muted small mt-1">{{ __('crm.uploading') }}...</div>
                            <small class="text-muted d-block mt-1">{{ __('crm.vat_registration_certificate_help') }}</small>
                            @if($vatRegistrationCertificateFile)
                                <small class="text-muted d-block mt-1">{{ $vatRegistrationCertificateFile->getClientOriginalName() }}</small>
                            @elseif($customer->vatRegistrationCertificateUrl())
                                <div class="mt-2 d-flex align-items-center" style="gap:10px;">
                                    <a href="{{ $customer->vatRegistrationCertificateUrl() }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="mdi mdi-file-document-outline"></i> {{ __('crm.view_certificate') }}
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeVatRegistrationCertificate">
                                        {{ __('crm.remove_certificate') }}
                                    </button>
                                </div>
                            @endif
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
                                                {{ app(\App\Services\Commercial\AccountPaymentTermsService::class)->displayLabel($this->selectedAccount) }}
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
                                                        {{ app(\App\Services\Commercial\AccountPaymentTermsService::class)->displayLabel($account) }}
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

                        @php $accountTerms = $this->selectedAccountTerms; @endphp
                        @if(!empty($account_status))
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">
                                    {{ __('crm.credit_days') }}:
                                    @if(($accountTerms['billing_type'] ?? '') === 'other')
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <div class="col-sm-8">
                                    <input type="number"
                                        class="form-control @error('credit_days') is-invalid @enderror"
                                        wire:model="credit_days"
                                        min="0"
                                        @if(!($accountTerms['allows_custom_days'] ?? false)) readonly @endif>
                                    @if(($accountTerms['anchor'] ?? '') === 'test_report_delivery')
                                        <small class="text-muted">Days counted from Test Report delivery.</small>
                                    @elseif(($accountTerms['anchor'] ?? '') === 'immediate')
                                        <small class="text-muted">Payment due immediately / in advance.</small>
                                    @endif
                                    @error('credit_days') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @if(($accountTerms['billing_type'] ?? '') === 'other')
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">{{ __('crm.payment_method') }}: <span class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <select wire:model.live="payment_method" class="form-select form-control @error('payment_method') is-invalid @enderror" style="min-height:42px;height:auto;line-height:1.5;padding-top:10px;padding-bottom:10px;">
                                            <option value="">{{ __('crm.select_payment_method') }}</option>
                                            @foreach($this->paymentMethodOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('payment_method') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                @if(($payment_method ?? '') === 'other')
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">{{ __('crm.payment_terms_note') }}:</label>
                                        <div class="col-sm-8">
                                            <input type="text"
                                                class="form-control @error('payment_terms_note') is-invalid @enderror"
                                                wire:model="payment_terms_note"
                                                maxlength="500"
                                                placeholder="{{ __('crm.payment_terms_note_placeholder') }}">
                                            @error('payment_terms_note') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @endif
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
                            <th>{{ __('crm.contact_person') }}:</th>
                            <td>{{ $customer->contact_person ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('crm.designation') }}:</th>
                            <td>{{ $customer->designation ?: '—' }}</td>
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
                        <tr>
                            <th>{{ __('crm.customer_logo') }}:</th>
                            <td>
                                @if($customer->logoUrl())
                                    <img src="{{ $customer->logoUrl() }}" alt="{{ $customer->name }}"
                                         style="max-height:48px;max-width:140px;object-fit:contain;border:1px solid #e5e7eb;border-radius:6px;padding:3px;background:#fff;">
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
                            <th>{{ __('crm.company_address') }}:</th>
                            <td>{{ $customer->physical_address ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('crm.postal_address') }}:</th>
                            <td>{{ $customer->postal_address ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('crm.billing_address') }}:</th>
                            <td>{{ $customer->billing_address ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('crm.vat_registration') }}:</th>
                            <td>{{ $customer->vat_no ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('crm.trade_license') }}:</th>
                            <td>{{ $customer->trade_license ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('crm.vat_registration_certificate') }}:</th>
                            <td>
                                @if($customer->vatRegistrationCertificateUrl())
                                    <a href="{{ $customer->vatRegistrationCertificateUrl() }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="mdi mdi-file-document-outline"></i> {{ __('crm.view_certificate') }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
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
                                        $selectedAccount = $accounts->firstWhere('id', $customer->account_status);
                                        $accountName = $selectedAccount
                                            ? app(\App\Services\Commercial\AccountPaymentTermsService::class)->displayLabel($selectedAccount)
                                            : '—';
                                        $terms = app(\App\Services\Commercial\AccountPaymentTermsService::class)
                                            ->resolveFromCustomer($customer);
                                    @endphp
                                    {{ $accountName }}
                                    @if($terms['days'] !== null)
                                        <small class="text-muted d-block mt-1">
                                            {{ $terms['days'] }} {{ __('crm.credit_days') }}
                                            @if($terms['anchor'] === 'test_report_delivery')
                                                — from Test Report delivery
                                            @elseif($terms['anchor'] === 'immediate')
                                                — due immediately
                                            @endif
                                        </small>
                                    @endif
                                    @if(filled($terms['payment_method']))
                                        <small class="text-muted d-block mt-1">
                                            {{ __('crm.payment_method') }}:
                                            {{ app(\App\Services\Commercial\AccountPaymentTermsService::class)->paymentMethodLabel($terms['payment_method']) }}
                                        </small>
                                    @endif
                                    @if(filled($customer->payment_terms_note))
                                        <small class="text-muted d-block mt-1">{{ $customer->payment_terms_note }}</small>
                                    @endif
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