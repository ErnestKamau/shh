@if($activeModal !== '')
    @php
        $modalTitles = [
            'adjust' => ($adjustForm['direction'] ?? '') === 'reduce' ? 'Reduce line quantity' : 'Top up line quantity',
            'add_line' => 'Add a line',
            'validity' => 'Change validity',
            'details' => 'Edit details',
            'finalise' => ($finaliseForm['action'] ?? '') === 'cancel' ? 'Cancel purchase order' : 'Close purchase order',
        ];
        $saveActions = [
            'adjust' => 'saveAdjust',
            'add_line' => 'saveAddLine',
            'validity' => 'saveValidity',
            'details' => 'saveDetails',
            'finalise' => 'saveFinalise',
        ];
        $adjustLine = $activeModal === 'adjust' ? $po->lines->firstWhere('id', $adjustForm['line_id']) : null;
    @endphp

    <div class="modal fade show d-block cpo-modal-backdrop" tabindex="-1" role="dialog" aria-modal="true" wire:keydown.escape="closeModal">
        <div class="modal-dialog modal-dialog-centered {{ in_array($activeModal, ['add_line', 'details'], true) ? 'modal-lg' : '' }}" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modalTitles[$activeModal] ?? '' }}</h5>
                    <button type="button" class="close" wire:click="closeModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    @if($activeModal === 'adjust' && $adjustLine)
                        <p class="mb-3">
                            <strong>#{{ $adjustLine->line_no }} {{ $adjustLine->description }}</strong><br>
                            <span class="cpo-help">
                                {{ number_format($adjustLine->ordered_qty) }} ordered ·
                                {{ number_format($adjustLine->committed_qty + $adjustLine->reserved_qty) }} in use ·
                                {{ number_format($adjustLine->remaining_qty) }} remaining
                            </span>
                        </p>
                        <div class="ls-field mb-3">
                            <label class="ls-field__label" for="cpo-adjust-qty">
                                Quantity to {{ $adjustForm['direction'] === 'reduce' ? 'remove' : 'add' }}
                            </label>
                            <div class="ls-field__control">
                                <input id="cpo-adjust-qty" type="number" min="1" step="1" class="ls-field__input" wire:model="adjustForm.quantity" autofocus>
                            </div>
                            @error('adjustForm.quantity') <div class="cpo-error">{{ $message }}</div> @enderror
                            @if($adjustForm['direction'] === 'reduce')
                                <div class="cpo-help">You can remove at most {{ number_format($adjustLine->remaining_qty) }}; reserved and committed samples keep their cover.</div>
                            @endif
                        </div>
                        <div class="ls-field mb-0">
                            <label class="ls-field__label" for="cpo-adjust-reason">Reason <span class="text-danger">*</span></label>
                            <div class="ls-field__control">
                                <textarea id="cpo-adjust-reason" rows="2" class="ls-field__input" style="height:auto;" wire:model="adjustForm.reason" placeholder="e.g. Customer amended PO, revision 2"></textarea>
                            </div>
                            @error('adjustForm.reason') <div class="cpo-error">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    @if($activeModal === 'add_line')
                        @if($this->quotationDetailOptions->isNotEmpty())
                            <div class="ls-field mb-3">
                                <label class="ls-field__label" for="cpo-line-source">From the source quotation</label>
                                <div class="ls-field__control">
                                    <select id="cpo-line-source" class="ls-field__input" wire:model.live="lineSourceDetailId">
                                        <option value="">Manual line</option>
                                        @foreach($this->quotationDetailOptions as $detail)
                                            <option value="{{ $detail->id }}">{{ $detail->item_name ?: $detail->description }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-12">
                                <div class="ls-field mb-3">
                                    <label class="ls-field__label" for="cpo-line-description">Description <span class="text-danger">*</span></label>
                                    <div class="ls-field__control">
                                        <input id="cpo-line-description" type="text" class="ls-field__input" wire:model="lineForm.description" maxlength="500">
                                    </div>
                                    @error('line.description') <div class="cpo-error">{{ $message }}</div> @enderror
                                    @if(($lineForm['quotation_detail_id'] ?? null) === null)
                                        <div class="cpo-help">A manual line covers any sample type and analysis for this customer.</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="ls-field mb-3">
                                    <label class="ls-field__label" for="cpo-line-qty">Quantity <span class="text-danger">*</span></label>
                                    <div class="ls-field__control">
                                        <input id="cpo-line-qty" type="number" min="1" class="ls-field__input" wire:model="lineForm.ordered_qty">
                                    </div>
                                    @error('line.ordered_qty') <div class="cpo-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="ls-field mb-3">
                                    <label class="ls-field__label" for="cpo-line-price">Unit price (gross) <span class="text-danger">*</span></label>
                                    <div class="ls-field__control">
                                        <input id="cpo-line-price" type="number" min="0" step="0.01" class="ls-field__input" wire:model="lineForm.unit_price_gross">
                                    </div>
                                    @error('line.unit_price_gross') <div class="cpo-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="ls-field mb-3">
                                    <label class="ls-field__label" for="cpo-line-threshold">Alert when remaining ≤</label>
                                    <div class="ls-field__control">
                                        <input id="cpo-line-threshold" type="number" min="0" class="ls-field__input" placeholder="Off" wire:model="lineForm.notify_remaining_qty">
                                    </div>
                                    @error('line.notify_remaining_qty') <div class="cpo-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="ls-field mb-0">
                            <label class="ls-field__label" for="cpo-line-reason">Reason <span class="text-danger">*</span></label>
                            <div class="ls-field__control">
                                <textarea id="cpo-line-reason" rows="2" class="ls-field__input" style="height:auto;" wire:model="lineReason"></textarea>
                            </div>
                            @error('reason') <div class="cpo-error">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    @if($activeModal === 'validity')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="ls-field mb-3">
                                    <label class="ls-field__label" for="cpo-validity-from">Valid from</label>
                                    <div class="ls-field__control">
                                        <input id="cpo-validity-from" type="date" class="ls-field__input" wire:model="validityForm.valid_from">
                                    </div>
                                    @error('validityForm.valid_from') <div class="cpo-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="ls-field mb-3">
                                    <label class="ls-field__label" for="cpo-validity-to">Valid to <span class="text-danger">*</span></label>
                                    <div class="ls-field__control">
                                        <input id="cpo-validity-to" type="date" class="ls-field__input" wire:model="validityForm.valid_to">
                                    </div>
                                    @error('validityForm.valid_to') <div class="cpo-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="ls-field mb-0">
                            <label class="ls-field__label" for="cpo-validity-reason">Reason <span class="text-danger">*</span></label>
                            <div class="ls-field__control">
                                <textarea id="cpo-validity-reason" rows="2" class="ls-field__input" style="height:auto;" wire:model="validityForm.reason" placeholder="e.g. Customer extended PO to 31 Dec"></textarea>
                            </div>
                            @error('validityForm.reason') <div class="cpo-error">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    @if($activeModal === 'details')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="ls-field mb-3">
                                    <label class="ls-field__label" for="cpo-details-number">PO number</label>
                                    <div class="ls-field__control">
                                        <input id="cpo-details-number" type="text" class="ls-field__input" wire:model="detailsForm.po_number" maxlength="255">
                                    </div>
                                    @error('detailsForm.po_number') <div class="cpo-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="ls-field mb-3">
                                    <label class="ls-field__label" for="cpo-details-notice">Expiry reminder (days before)</label>
                                    <div class="ls-field__control">
                                        <input id="cpo-details-notice" type="number" min="0" max="365" class="ls-field__input" wire:model="detailsForm.expiry_notice_days">
                                    </div>
                                    @error('detailsForm.expiry_notice_days') <div class="cpo-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="ls-field mb-3">
                                    <label class="ls-field__label" for="cpo-details-mode">Invoicing</label>
                                    <div class="ls-field__control">
                                        <select id="cpo-details-mode" class="ls-field__input" wire:model.live="detailsForm.invoicing_mode">
                                            @foreach($invoicingModes as $mode)
                                                <option value="{{ $mode->value }}">{{ $mode->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('detailsForm.invoicing_mode') <div class="cpo-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            @if(($detailsForm['invoicing_mode'] ?? '') === 'periodic')
                                <div class="col-md-6">
                                    <div class="ls-field mb-3">
                                        <label class="ls-field__label" for="cpo-details-period">Invoice every</label>
                                        <div class="ls-field__control">
                                            <select id="cpo-details-period" class="ls-field__input" wire:model="detailsForm.invoicing_period">
                                                <option value="">Choose…</option>
                                                @foreach($invoicingPeriods as $period)
                                                    <option value="{{ $period->value }}">{{ $period->label() }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('detailsForm.invoicing_period') <div class="cpo-error">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($po->lines->isNotEmpty())
                            <label class="ls-field__label">Low-balance alerts</label>
                            <div class="ls-table-wrap mb-3">
                                <table class="table ls-table ls-table--dense mb-0">
                                    <tbody>
                                        @foreach($po->lines as $line)
                                            <tr>
                                                <td>#{{ $line->line_no }} {{ $line->description }}</td>
                                                <td style="width: 170px;">
                                                    <input type="number" min="0" class="ls-field__input text-right" placeholder="Off" wire:model="lineThresholds.{{ $line->id }}">
                                                    @error('lineThresholds.'.$line->id) <div class="cpo-error">{{ $message }}</div> @enderror
                                                    @error('line_thresholds.'.$line->id) <div class="cpo-error">{{ $message }}</div> @enderror
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <div class="ls-field mb-3">
                            <label class="ls-field__label" for="cpo-details-file">Replace PO file</label>
                            <div class="ls-field__control">
                                <input id="cpo-details-file" type="file" class="ls-field__input" accept=".pdf,.jpg,.jpeg,.png" wire:model="detailsFile">
                            </div>
                            <div wire:loading wire:target="detailsFile" class="cpo-help">Uploading…</div>
                            @error('file') <div class="cpo-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="ls-field mb-3">
                            <label class="ls-field__label" for="cpo-details-notes">Notes</label>
                            <div class="ls-field__control">
                                <textarea id="cpo-details-notes" rows="2" class="ls-field__input" style="height:auto;" wire:model="detailsForm.notes"></textarea>
                            </div>
                            @error('detailsForm.notes') <div class="cpo-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="ls-field mb-0">
                            <label class="ls-field__label" for="cpo-details-reason">Reason <span class="text-danger">*</span></label>
                            <div class="ls-field__control">
                                <textarea id="cpo-details-reason" rows="2" class="ls-field__input" style="height:auto;" wire:model="detailsForm.reason"></textarea>
                            </div>
                            @error('detailsForm.reason') <div class="cpo-error">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    @if($activeModal === 'finalise')
                        @if($finaliseForm['action'] === 'cancel')
                            <div class="cpo-note cpo-note--danger mb-3">
                                <i class="mdi mdi-alert-outline"></i>
                                Cancel only a PO recorded in error. It must not cover any received samples yet. Open reservations will be released.
                            </div>
                        @else
                            <div class="cpo-note cpo-note--warn mb-3">
                                <i class="mdi mdi-alert-outline"></i>
                                Closing stops any further cover from this PO. Open reservations ({{ number_format($totals['reserved']) }}) will be released;
                                jobs already received keep their cover and can still be invoiced. This cannot be undone.
                            </div>
                        @endif
                        <div class="ls-field mb-0">
                            <label class="ls-field__label" for="cpo-finalise-reason">Reason <span class="text-danger">*</span></label>
                            <div class="ls-field__control">
                                <textarea id="cpo-finalise-reason" rows="2" class="ls-field__input" style="height:auto;" wire:model="finaliseForm.reason"></textarea>
                            </div>
                            @error('finaliseForm.reason') <div class="cpo-error">{{ $message }}</div> @enderror
                        </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="closeModal">Cancel</button>
                    <button type="button"
                        class="btn btn-sm {{ $activeModal === 'finalise' ? 'btn-danger' : 'btn-quotation-primary' }}"
                        wire:click="{{ $saveActions[$activeModal] ?? 'closeModal' }}"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="{{ $saveActions[$activeModal] ?? 'closeModal' }}">
                            {{ $activeModal === 'finalise' ? (($finaliseForm['action'] ?? '') === 'cancel' ? 'Cancel PO' : 'Close PO') : 'Save' }}
                        </span>
                        <span wire:loading wire:target="{{ $saveActions[$activeModal] ?? 'closeModal' }}">Saving…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
