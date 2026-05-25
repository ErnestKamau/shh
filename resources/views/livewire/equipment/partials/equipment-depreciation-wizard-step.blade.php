@php
    $selectedMethodCode = null;
    if (!empty($depreciationForm['depreciation_method_id'])) {
        $m = $this->depreciationMethods->firstWhere('id', $depreciationForm['depreciation_method_id']);
        $selectedMethodCode = $m?->code?->value ?? $m?->code ?? null;
    }
@endphp
<div class="step-content">
    <div class="eq-section-header">
        <i class="mdi mdi-finance"></i> Asset Depreciation Configuration
    </div>

    <div class="form-group mb-3">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="enable_depreciation"
                   wire:model.live="depreciationForm.enable_depreciation">
            <label class="custom-control-label" for="enable_depreciation">Enable Depreciation</label>
        </div>
    </div>

    @if(!empty($depreciationForm['enable_depreciation']))
        <div class="eq-section-header mt-3">
            <i class="mdi mdi-cash-multiple"></i> Financial Basis
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="form-label">Currency <span class="text-danger">*</span></label>
                    <div class="tag-select-container equipment-tag-select"
                         wire:click="$set('showDepreciationCurrencyDropdown', true)"
                         wire:click.outside="$set('showDepreciationCurrencyDropdown', false)">
                        <div class="tag-select-input">
                            @if($this->selectedDepreciationCurrency)
                                <span class="tag-badge">
                                    {{ $this->selectedDepreciationCurrency->name }}@if($this->selectedDepreciationCurrency->description) — {{ $this->selectedDepreciationCurrency->description }}@endif
                                    <i class="mdi mdi-close-circle" wire:click.stop="clearDepreciationCurrency"></i>
                                </span>
                            @endif
                            <input type="text"
                                   wire:model.live="depreciationCurrencySearch"
                                   class="tag-input"
                                   placeholder="{{ $this->selectedDepreciationCurrency ? '' : 'Search currencies...' }}"
                                   autocomplete="off">
                        </div>
                        @if($showDepreciationCurrencyDropdown && count($this->filteredDepreciationCurrencies) > 0)
                            <div class="tag-dropdown">
                                @foreach($this->filteredDepreciationCurrencies as $currency)
                                    <div class="tag-dropdown-item"
                                         wire:click.stop="selectDepreciationCurrency(@js($currency->name))">
                                        {{ $currency->name }}@if($currency->description) — {{ $currency->description }}@endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @error('depreciationForm.currency') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="form-label">Freight / Installation Cost</label>
                    <input type="number" step="0.01" wire:model.live="depreciationForm.freight_cost" class="form-control" min="0">
                    @error('depreciationForm.freight_cost') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="form-label">Capitalized Amount</label>
                    <input type="number" step="0.01"
                           wire:model="depreciationForm.capitalized_amount"
                           class="form-control"
                           @if(empty($depreciationForm['capitalized_amount_override'])) readonly @endif>
                    <small class="text-muted">Purchase price + freight (from Basic Info step)</small>
                    @error('depreciationForm.capitalized_amount') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="eq-section-header mt-3">
            <i class="mdi mdi-chart-timeline-variant"></i> Depreciation Method
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label">Method <span class="text-danger">*</span></label>
                    <div class="tag-select-container equipment-tag-select"
                         wire:click="$set('showDepreciationMethodDropdown', true)"
                         wire:click.outside="$set('showDepreciationMethodDropdown', false)">
                        <div class="tag-select-input">
                            @if($this->selectedDepreciationMethod)
                                <span class="tag-badge">
                                    {{ $this->selectedDepreciationMethod->name }}
                                    <i class="mdi mdi-close-circle" wire:click.stop="clearDepreciationMethod"></i>
                                </span>
                            @endif
                            <input type="text"
                                   wire:model.live="depreciationMethodSearch"
                                   class="tag-input"
                                   placeholder="{{ $this->selectedDepreciationMethod ? '' : 'Search methods...' }}"
                                   autocomplete="off">
                        </div>
                        @if($showDepreciationMethodDropdown && count($this->filteredDepreciationMethods) > 0)
                            <div class="tag-dropdown">
                                @foreach($this->filteredDepreciationMethods as $method)
                                    <div class="tag-dropdown-item"
                                         wire:click.stop="selectDepreciationMethod(@js($method->id))">
                                        {{ $method->name }}
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @error('depreciationForm.depreciation_method_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label d-block">Depreciation Frequencies <span class="text-danger">*</span></label>
                    <div class="d-flex flex-wrap" style="gap: 16px;">
                        <label class="mb-0 d-flex align-items-center" style="gap: 6px;">
                            <input type="checkbox" value="monthly" wire:model="depreciationForm.frequencies">
                            Monthly
                        </label>
                        <label class="mb-0 d-flex align-items-center" style="gap: 6px;">
                            <input type="checkbox" value="quarterly" wire:model="depreciationForm.frequencies">
                            Quarterly
                        </label>
                        <label class="mb-0 d-flex align-items-center" style="gap: 6px;">
                            <input type="checkbox" value="yearly" wire:model="depreciationForm.frequencies">
                            Yearly
                        </label>
                    </div>
                    <small class="text-muted d-block mt-1">Each selected frequency generates its own depreciation schedule and ledger entries.</small>
                    @error('depreciationForm.frequencies') <span class="text-danger d-block">{{ $message }}</span> @enderror
                    @error('depreciationForm.frequencies.*') <span class="text-danger d-block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="form-label">Depreciation Start Date <span class="text-danger">*</span></label>
                    <input type="date" wire:model="depreciationForm.depreciation_start_date" class="form-control">
                    @error('depreciationForm.depreciation_start_date') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="form-label">Useful Life (Years) <span class="text-danger">*</span></label>
                    <input type="number" wire:model="depreciationForm.useful_life_years" class="form-control" min="1" max="100">
                    @error('depreciationForm.useful_life_years') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label class="form-label">Salvage Value <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" wire:model="depreciationForm.salvage_value" class="form-control" min="0">
                    @error('depreciationForm.salvage_value') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        @if($selectedMethodCode === 'declining_balance')
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Depreciation Rate (%)</label>
                        <input type="number" step="0.01" wire:model="depreciationForm.depreciation_rate" class="form-control" min="0" max="100">
                        @error('depreciationForm.depreciation_rate') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Declining Type</label>
                        <select wire:model="depreciationForm.declining_balance_type" class="form-control">
                            <option value="standard">Standard</option>
                            <option value="double">Double Declining</option>
                        </select>
                        @error('depreciationForm.declining_balance_type') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        @endif

        @if($selectedMethodCode === 'units_of_production')
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label">Expected Total Units <span class="text-danger">*</span></label>
                        <input type="number" step="0.0001" wire:model="depreciationForm.expected_total_units" class="form-control" min="0">
                        @error('depreciationForm.expected_total_units') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label">Unit Type <span class="text-danger">*</span></label>
                        <input type="text" wire:model="depreciationForm.unit_type" class="form-control" placeholder="e.g. hours, samples">
                        @error('depreciationForm.unit_type') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label">Current Units Used</label>
                        <input type="number" step="0.0001" wire:model="depreciationForm.current_units_used" class="form-control" min="0">
                        @error('depreciationForm.current_units_used') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label">Usage Source</label>
                        <select wire:model="depreciationForm.usage_source" class="form-control">
                            <option value="manual">Manual</option>
                            <option value="daily_log">Daily Log</option>
                        </select>
                    </div>
                </div>
            </div>
        @endif

        <div class="alert alert-light border mt-3">
            <strong>Computed values</strong> (updated after schedule generation):
            <div class="row mt-2">
                <div class="col-md-3"><small class="text-muted">Initial Book Value</small><div>{{ $depreciationForm['initial_book_value'] ?? '—' }}</div></div>
                <div class="col-md-3"><small class="text-muted">Current Book Value</small><div>{{ $depreciationForm['current_book_value'] ?? '—' }}</div></div>
                <div class="col-md-3"><small class="text-muted">Accumulated</small><div>{{ $depreciationForm['accumulated_depreciation'] ?? '—' }}</div></div>
                <div class="col-md-3"><small class="text-muted">Status</small><div>{{ ucfirst(str_replace('_', ' ', $depreciationForm['status'] ?? 'pending')) }}</div></div>
            </div>
        </div>
    @endif
</div>
