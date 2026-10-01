<div class="cpo-ls-create ls-quotation-shell">
    @include('layouts.lab.partials.ls-ui.quotation.ls-quotation-overview-styles')

    @php
        $steps = [
            1 => ['label' => 'Customer & quotation', 'icon' => 'mdi-account-tie-outline'],
            2 => ['label' => 'Lines', 'icon' => 'mdi-format-list-numbered'],
            3 => ['label' => 'Validity & invoicing', 'icon' => 'mdi-calendar-range'],
            4 => ['label' => 'Review', 'icon' => 'mdi-check-decagram-outline'],
        ];
        $priceWarnings = $this->priceWarnings;
        $totals = $this->totals;
        $currencyCode = $this->currencyCode;
    @endphp

    <div class="batch-header-bar cpo-burgundy-header mb-3">
        <div class="cpo-burgundy-header__top">
            <div class="cpo-burgundy-header__identity" style="flex-direction: column; align-items: flex-start;">
                <a href="{{ route('billing.customer-purchase-orders') }}" class="cpo-burgundy-header__back">
                    <i class="mdi mdi-arrow-left"></i>
                    Back to purchase orders
                </a>
                <h2 class="cpo-burgundy-header__title">
                    <i class="mdi mdi-file-document-plus-outline"></i>
                    New blanket purchase order
                </h2>
            </div>
        </div>
        <p class="cpo-burgundy-header__subtitle">
            A blanket PO covers many enquiries for one customer until its quantity runs out or it expires.
            Lines are pre-filled from the source quotation; prices are VAT inclusive.
        </p>
    </div>

    <ol class="cpo-stepper mb-3">
        @foreach($steps as $number => $meta)
            <li class="cpo-stepper__item {{ $step === $number ? 'is-current' : '' }} {{ $step > $number ? 'is-done' : '' }}">
                <button type="button" class="cpo-stepper__btn" wire:click="goToStep({{ $number }})" @disabled($number >= $step)>
                    <span class="cpo-stepper__dot">
                        @if($step > $number)
                            <i class="mdi mdi-check"></i>
                        @else
                            {{ $number }}
                        @endif
                    </span>
                    <span class="cpo-stepper__label"><i class="mdi {{ $meta['icon'] }}"></i> {{ $meta['label'] }}</span>
                </button>
            </li>
        @endforeach
    </ol>

    <div class="ls-soft-card is-expanded mb-3">
        <div class="ls-soft-card__body">
            {{-- Step 1: customer, PO number, source quotation --}}
            @if($step === 1)
                <div class="row">
                    <div class="col-lg-6">
                        <div class="ls-field mb-3">
                            <label class="ls-field__label" for="cpo-customer-search">Customer <span class="text-danger">*</span></label>
                            @if($this->selectedCustomer)
                                <div class="cpo-chip">
                                    <i class="mdi mdi-domain"></i>
                                    <strong>{{ $this->selectedCustomer->name }}</strong>
                                    <button type="button" class="cpo-chip__clear" wire:click="clearCustomer" aria-label="Change customer">
                                        <i class="mdi mdi-close"></i>
                                    </button>
                                </div>
                            @else
                                <div class="ls-field__control position-relative">
                                    <input id="cpo-customer-search" type="text" class="ls-field__input" placeholder="Type at least 2 letters of the customer name…"
                                        wire:model.live.debounce.300ms="customerSearch" autocomplete="off">
                                    @if($this->customerOptions->isNotEmpty())
                                        <div class="cpo-dropdown">
                                            @foreach($this->customerOptions as $customer)
                                                <button type="button" class="cpo-dropdown__item" wire:click="selectCustomer('{{ $customer->id }}')">
                                                    {{ $customer->name }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                            @error('form.customer_id') <div class="cpo-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="ls-field mb-3">
                            <label class="ls-field__label" for="cpo-po-number">Customer PO number <span class="text-danger">*</span></label>
                            <div class="ls-field__control">
                                <input id="cpo-po-number" type="text" class="ls-field__input" wire:model.live.debounce.400ms="form.po_number" maxlength="255" placeholder="As printed on the customer's PO">
                            </div>
                            @error('form.po_number') <div class="cpo-error">{{ $message }}</div> @enderror
                            @if($this->matchingPoNumbers->isNotEmpty())
                                <div class="cpo-note cpo-note--warn mt-2">
                                    <i class="mdi mdi-alert-outline"></i>
                                    This number is already recorded for this customer:
                                    @foreach($this->matchingPoNumbers as $match)
                                        <a href="{{ route('billing.customer-purchase-orders.show', $match->id) }}" target="_blank">
                                            {{ $match->po_type?->label() ?? 'PO' }} · {{ $match->effectiveStatus()->label() }}
                                        </a>@if(! $loop->last), @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="ls-field mb-3">
                            <label class="ls-field__label" for="cpo-quotation">Source quotation</label>
                            <div class="ls-field__control">
                                <select id="cpo-quotation" class="ls-field__input" wire:model.live="form.quotation_header_id" @disabled($form['customer_id'] === '')>
                                    <option value="">{{ $form['customer_id'] === '' ? 'Choose a customer first' : 'No quotation (enter lines manually)' }}</option>
                                    @foreach($this->quotationOptions as $quotation)
                                        <option value="{{ $quotation->id }}">
                                            {{ $quotation->quote_number ?: 'Quote' }} · {{ optional($quotation->created_at)->format('d M Y') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('form.quotation_header_id') <div class="cpo-error">{{ $message }}</div> @enderror
                            <div class="cpo-help">Only completed, current quotations for this customer are listed. Choosing one replaces the lines with its items.</div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Step 2: lines --}}
            @if($step === 2)
                @error('lines') <div class="cpo-note cpo-note--danger mb-3"><i class="mdi mdi-alert-circle-outline"></i> {{ $message }}</div> @enderror

                <div class="ls-table-wrap mb-3">
                    <table class="table ls-table ls-table--dense mb-0 cpo-lines-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>Description</th>
                                <th style="width: 120px;" class="text-right">Quantity</th>
                                <th style="width: 150px;" class="text-right">Unit price (gross)</th>
                                <th style="width: 130px;" class="text-right">Alert when ≤</th>
                                <th style="width: 130px;" class="text-right">Line value</th>
                                <th style="width: 48px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lines as $index => $line)
                                <tr wire:key="cpo-line-{{ $index }}">
                                    <td class="text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <input type="text" class="ls-field__input" wire:model.blur="lines.{{ $index }}.description" maxlength="500">
                                        <div class="cpo-help mt-1">
                                            @if(filled($line['sample_type_name'] ?? null))
                                                <span class="ls-pill ls-pill--info">{{ $line['sample_type_name'] }}</span>
                                            @endif
                                            @if(! empty($line['is_package']))
                                                <span class="ls-pill ls-pill--open">Package</span>
                                            @endif
                                            @if(! empty($line['analysis_type_ids']))
                                                {{ count($line['analysis_type_ids']) }} analysis type(s)
                                            @elseif(filled($line['quotation_detail_id'] ?? null))
                                                Any analysis
                                            @else
                                                Manual line · covers any sample type and analysis
                                            @endif
                                        </div>
                                        @error("lines.$index.description") <div class="cpo-error">{{ $message }}</div> @enderror
                                    </td>
                                    <td>
                                        <input type="number" min="1" step="1" class="ls-field__input text-right" wire:model.live.debounce.400ms="lines.{{ $index }}.ordered_qty">
                                        @error("lines.$index.ordered_qty") <div class="cpo-error">{{ $message }}</div> @enderror
                                    </td>
                                    <td>
                                        <input type="number" min="0" step="0.01" class="ls-field__input text-right" wire:model.live.debounce.400ms="lines.{{ $index }}.unit_price_gross">
                                        @error("lines.$index.unit_price_gross") <div class="cpo-error">{{ $message }}</div> @enderror
                                        @isset($priceWarnings[$index])
                                            <div class="cpo-note cpo-note--warn mt-1">Quoted {{ number_format($priceWarnings[$index], 2) }}</div>
                                        @endisset
                                    </td>
                                    <td>
                                        <input type="number" min="0" step="1" class="ls-field__input text-right" placeholder="Off" wire:model.blur="lines.{{ $index }}.notify_remaining_qty">
                                        @error("lines.$index.notify_remaining_qty") <div class="cpo-error">{{ $message }}</div> @enderror
                                    </td>
                                    <td class="text-right align-middle">
                                        {{ number_format((int) ($line['ordered_qty'] ?? 0) * (float) ($line['unit_price_gross'] ?? 0), 2) }}
                                    </td>
                                    <td class="text-right align-middle">
                                        <button type="button" class="btn btn-sm btn-link text-danger p-0" wire:click="removeLine({{ $index }})" title="Remove line">
                                            <i class="mdi mdi-delete-outline" style="font-size: 1.15rem;"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No lines yet. Pick a source quotation in step 1, or add a line manually.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($lines) > 0)
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-right">Total</th>
                                    <th class="text-right">{{ number_format($totals['quantity']) }}</th>
                                    <th colspan="2"></th>
                                    <th class="text-right">{{ $currencyCode }} {{ number_format($totals['value'], 2) }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                <button type="button" class="ls-btn ls-btn--secondary-fill" wire:click="addBlankLine">
                    <i class="mdi mdi-plus"></i> Add line
                </button>

                @if(! empty($priceWarnings))
                    <div class="cpo-note cpo-note--warn mt-3">
                        <i class="mdi mdi-alert-outline"></i>
                        {{ count($priceWarnings) }} line(s) differ from the quotation price. The PO price is what will be invoiced for covered samples.
                    </div>
                @endif
            @endif

            {{-- Step 3: validity, invoicing, file --}}
            @if($step === 3)
                <div class="row">
                    <div class="col-md-4">
                        <div class="ls-field mb-3">
                            <label class="ls-field__label" for="cpo-valid-from">Valid from <span class="text-danger">*</span></label>
                            <div class="ls-field__control">
                                <input id="cpo-valid-from" type="date" class="ls-field__input" wire:model.live="form.valid_from">
                            </div>
                            @error('form.valid_from') <div class="cpo-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="ls-field mb-3">
                            <label class="ls-field__label" for="cpo-valid-to">Valid to <span class="text-danger">*</span></label>
                            <div class="ls-field__control">
                                <input id="cpo-valid-to" type="date" class="ls-field__input" wire:model.live="form.valid_to">
                            </div>
                            @error('form.valid_to') <div class="cpo-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="ls-field mb-3">
                            <label class="ls-field__label" for="cpo-expiry-notice">Expiry reminder (days before)</label>
                            <div class="ls-field__control">
                                <input id="cpo-expiry-notice" type="number" min="0" max="365" class="ls-field__input" wire:model.blur="form.expiry_notice_days">
                            </div>
                            @error('form.expiry_notice_days') <div class="cpo-error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="ls-field mb-3">
                            <label class="ls-field__label" for="cpo-invoicing-mode">Invoicing <span class="text-danger">*</span></label>
                            <div class="ls-field__control">
                                <select id="cpo-invoicing-mode" class="ls-field__input" wire:model.live="form.invoicing_mode">
                                    @foreach($invoicingModes as $mode)
                                        <option value="{{ $mode->value }}">{{ $mode->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('form.invoicing_mode') <div class="cpo-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    @if($form['invoicing_mode'] === 'periodic')
                        <div class="col-md-4">
                            <div class="ls-field mb-3">
                                <label class="ls-field__label" for="cpo-invoicing-period">Invoice every <span class="text-danger">*</span></label>
                                <div class="ls-field__control">
                                    <select id="cpo-invoicing-period" class="ls-field__input" wire:model.live="form.invoicing_period">
                                        <option value="">Choose…</option>
                                        @foreach($invoicingPeriods as $period)
                                            <option value="{{ $period->value }}">{{ $period->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('form.invoicing_period') <div class="cpo-error">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    @endif
                    <div class="col-md-4">
                        <div class="ls-field mb-3">
                            <label class="ls-field__label" for="cpo-currency">Currency</label>
                            <div class="ls-field__control">
                                <select id="cpo-currency" class="ls-field__input" wire:model.live="form.currency_id">
                                    <option value="">Not set</option>
                                    @foreach($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('form.currency_id') <div class="cpo-error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="ls-field mb-3">
                            <label class="ls-field__label" for="cpo-file">PO file (optional)</label>
                            <div class="ls-field__control">
                                <input id="cpo-file" type="file" class="ls-field__input" accept=".pdf,.jpg,.jpeg,.png" wire:model="file">
                            </div>
                            <div wire:loading wire:target="file" class="cpo-help">Uploading…</div>
                            @error('file') <div class="cpo-error">{{ $message }}</div> @enderror
                            <div class="cpo-help">PDF, JPEG or PNG, up to 10 MB.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="ls-field mb-3">
                            <label class="ls-field__label" for="cpo-notes">Notes</label>
                            <div class="ls-field__control">
                                <textarea id="cpo-notes" rows="3" class="ls-field__input" style="height:auto;" wire:model.blur="form.notes" maxlength="5000"></textarea>
                            </div>
                            @error('form.notes') <div class="cpo-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            @endif

            {{-- Step 4: review --}}
            @if($step === 4)
                <div class="row">
                    <div class="col-lg-5 mb-3">
                        <dl class="cpo-review">
                            <dt>Customer</dt><dd>{{ $this->selectedCustomer?->name ?? '—' }}</dd>
                            <dt>PO number</dt><dd>{{ $form['po_number'] }}</dd>
                            <dt>Source quotation</dt>
                            <dd>{{ $this->quotationOptions->firstWhere('id', $form['quotation_header_id'])?->quote_number ?? 'None' }}</dd>
                            <dt>Validity</dt>
                            <dd>{{ \Carbon\Carbon::parse($form['valid_from'])->format('d M Y') }} – {{ \Carbon\Carbon::parse($form['valid_to'])->format('d M Y') }}</dd>
                            <dt>Invoicing</dt>
                            <dd>
                                {{ \App\Enums\Commercial\PurchaseOrderInvoicingMode::from($form['invoicing_mode'])->label() }}
                                @if($form['invoicing_mode'] === 'periodic' && $form['invoicing_period'] !== '')
                                    ({{ \App\Enums\Commercial\PurchaseOrderInvoicingPeriod::from($form['invoicing_period'])->label() }})
                                @endif
                            </dd>
                            <dt>Expiry reminder</dt><dd>{{ (int) $form['expiry_notice_days'] }} days before</dd>
                            <dt>File</dt><dd>{{ $file?->getClientOriginalName() ?? 'None' }}</dd>
                        </dl>
                    </div>
                    <div class="col-lg-7 mb-3">
                        <div class="ls-table-wrap">
                            <table class="table ls-table ls-table--dense mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Description</th>
                                        <th class="text-right">Qty</th>
                                        <th class="text-right">Unit (gross)</th>
                                        <th class="text-right">Alert ≤</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lines as $index => $line)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                {{ $line['description'] }}
                                                @isset($priceWarnings[$index])
                                                    <i class="mdi mdi-alert-outline text-warning" title="Differs from quoted {{ number_format($priceWarnings[$index], 2) }}"></i>
                                                @endisset
                                            </td>
                                            <td class="text-right">{{ number_format((int) $line['ordered_qty']) }}</td>
                                            <td class="text-right">{{ number_format((float) $line['unit_price_gross'], 2) }}</td>
                                            <td class="text-right">{{ ($line['notify_remaining_qty'] ?? '') === '' || $line['notify_remaining_qty'] === null ? '—' : $line['notify_remaining_qty'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" class="text-right">Total</th>
                                        <th class="text-right">{{ number_format($totals['quantity']) }}</th>
                                        <th class="text-right" colspan="2">{{ $currencyCode }} {{ number_format($totals['value'], 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <div>
            @if($step > 1)
                <button type="button" class="ls-btn ls-btn--secondary-fill" wire:click="previousStep">
                    <i class="mdi mdi-chevron-left"></i> Back
                </button>
            @endif
        </div>
        <div>
            @if($step < 4)
                <button type="button" class="ls-btn ls-btn--primary" wire:click="nextStep" wire:loading.attr="disabled">
                    Continue <i class="mdi mdi-chevron-right"></i>
                </button>
            @else
                <button type="button" class="ls-btn ls-btn--primary" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save"><i class="mdi mdi-content-save-outline"></i> Create purchase order</span>
                    <span wire:loading wire:target="save">Creating…</span>
                </button>
            @endif
        </div>
    </div>

    @include('livewire.billing.partials.customer-purchase-order-header-styles')
    @include('livewire.billing.partials.customer-purchase-order-ledger-styles')
</div>
