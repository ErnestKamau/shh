@php
    $customers = $customers ?? collect();
    $currencies = $currencies ?? \App\Models\Currency::query()->orderBy('code')->get();
    $labSections = $labSections ?? \App\SampleAnalysisStage::query()
        ->where('active', 1)
        ->where(function ($query): void {
            $query->where('is_sample_stage', 0)->orWhereNull('is_sample_stage');
        })
        ->orderBy('name')
        ->get(['id', 'name', 'code']);

    $clientOptions = $customers->map(fn ($customer) => [
        'value' => (string) $customer->id,
        'label' => (string) $customer->name,
        'meta' => ['currency_id' => $customer->currency_id ? (string) $customer->currency_id : ''],
    ])->values()->all();

    $labSectionOptions = $labSections->mapWithKeys(fn ($section) => [
        (string) $section->id => [
            'label' => (string) $section->name,
            'meta' => (string) ($section->code ?: ''),
        ],
    ])->all();

    $currencyOptions = $currencies->map(fn ($currency) => [
        'value' => (string) $currency->id,
        'label' => trim($currency->code.' - '.$currency->description),
    ])->values()->all();

    $defaultCurrencyId = optional($currencies->first())->id;
@endphp
@include('layouts.lab.invoice.partials.add-quotation-modal-styles')
<div class="modal fade ls-ui-kit" id="add-quotation" role="dialog" aria-labelledby="add-quotation-title">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('add-quotation-header') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header quotation-modal-header">
                <div>
                    <h4 class="modal-title" id="add-quotation-title">
                        <i class="mdi mdi-file-document-plus-outline"></i> New Quotation
                    </h4>
                    <p class="modal-subtitle">Select client, contact, lab section(s), and quotation type to continue.</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body quotation-modal-body">
                <div class="ls-form-panel mb-3">
                    <h4 class="ls-form-panel__title"><i class="mdi mdi-account-outline"></i> Client &amp; type</h4>
                    <div class="ls-form-grid ls-form-grid--3">
                        @include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
                            'label' => 'Client',
                            'id' => 'select-client',
                            'name' => 'client',
                            'required' => true,
                            'placeholder' => 'Type to search clients…',
                            'options' => $clientOptions,
                            'selected' => null,
                            'disableSuccess' => true,
                        ])

                        @include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
                            'label' => 'Client Contact',
                            'id' => 'select-client-contact',
                            'name' => 'client_contact',
                            'required' => true,
                            'placeholder' => 'Select client first…',
                            'options' => [],
                            'selected' => null,
                            'disableSuccess' => true,
                            'hint' => 'Loaded from the selected client.',
                        ])

                        @include('layouts.lab.partials.ls-ui.fields.ls-field-status-select', [
                            'label' => 'Quotation Type',
                            'id' => 'select-quotation-type',
                            'name' => 'quotation_type',
                            'required' => true,
                            'selected' => 'Analysis',
                            'options' => [
                                ['value' => 'Analysis', 'label' => 'Analysis Quotation', 'color' => '#2563eb'],
                                ['value' => 'General', 'label' => 'General Quotation', 'color' => '#64748b'],
                            ],
                        ])
                    </div>
                </div>

                <div class="ls-form-panel mb-3">
                    <h4 class="ls-form-panel__title"><i class="mdi mdi-map-marker-outline"></i> Location and Lab Section</h4>
                    <div class="ls-form-grid ls-form-grid--3">
                        @include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-dropdown-search', [
                            'label' => 'Company Unit',
                            'id' => 'select-company-unit',
                            'name' => 'crm_company_unit_id',
                            'multiple' => false,
                            'selected' => [],
                            'options' => [],
                            'placeholder' => 'Select company unit…',
                            'variant' => 'slate',
                        ])

                        @include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-dropdown-search', [
                            'label' => 'Sampling Location',
                            'id' => 'select-sample-point',
                            'name' => 'sample_point_id',
                            'multiple' => false,
                            'selected' => [],
                            'options' => [],
                            'placeholder' => 'Select sample point…',
                            'variant' => 'slate',
                        ])

                        @include('layouts.lab.partials.ls-ui.select2.ls-select2-multi-columns', [
                            'label' => 'Lab Section(s)',
                            'id' => 'select-lab-sections',
                            'name' => 'lab_section_ids',
                            'required' => true,
                            'selected' => [],
                            'options' => $labSectionOptions,
                            'placeholder' => 'Select lab section(s)…',
                            'hint' => 'A quotation can cover one or more lab sections.',
                        ])
                    </div>
                </div>

                <div class="ls-form-panel mb-3 mb-0">
                    <h4 class="ls-form-panel__title"><i class="mdi mdi-calendar-outline"></i> Schedule</h4>
                    <div class="ls-form-grid ls-form-grid--3">
                        @include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
                            'label' => 'Quotation Date',
                            'id' => 'quotation-date',
                            'name' => 'quotation_date',
                            'type' => 'date',
                            'required' => true,
                            'value' => now()->format('Y-m-d'),
                        ])

                        @include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
                            'label' => 'Expiry Date',
                            'id' => 'expire-date',
                            'name' => 'expire_date',
                            'type' => 'date',
                            'required' => true,
                            'value' => now()->addDays(30)->format('Y-m-d'),
                        ])

                        @include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
                            'label' => 'Currency',
                            'id' => 'select-currency',
                            'name' => 'currency_id',
                            'required' => true,
                            'placeholder' => 'Type to search currency…',
                            'options' => $currencyOptions,
                            'selected' => $defaultCurrencyId ? (string) $defaultCurrencyId : null,
                            'disableSuccess' => true,
                            'hint' => 'Defaults from the selected client when available.',
                        ])
                    </div>
                </div>
            </div>

            <div class="modal-footer quotation-modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-quotation-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-quotation-primary">
                    <i class="mdi mdi-arrow-right"></i> Continue
                </button>
            </div>
        </form>
    </div>
</div>
