@if($this->isFood || $this->isWater)
    @php($hideOuterSection = $hideOuterSection ?? false)
    @if(! $hideOuterSection)
    <div class="form-section mb-3" x-data="{ open: false }">
        <button
            type="button"
            class="form-section-title font-weight-bold text-dark border-bottom pb-2 mb-0 w-100 text-left bg-transparent border-0 d-flex align-items-center justify-content-between"
            @click="open = !open; $nextTick(() => { if (typeof window.initTrfSignaturePads === 'function') window.initTrfSignaturePads(true); })"
        >
            <span>SAMPLE DETAILS</span>
            <i class="mdi" :class="open ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
        </button>
        <div class="pt-3" x-show="open" x-collapse>
    @endif
            @if($this->isFood)
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="bg-secondary text-white text-center small">
                            <tr>
                                <th style="width: 5%">S. No.</th>
                                <th style="width: 15%">Sample Description</th>
                                <th style="width: 15%">Sampling Point/Location</th>
                                <th style="width: 12%">Qty / Unit</th>
                                <th style="width: 12%">Sample Type</th>
                                <th style="width: 15%">Sample Condition</th>
                                <th style="width: 10%">Dates & Batch</th>
                                <th style="width: 10%">State</th>
                                <th style="width: 10%">Test Category</th>
                                <th style="width: 5%">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @foreach($formData['sample_rows'] ?? [] as $rowIdx => $row)
                                <tr wire:key="food-row-{{ $rowIdx }}">
                                    <td class="text-center align-middle font-weight-bold">{{ $rowIdx + 1 }}</td>
                                    <td>
                                        @include('livewire.partials.trf-sample-description-editor', ['rowIdx' => $rowIdx, 'row' => $row])
                                    </td>
                                    <td>
                                        <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.sampling_point" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Point/Loc">
                                    </td>
                                    <td>
                                        @include('livewire.partials.trf-sample-quantity-fields', ['rowIdx' => $rowIdx])
                                    </td>
                                    <td>
                                        <select wire:model="formData.sample_rows.{{ $rowIdx }}.sample_type" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;">
                                            <option value="">Select Type</option>
                                            <option value="Raw">Raw</option>
                                            <option value="Cooked">Cooked</option>
                                            <option value="Ready To Eat">Ready To Eat</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select wire:model="formData.sample_rows.{{ $rowIdx }}.sample_condition" class="form-control form-control-xs mb-1" style="padding: 2px 5px; height: auto; font-size: 11px;">
                                            <option value="">Select Cond.</option>
                                            <option value="Acceptable">Acceptable</option>
                                            <option value="Chilled">Chilled</option>
                                            <option value="Frozen">Frozen</option>
                                            <option value="Ambient">Ambient</option>
                                        </select>
                                        <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.sample_temp" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Temp (°C)">
                                    </td>
                                    <td>
                                        <label class="mb-0 text-muted" style="font-size: 9px;">Prod:</label>
                                        <input type="date" wire:model="formData.sample_rows.{{ $rowIdx }}.production_date" class="form-control form-control-xs p-1 mb-1" style="font-size: 10px; height: auto;">
                                        <label class="mb-0 text-muted" style="font-size: 9px;">Exp:</label>
                                        <input type="date" wire:model="formData.sample_rows.{{ $rowIdx }}.expiration_date" class="form-control form-control-xs p-1 mb-1" style="font-size: 10px; height: auto;">
                                        <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.batch_number" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Batch No.">
                                    </td>
                                    <td>
                                        <select wire:model="formData.sample_rows.{{ $rowIdx }}.state_of_sample" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;">
                                            <option value="">Select State</option>
                                            <option value="Liquid">L - Liquid</option>
                                            <option value="Semi Solid">SS - Semi Solid</option>
                                            <option value="Solid">S - Solid</option>
                                        </select>
                                    </td>
                                    <td>
                                        @include('workflow.forms.test-request.partials.test-category-radios', ['rowIdx' => $rowIdx, 'showLegionella' => false])
                                    </td>
                                    <td class="text-center align-middle">
                                        <button type="button" wire:click="removeSampleRow({{ $rowIdx }})" class="btn btn-danger btn-xs p-1"><i class="mdi mdi-trash-can"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif($this->isWater)
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="bg-secondary text-white text-center small">
                            <tr>
                                <th style="width: 5%">S. No.</th>
                                <th style="width: 15%">Sample Description</th>
                                <th style="width: 15%">Location</th>
                                <th style="width: 12%">Qty / Unit</th>
                                <th style="width: 12%">Sampling Point</th>
                                <th style="width: 20%">Field Data (pH, Cl, Temp, Odor)</th>
                                <th style="width: 15%">Test Requirements</th>
                                <th style="width: 5%">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @foreach($formData['sample_rows'] ?? [] as $rowIdx => $row)
                                <tr wire:key="water-row-{{ $rowIdx }}">
                                    <td class="text-center align-middle font-weight-bold">{{ $rowIdx + 1 }}</td>
                                    <td>
                                        @include('livewire.partials.trf-sample-description-editor', ['rowIdx' => $rowIdx, 'row' => $row])
                                    </td>
                                    <td>
                                        <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.location" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Location">
                                    </td>
                                    <td>
                                        @include('livewire.partials.trf-sample-quantity-fields', ['rowIdx' => $rowIdx])
                                    </td>
                                    <td>
                                        <select wire:model="formData.sample_rows.{{ $rowIdx }}.sampling_point" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;">
                                            <option value="">Select Point</option>
                                            <option value="Tap">Tap</option>
                                            <option value="Tank">Tank</option>
                                            <option value="Pool">Pool</option>
                                            <option value="Shower Head">Shower Head</option>
                                            <option value="Others">Others</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="row no-gutters">
                                            <div class="col-6 pr-1 mb-1">
                                                <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.ph" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="pH">
                                            </div>
                                            <div class="col-6 mb-1">
                                                <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.residual_chlorine" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Res. Cl">
                                            </div>
                                            <div class="col-6 pr-1">
                                                <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.sample_temp" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Temp (°C)">
                                            </div>
                                            <div class="col-6">
                                                <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.odor" class="form-control form-control-xs" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Odor">
                                            </div>
                                        </div>
                                        <input type="text" wire:model="formData.sample_rows.{{ $rowIdx }}.appearance" class="form-control form-control-xs mt-1" style="padding: 2px 5px; height: auto; font-size: 11px;" placeholder="Appearance">
                                    </td>
                                    <td>
                                        @include('workflow.forms.test-request.partials.test-category-radios', ['rowIdx' => $rowIdx, 'showLegionella' => true])
                                    </td>
                                    <td class="text-center align-middle">
                                        <button type="button" wire:click="removeSampleRow({{ $rowIdx }})" class="btn btn-danger btn-xs p-1"><i class="mdi mdi-trash-can"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            <div class="mt-2 text-right">
                <button type="button" wire:click="addSampleRow" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-plus mr-1"></i> Add Sample Row</button>
            </div>
    @if(! $hideOuterSection)
        </div>
    </div>
    @endif
@endif
