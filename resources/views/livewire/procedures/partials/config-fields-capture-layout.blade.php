<div class="pw-capture-layout-panel mb-4">
    <div class="d-flex align-items-start gap-3 flex-wrap">
        <div class="pw-capture-layout-panel__icon">
            <i class="mdi mdi-arrow-collapse-vertical"></i>
        </div>
        <div class="flex-grow-1" style="min-width: 240px;">
            <h6 class="fw-semibold mb-1">Configurable fields placement during capture</h6>
            <p class="text-muted small mb-3 mb-md-2">
                All configurable fields on this worksheet appear
                <strong>{{ $config_fields_placement === 'top' ? 'above' : 'below' }}</strong>
                procedure steps and custom tables during batch capture (same pattern as log entry worksheets).
            </p>
            <div class="row align-items-end g-2">
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Placement</label>
                    @php
                        $placementOptions = [
                            ['value' => 'top', 'label' => 'Top — above procedure steps and tables'],
                            ['value' => 'bottom', 'label' => 'Bottom — below procedure steps and test kit'],
                        ];
                    @endphp
                    @include('livewire.procedures.partials.config-search-select', [
                        'options' => $placementOptions,
                        'wireModel' => 'config_fields_placement',
                        'placeholder' => 'Search placement...',
                        'emptyMessage' => 'No placement matches your search.',
                        'inputRef' => 'placementSearch',
                        'live' => true,
                        'pickerKey' => 'config-field-placement-' . ($config_fields_placement ?? 'top'),
                    ])
                    @error('config_fields_placement') <small class="text-danger d-block">{{ $message }}</small> @enderror
                </div>
                @if($showSaveButton ?? false)
                <div class="col-md-auto">
                    <button type="button" class="btn btn-outline-primary" wire:click="saveConfigFieldsCaptureSettings">
                        <i class="mdi mdi-content-save-outline"></i> Save layout
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
