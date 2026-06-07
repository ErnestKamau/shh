<div>
    <div class="modal fade show"
        style="display: flex; align-items: flex-start; overflow-y: auto; background-color: rgba(0,0,0,0.5); padding-top: 30px; padding-bottom: 30px;"
        wire:click.self="close" tabindex="-1" role="dialog" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="mdi mdi-{{ $customer ? 'pencil' : 'plus' }}"></i>
                        {{ $customer ? 'Edit' : 'Add' }} Customer
                    </h4>
                    <button type="button" class="close" wire:click="close" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        wire:model="name" placeholder="Name..." required />
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Postal Address</label>
                                    <textarea class="form-control @error('postal_address') is-invalid @enderror"
                                        wire:model="postal_address" placeholder="Postal Address..."></textarea>
                                    @error('postal_address') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Physical Address <span
                                            class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('physical_address') is-invalid @enderror"
                                        wire:model="physical_address" placeholder="Location..." required />
                                    @error('physical_address') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Website</label>
                                    <input type="text" class="form-control @error('website') is-invalid @enderror"
                                        wire:model="website" placeholder="Website..." />
                                    @error('website') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Country</label>
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
                                                wire:model.live.debounce.200ms="countrySearch"
                                                class="tag-input"
                                                placeholder="{{ $this->selectedCountry ? '' : 'Search countries...' }}"
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
                                    @error('country_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                wire:model="lpos_required" />
                                            <label class="form-check-label">LPO Required?</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" wire:model="active" />
                                            <label class="form-check-label">Is Active?</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" wire:model="is_internal" />
                                            <label class="form-check-label">Internal Customer?</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label">Fax</label>
                                    <input type="text" class="form-control @error('fax') is-invalid @enderror"
                                        wire:model="fax" placeholder="Fax..." />
                                    @error('fax') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        wire:model="email" placeholder="Email..." required />
                                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Phone 1 <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control @error('telephone1') is-invalid @enderror"
                                        wire:model="telephone1" placeholder="Phone 1..." required />
                                    @error('telephone1') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Phone 2</label>
                                    <input type="tel" class="form-control @error('telephone2') is-invalid @enderror"
                                        wire:model="telephone2" placeholder="Phone 2..." />
                                    @error('telephone2') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Credit Days</label>
                                    <input type="number" name="credit_days"
                                        class="form-control @error('credit_days') is-invalid @enderror"
                                        wire:model="credit_days" />
                                    @error('credit_days') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Account Settings <span class="text-danger">*</span></label>
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
                                                wire:model.live.debounce.200ms="accountSearch"
                                                class="tag-input"
                                                placeholder="{{ $this->selectedAccount ? '' : 'Search account settings...' }}"
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
                                    @error('account_status') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row border-top pt-3 mt-3">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label">Contract Validity From</label>
                                    <input type="date" wire:model="contract_valid_from" class="form-control @error('contract_valid_from') is-invalid @enderror" />
                                    @error('contract_valid_from') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label">Contract Validity To</label>
                                    <input type="date" wire:model="contract_valid_to" class="form-control @error('contract_valid_to') is-invalid @enderror" />
                                    @error('contract_valid_to') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                        <button type="button" class="btn btn-default" wire:click="close">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

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