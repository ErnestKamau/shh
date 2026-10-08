{{--
    Shared PO capture fields for the quotation-acceptance / Record PO modals.
    Host component uses App\Livewire\Concerns\CapturesEnquiryPurchaseOrder and owns $clientPoNumber / $poRuleMessage.
    $poNumberInputId: optional id for the PO number input.
--}}
@php
    $poNumberInputId = $poNumberInputId ?? 'clientPoNumber';
    $poCaptureEnabled = \App\Services\Commercial\EnquiryPurchaseOrderService::enabled() && $poCaptureRequirement !== '';
    $poNoneLabel = match ($poCaptureRequirement) {
        \App\Services\Commercial\EnquiryPurchaseOrderService::REQUIREMENT_REQUIRED => 'PO to follow',
        \App\Services\Commercial\EnquiryPurchaseOrderService::REQUIREMENT_OPTIONAL => 'No PO',
        default => 'Skip PO',
    };
@endphp

@if($poRuleMessage !== '')
    <div class="alert alert-info py-2 mb-3 small">
        {{ $poRuleMessage }}
    </div>
@endif

@if($poCaptureEnabled)
    <div class="form-group">
        <label class="d-block mb-1">Purchase order</label>
        <div class="d-flex flex-wrap" style="gap: 4px 16px;">
            @if($poCaptureBlanketOptions !== [])
                <div class="custom-control custom-radio">
                    <input type="radio" id="{{ $poNumberInputId }}-mode-blanket" class="custom-control-input" value="blanket" wire:model.live="poCaptureMode">
                    <label class="custom-control-label" for="{{ $poNumberInputId }}-mode-blanket">Purchase orders</label>
                </div>
            @endif
            @unless($poCaptureBlanketOnly)
                <div class="custom-control custom-radio">
                    <input type="radio" id="{{ $poNumberInputId }}-mode-single" class="custom-control-input" value="single" wire:model.live="poCaptureMode">
                    <label class="custom-control-label" for="{{ $poNumberInputId }}-mode-single">PO for this request</label>
                </div>
                <div class="custom-control custom-radio">
                    <input type="radio" id="{{ $poNumberInputId }}-mode-none" class="custom-control-input" value="none" wire:model.live="poCaptureMode">
                    <label class="custom-control-label" for="{{ $poNumberInputId }}-mode-none">{{ $poNoneLabel }}</label>
                </div>
            @endunless
        </div>
        @error('mode')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @enderror
        @error('po_skipped')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @enderror
    </div>

    @if($poCaptureMode === 'blanket')
        <div class="form-group">
            <label for="{{ $poNumberInputId }}-blanket">Purchase order</label>
            <select id="{{ $poNumberInputId }}-blanket" class="form-control" wire:model.live="poCaptureBlanketId">
                <option value="">Select a purchase order…</option>
                @foreach($poCaptureBlanketOptions as $option)
                    <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
            @error('customer_purchase_order_id')
                <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
            <small class="form-text text-muted">The PO document is kept on the purchase order record.</small>
        </div>

        @if(($poCaptureCoverage['rows'] ?? []) !== [])
            <div class="border rounded p-2 mb-3 small" wire:loading.class="opacity-50" wire:target="poCaptureBlanketId,poCaptureMode">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong>Tests covered by po</strong>
                    @if(($poCaptureCoverage['uncovered'] ?? 0) === 0)
                        <span class="badge badge-success">All {{ $poCaptureCoverage['requested'] }} covered</span>
                    @else
                        <span class="badge badge-warning">{{ $poCaptureCoverage['covered'] }} of {{ $poCaptureCoverage['requested'] }} covered</span>
                    @endif
                </div>
                @php
                    $poCaptureCoverageGroups = collect($poCaptureCoverage['rows'])
                        ->groupBy(fn ($row) => $row['analysis_type'] ?: 'Other');
                @endphp
                <table class="table table-sm mb-1">
                    <thead>
                        <tr>
                            <th>Samples</th>
                            <th class="text-right">Requested</th>
                            <th class="text-right">Covered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($poCaptureCoverageGroups as $analysisType => $groupRows)
                            <tr>
                                <td colspan="3" class="font-weight-bold bg-light">{{ $analysisType }}</td>
                            </tr>
                            @foreach($groupRows as $row)
                                <tr>
                                    <td class="pl-3">
                                        @if($row['analyte'])
                                            {{ $row['analyte'] }}
                                        @elseif($row['package_name'])
                                            <div>{{ $row['package_name'] }} <span class="text-muted">(package)</span></div>
                                            @if($row['package_parameters'] !== [])
                                                <ul class="list-unstyled mb-0 mt-1">
                                                    @foreach($row['package_parameters'] as $parameter)
                                                        <li class="{{ $parameter['covered'] ? 'text-success' : 'text-muted' }}">
                                                            <i class="mdi {{ $parameter['covered'] ? 'mdi-check-circle' : 'mdi-close-circle-outline' }}"></i>
                                                            {{ $parameter['name'] }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        @else
                                            {{ $row['sample_type'] ?: $row['label'] }}
                                        @endif
                                        @if($row['reason'])
                                            <div class="text-muted">{{ $row['reason'] }}</div>
                                        @endif
                                    </td>
                                    <td class="text-right">{{ $row['requested'] }}</td>
                                    <td class="text-right {{ $row['uncovered'] > 0 ? 'text-warning font-weight-bold' : '' }}">{{ $row['covered'] }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
                @if(($poCaptureCoverage['uncovered'] ?? 0) > 0)
                    <div class="text-muted">
                        {{ $poCaptureCoverage['uncovered'] }} sample(s) are not covered. They will be held as Awaiting PO when the job is created.
                    </div>
                @endif
            </div>
        @endif
    @elseif($poCaptureMode === 'single')
        <div class="form-group">
            <label for="{{ $poNumberInputId }}">Client PO number</label>
            <input type="text" id="{{ $poNumberInputId }}" class="form-control" wire:model.defer="clientPoNumber">
            @error('client_po_number')
                <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
            <small class="form-text text-muted">The PO covers the quantities on the accepted quotation.</small>
        </div>
    @elseif($poCaptureRequirement === \App\Services\Commercial\EnquiryPurchaseOrderService::REQUIREMENT_REQUIRED)
        <div class="alert alert-warning py-2 mb-3 small">
            No PO yet. The request can still be received, but its samples will be held as Awaiting PO until a PO is applied.
        </div>
    @endif
@else
    <div class="form-group">
        <label for="{{ $poNumberInputId }}">Client PO number</label>
        <input type="text" id="{{ $poNumberInputId }}" class="form-control" wire:model.defer="clientPoNumber">
        @error('client_po_number')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @enderror
    </div>
@endif
