@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])



@section('title2')
<title>Lab — Quotation {{ $header->quote_number }}</title>
@endsection
@section('content2')
<main class="container-fluid lab-surface-theme ls-admin-page quotation-show-page ls-quotation-shell ls-ui-kit" data-ls-type="plex">
    @include('layouts.lab.partials.lab-surface-theme-styles')
    @include('layouts.lab.partials.ls-ui.ls-ui-tokens-and-styles')
    @include('layouts.lab.partials.ls-ui.quotation.ls-quotation-overview-styles')
    @include('layouts.lab.invoice.partials.quotation-show-styles')
    @include('layouts.lab.invoice.partials.quotation-preview-hover-styles')

    <?php
    $items = array(
        array(
            'link' => route('lab-home'),
            'name' => 'Lab',
            'icon' => null
        ),
        array(
            'link' => route('quotation-index'),
            'name' => 'Billing-Quotation',
            'icon' => null
        ),
        array(
            'link' => route('quotation-index', ['stage' => $header->status]),
            'name' => $header->status,
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => $header->quote_number,
            'icon' => null
        ),


    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0 ls-quotation-header-card">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap: 12px;">
                        <div>
                            <h2 class="mb-1 quotation-preview-hover-parent">
                                <i class="mdi mdi-file-document-edit-outline text-primary"></i>
                                Quotation {{ $header->quote_number }}
                                @if(($header->revision_number ?? 1) > 1)
                                    <span class="badge badge-info ml-1">Rev. {{ $header->revision_number }}</span>
                                @endif
                                @if($header->isSuperseded())
                                    <span class="badge badge-secondary ml-1">Superseded</span>
                                @endif
                            </h2>
                            <p class="text-muted mb-0">{{ $header->status }} · Configure header, lines, and terms</p>
                            <div class="d-flex flex-wrap mt-2" style="gap: 0.35rem;">
                                @if($header->status == 'Quote In Approval')
                                    @if((int) $header->is_approved === 1)
                                        <span class="ls-quotation-meta-chip ls-quotation-meta-chip--ok"><i class="mdi mdi-thumb-up"></i> Approved</span>
                                    @else
                                        <span class="ls-quotation-meta-chip ls-quotation-meta-chip--warn"><i class="mdi mdi-alert-decagram"></i> Awaiting Approval</span>
                                        @if((string) $header->approved_by !== (string) auth()->id())
                                            <span class="ls-quotation-meta-chip ls-quotation-meta-chip--danger"><i class="mdi mdi-account-alert"></i> Approver — {{ getUserById($header->approved_by)->name ?? '-' }}</span>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                            <div class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle btn btn-sm btn-outline-secondary" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="mdi mdi-compare-vertical"></i> Move To workflow
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" style="font-size: 13px;" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{route('change_quotation_workflow',['id'=>$header->id,'stage'=>'Quote In Preparation'])}}"><i class="mdi mdi-subdirectory-arrow-right"></i> Quotation In Preparation</a>
                                    @if(in_array($header->status,['Quote In Approval','Quote Complete'], true))
                                    <a class="dropdown-item" href="{{route('change_quotation_workflow',['id'=>$header->id,'stage'=>'Quote In Approval'])}}"><i class="mdi mdi-subdirectory-arrow-right"></i> Quotation In Approval</a>
                                    <a class="dropdown-item" href="{{route('change_quotation_workflow',['id'=>$header->id,'stage'=>'Quote Complete'])}}"><i class="mdi mdi-subdirectory-arrow-right"></i> Quotation Complete</a>
                                    @endif
                                </div>
                            </div>
                            <div class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle btn btn-sm btn-outline-secondary" href="#" id="quotationActionsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical"></i> Actions
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" style="font-size: 13px;" aria-labelledby="quotationActionsDropdown">
                                    @if(sizeof($details) > 0)
                                    <a class="dropdown-item" href="{{ route('quotation.preview', ['id' => $header->id]) }}" target="_blank" title="Preview quotation document"><i class="mdi mdi-file-eye"></i> Preview Quote</a>
                                    <span class="dropdown-item" style="cursor: pointer;" data-target="#print-quotation" data-header="{{$header->id}}" data-toggle="modal"><i class="mdi mdi-printer"></i> Process PDF</span>
                                    @else
                                    <span class="dropdown-item text-muted" title="Add at least one line item first"><i class="mdi mdi-file-eye"></i> Preview Quote</span>
                                    <span class="dropdown-item text-muted" title="Add at least one line item first"><i class="mdi mdi-printer"></i> Process PDF</span>
                                    @endif
                                    @if($header->status == 'Quote In Preparation' && $header->quotation_type != 'General')
                                    <span class="dropdown-item" style="cursor: pointer;" data-target="#quote-commercial-modal" data-toggle="modal" title="Pricelist and billing mode">
                                        <i class="mdi mdi-cash-multiple"></i> Commercial
                                        @if(!empty($pricelistChooser['needs_choice']))
                                            <span class="badge badge-danger ml-1" style="font-size: 9px;">Pick</span>
                                        @endif
                                    </span>
                                    @endif
                                    @if(sizeof($details)>0)
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('create_quotation_revision', ['id' => $header->id]) }}" method="POST" class="px-0 m-0">
                                        @csrf
                                        <button type="submit" class="dropdown-item" style="cursor: pointer;"><i class="mdi mdi-source-branch"></i> Create Revision</button>
                                    </form>
                                    @endif
                                    @if($header->status == "Quote In Preparation")
                                    <span class="dropdown-item" style="cursor: pointer;" data-target="#save-draft" data-toggle="modal"><i class="mdi mdi-download-outline"></i> Save As Draft</span>
                                    <span class="dropdown-item text-danger" style="cursor: pointer;" data-target="#delete-quotation" data-toggle="modal"><i class="mdi mdi-delete-empty"></i> Delete Quotation</span>
                                    @if(sizeof($details)>0)
                                    <div class="dropdown-divider"></div>
                                    @if($skipsQuotationApproval ?? false)
                                    <span class="dropdown-item text-dark" style="cursor: pointer;"
                                          onclick="window.Livewire && window.Livewire.dispatch('billing-quotation-open-send-for-approval')">
                                        <i class="mdi mdi-check-circle-outline"></i> Mark as Complete
                                    </span>
                                    @else
                                    <span class="dropdown-item text-dark" style="cursor: pointer;"
                                          onclick="window.Livewire && window.Livewire.dispatch('billing-quotation-open-send-for-approval')">
                                        <i class="mdi mdi-share-circle"></i> Request For Approval
                                    </span>
                                    @endif
                                    @endif
                                    @endif
                                    @if($header->status == 'Quote In Approval' && sizeof($details)>0 && (int) $header->is_approved !== 1 && ($canApproveQuotation ?? false))
                                    <div class="dropdown-divider"></div>
                                    <span class="dropdown-item text-success" style="cursor: pointer;"
                                          onclick="window.Livewire && window.Livewire.dispatch('billing-quotation-open-approve')">
                                        <i class="mdi mdi-check-decagram"></i> Approve quotation
                                    </span>
                                    <span class="dropdown-item text-danger" style="cursor: pointer;"
                                          onclick="window.Livewire && window.Livewire.dispatch('billing-quotation-open-reject')">
                                        <i class="mdi mdi-close-octagon-outline"></i> Reject quotation
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($header->status == 'Quote In Approval' && (int) $header->is_approved !== 1)
        <div class="mb-3 d-flex flex-wrap align-items-center" style="gap: 8px;">
            @livewire('billing.quotation-approve-from-detail', ['quotationHeaderId' => (string) $header->id], key('billing-approve-show-'.$header->id))
        </div>
    @endif

    @if(($revisionFamily ?? collect())->count() > 1)
    <div class="ls-form-panel mb-3">
        <h4 class="ls-form-panel__title"><i class="mdi mdi-source-branch"></i> Revision history</h4>
        <ul class="mb-0 pl-3" style="font-size: 12px;">
            @foreach($revisionFamily as $revision)
                <li>
                    @if($revision->id === $header->id)
                        <strong>{{ $revision->quote_number }} Rev {{ $revision->revision_number ?? 1 }} (viewing)</strong>
                    @else
                        <a href="{{ route('add-qoute-details-view', ['id' => $revision->id]) }}">{{ $revision->quote_number }} Rev {{ $revision->revision_number ?? 1 }}</a>
                        @if($revision->isSuperseded())
                            <span class="text-muted">(superseded)</span>
                        @endif
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(($linkedEnquiryEngagements ?? collect())->isNotEmpty())
    <div class="ls-form-panel mb-3">
        <h4 class="ls-form-panel__title"><i class="mdi mdi-link-variant"></i> Linked enquiries</h4>
        <ul class="mb-0 pl-3" style="font-size: 12px;">
            @foreach($linkedEnquiryEngagements as $engagement)
                <li>
                    @if($engagement->enquiry)
                        @php
                            $linkedEnquiryLabel = trim((string) (
                                $engagement->enquiry->unique_identification
                                ?: $engagement->enquiry->reference_number
                                ?: $engagement->enquiry->formatted_number
                            ));
                        @endphp
                        <a href="{{ $engagement->enquiry->staffViewUrl() }}">{{ $linkedEnquiryLabel !== '' ? $linkedEnquiryLabel : 'Enquiry' }}</a>
                        <span class="text-muted">— {{ $engagement->enquiry->status }}</span>
                        @if($engagement->sent_to_customer_at)
                            <span class="text-muted">· sent {{ $engagement->sent_to_customer_at->format('Y-m-d') }}</span>
                        @endif
                        @if($engagement->accepted_at)
                            <span class="text-muted">· accepted {{ $engagement->accepted_at->format('Y-m-d') }}</span>
                        @endif
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="row ls-quotation-workspace">
        <div class="col-12 col-xl-4 col-lg-5 mb-3 mb-lg-0">
            @include('layouts.lab.invoice.partials.quotation-show-header')
        </div>
        <div class="col-12 col-xl-8 col-lg-7">
    <div class="ls-quotation-lines-panel mb-4">
        <h4 class="ls-quotation-lines-panel__title">
            <i class="mdi mdi-playlist-edit"></i>
            Line items &amp; terms
        </h4>
        <form action="{{ route('add_quotation_detail',['id'=>$header->id]) }}" method="POST" enctype="multipart/form-data" class="ls-quotation-lines-form">
            @csrf
            {{-- PHASE_A_LINES_ANCHOR_START --}}
                        @if($header->quotation_type == 'General')
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 quotation-lines-toolbar" style="gap: 8px;">
                            <button type="submit" class="btn btn-quotation-primary btn-sm">
                                <i class="mdi mdi-content-save-outline"></i> Save lines
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addrowgeneral()">
                                <i class="mdi mdi-plus"></i> Add line
                            </button>
                        </div>
                        <div class="table-responsive ls-quotation-table-scroll">

                            <table class="table table-hover ls-table ls-table--dense my-small-text livewire-table mb-0" style="min-width: 180%;">
                                <thead>
                                    <th></th>

                                    <th style="min-width: 10%;">Part No</th>
                                    <th nowrap style="min-width: 25%;">Item*</th>
                                    <th nowrap style="min-width: 30%;">Description*</th>
                                    <th nowrap style="min-width: 8%;">Photo</th>
                                    <th nowrap style="min-width: 8%;">Quantity*</th>
                                    <th nowrap style="min-width: 8%;">Unit Price</th>
                                    <th nowrap style="min-width: 11%;">Tax</th>

                                </thead>
                                <tbody id="quotation-detail-row">
                                    @foreach($details as $detail)
                                    <tr>
                                        <td style="display: flex;border:0px ">
                                            <span style="font-size:11px; flex:1" data-toggle="modal" data-target="#edit-detail-quotation-{{$detail->id}}" class="btn mdi mdi-pencil " data-toggle="tooltip" title="Edit"></span>
                                            <span style="font-size:12px;flex:1 ;border-bottom:0px" data-toggle="modal" data-target="#delete-detail-{{$detail->id}}" class="btn mdi mdi-delete-empty text-danger" data-toggle="tooltip" title="Delete"></span>


                                        </td>

                                        <td>{{$detail->part_no}}</td>
                                        <td>
                                            <div class="form-group">

                                                <textarea class=" form-control" value="" rows="1" readonly> {{$detail->item_name}} </textarea>
                                            </div>
                                        </td>
                                        <td nowrap>
                                            <div class="">
                                                <textarea style="min-height: 100px;" class=" form-control" value="" rows="1" readonly> {{$detail->description}} </textarea>

                                            </div>
                                        </td>
                                        <td>

                                            <div class="form-group">
                                                <img src="{{$detail->photo_url ?? '/images/no-logo.png' }}" alt="Item Photo" style="width: auto; height:100px">


                                            </div>

                                        </td>
                                        <td>
                                            <div class="form-group" id="">
                                                <input type="text" class="form-control" value="{{$detail->quantity}}" readonly id="">

                                            </div>
                                        </td>

                                        <td>

                                            <div class="form-group">
                                                <input type="float" class="form-control" value="{{number_format($detail->unit_price,2)}}" disabled>
                                            </div>

                                        </td>
                                        <td>

                                            <div class="form-group" id="">
                                                <input type="text" value="{{$detail->tax}}" readonly class="form-control" disabled>
                                            </div>



                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 mt-2 quotation-lines-toolbar" style="gap: 8px;">
                            <div class="d-flex flex-wrap align-items-center" style="gap: 16px;">
                                <label class="mb-0 small text-muted" title="Include LOQ column on the quotation PDF">
                                    <input type="hidden" name="show_loq_column" value="0">
                                    <input type="checkbox" name="show_loq_column" value="1" class="mr-1"
                                           {{ ($header->show_loq_column ?? true) ? 'checked' : '' }}>
                                    Show LOQ column
                                </label>
                                <label class="mb-0 small text-muted" title="Include MU% column on the quotation PDF">
                                    <input type="hidden" name="show_mu_column" value="0">
                                    <input type="checkbox" name="show_mu_column" value="1" class="mr-1"
                                           {{ ($header->show_mu_column ?? true) ? 'checked' : '' }}>
                                    Show MU column
                                </label>
                            </div>
                            <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                                @if($header->status == 'Quote In Preparation')
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="ls-quote-import-open" data-toggle="modal" data-target="#ls-quote-import-modal" title="Import lines from Excel or PDF">
                                    <i class="mdi mdi-file-upload-outline"></i> Import
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="ls-quote-pricelist-open" data-toggle="modal" data-target="#quote-commercial-modal" title="Append lines from customer pricelist">
                                    <i class="mdi mdi-format-list-bulleted-type"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm d-none" id="ls-quote-bulk-delete" title="Delete selected lines">
                                    <i class="mdi mdi-delete-outline"></i> Delete selected
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="add-row">
                                    <i class="mdi mdi-plus"></i> Add line
                                </button>
                                @endif
                                <button type="submit" class="btn btn-quotation-primary btn-sm">
                                    <i class="mdi mdi-content-save-outline"></i> Save lines
                                </button>
                            </div>
                        </div>
                        @include('layouts.lab.invoice.partials.quotation-import-modal')
                        @include('layouts.lab.invoice.partials.quotation-analysis-line-prototype')
                        <div class="table-responsive quotation-analysis-lines ls-quotation-table-scroll" id="ls-quote-analysis-lines-workspace"
                             data-pricelist-id="{{ $header->pricelist_id ? (string) $header->pricelist_id : '' }}"
                             data-bulk-delete-url="{{ route('quotation.bulk_delete_details', ['id' => $header->id]) }}">

                            <table class="table table-hover ls-table ls-table--dense mb-0 livewire-table ls-quote-analysis-table">
                                <colgroup>
                                    <col class="ls-quote-col--select" style="width:2.5%">
                                    <col class="ls-quote-col--no" style="width:4%">
                                    <col class="ls-quote-col--sample" style="width:15%">
                                    <col class="ls-quote-col--params" style="width:31%">
                                    <col class="ls-quote-col--qty-req" style="width:14%">
                                    <col class="ls-quote-col--qty" style="width:6%">
                                    <col class="ls-quote-col--price" style="width:11%">
                                    <col class="ls-quote-col--total" style="width:9%">
                                    <col class="ls-quote-col--tax" style="width:7.5%">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:2rem;">
                                            @if($header->status == 'Quote In Preparation')
                                            <input type="checkbox" id="ls-quote-select-all-lines" title="Select all lines" aria-label="Select all lines">
                                            @endif
                                        </th>
                                        <th>No</th>
                                        <th nowrap>Sample Type <span class="text-danger">*</span></th>
                                        <th nowrap>Parameters<span class="text-danger">*</span></th>
                                        <th nowrap>Quantity Required</th>
                                        <th nowrap>Qty<span class="text-danger">*</span></th>
                                        <th nowrap>Unit Price</th>
                                        <th nowrap>Total Price</th>
                                        <th nowrap>Tax%</th>
                                    </tr>
                                </thead>
                                <tbody id="create-detail">
                                    @foreach($details as $detail)
                                    <tr data-saved-detail-id="{{ $detail->id }}">
                                        @if($header->status == 'Quote In Preparation')
                                        <td class="text-center align-middle">
                                            <input type="checkbox" class="ls-quote-line-select" value="{{ $detail->id }}" aria-label="Select line">
                                        </td>
                                        @endif
                                        <td class="align-middle ls-quote-line-actions">
                                            <button type="button" class="ls-quote-icon-btn" data-sample="{{$detail->sample_type_name}}" data-detail="{{$detail->id}}" data-toggle="modal" data-target="#detail-edit-mode" title="Edit">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button type="button" class="ls-quote-icon-btn ls-quote-icon-btn--danger" data-toggle="modal" data-target="#delete-detail-{{$detail->id}}" title="Delete">
                                                <i class="mdi mdi-delete-empty"></i>
                                            </button>
                                        </td>
                                        <td class="align-middle">
                                            <div class="ls-field ls-compact is-disabled mb-0">
                                                <div class="ls-field__control">
                                                    <input type="text" class="ls-field__input" value="{{$detail->sample_type_name}}" disabled>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle ls-quote-col-params">
                                            @php
                                                $paramCount = count($detail->default ?? [])
                                                    + count($detail->sub_acc ?? [])
                                                    + count($detail->sub_analytes ?? [])
                                                    + count($detail->acc_analytes ?? []);
                                                $paramsCollapsed = $paramCount > 7;
                                                $lineTotal = round((float) $detail->unit_price * (int) $detail->quantity, 2);
                                            @endphp
                                            {{-- Amspec: tests listed for scope; commercial qty×price is on the line, not per chip. --}}
                                            <div class="ls-quote-params {{ $paramsCollapsed ? 'is-collapsed' : '' }}" data-param-count="{{ $paramCount }}">
                                                <div class="ls-quote-params__toolbar">
                                                    <button type="button" class="ls-quote-params__title js-quote-params-toggle" @if($paramsCollapsed) title="Show parameters" @endif>
                                                        {{ $detail->sample_type_name }}
                                                        @if($paramsCollapsed)
                                                            <span class="ls-quote-params__count">{{ $paramCount }}</span>
                                                        @endif
                                                    </button>
                                                </div>
                                                <div class="ls-select2-view ls-quote-params__chips">
                                                    @foreach($detail->default as $da)
                                                        <span class="ls-select2-view__chip ls-quote-param-chip">{{ $da }}</span>
                                                    @endforeach
                                                    @foreach($detail->sub_acc as $sb)
                                                        <span class="ls-select2-view__chip ls-quote-param-chip ls-quote-param-chip--acc ls-quote-param-chip--sub">{{ $sb }} <i class="mdi mdi-asterisk" title="Subcontracted"></i><i class="mdi mdi-check-decagram" title="Accredited"></i></span>
                                                    @endforeach
                                                    @foreach($detail->sub_analytes as $sa)
                                                        <span class="ls-select2-view__chip ls-quote-param-chip ls-quote-param-chip--sub">{{ $sa }} <i class="mdi mdi-asterisk" title="Subcontracted"></i></span>
                                                    @endforeach
                                                    @foreach($detail->acc_analytes as $acc)
                                                        <span class="ls-select2-view__chip ls-quote-param-chip ls-quote-param-chip--acc">{{ $acc }} <i class="mdi mdi-check-decagram" title="Accredited"></i></span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="ls-field ls-compact is-disabled mb-0">
                                                <div class="ls-field__control">
                                                    <input type="text" class="ls-field__input" value="{{ $detail->quantity_required }}" disabled>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="ls-field ls-compact is-disabled mb-0">
                                                <div class="ls-field__control">
                                                    <input type="number" class="ls-field__input" value="{{$detail->quantity}}" disabled>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="ls-field ls-compact is-disabled mb-0">
                                                <div class="ls-field__control">
                                                    <input type="text" class="ls-field__input text-right" value="{{number_format($detail->unit_price,2)}}" disabled>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="ls-field ls-compact is-disabled mb-0">
                                                <div class="ls-field__control">
                                                    <input type="text" class="ls-field__input text-right" value="{{ number_format($lineTotal, 2) }}" disabled>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="ls-field ls-compact is-disabled mb-0">
                                                <div class="ls-field__control">
                                                    <input type="text" class="ls-field__input" value="{{$detail->tax}}" disabled>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif

                        <div class="ls-form-panel mt-3 mb-3">
                        <h4 class="ls-form-panel__title"><i class="mdi mdi-file-document-outline"></i> Terms of Sale</h4>

                        <div class="terms">
                            <div class="ls-form-grid ls-form-grid--2">
                            <div class="ls-field">
                                <label class="ls-field__label">Quote Currency <span class="ls-req">*</span></label>
                                <div class="ls-field__control">
                                <select name="currency_id" class="ls-field__input form-control" required>
                                    <option value="">Choose Currency...</option>
                                    @foreach($currencies as $currency)
                                    <option value="{{ $currency->id }}" {{ $header->currency_id == $currency->id ? 'selected' : '' }}>{{ $currency->code }} - {{ $currency->description }}</option>
                                    @endforeach
                                </select>
                                </div>
                            </div>

                            <div class="ls-field">
                                <label class="ls-field__label">Delivery of results</label>
                                <div class="ls-field__control">
                                <textarea class="ls-field__input form-control" rows="2" name="service_delivery" placeholder="Delivery of results...">{{ $termsOfSale['service_delivery'] ?? '' }}</textarea>
                                </div>
                            </div>
                            </div>
                            @php
                                $paymentOptions = collect($accountPaymentOptions ?? []);
                                $currentPayments = trim((string) ($termsOfSale['payments'] ?? ''));
                                $matchedPaymentOption = $paymentOptions->firstWhere('label', $currentPayments);
                                $otherOption = $paymentOptions->firstWhere('allows_custom', true);
                                $isCustomPayment = $currentPayments !== '' && $matchedPaymentOption === null;
                                $selectedPaymentId = $matchedPaymentOption['id']
                                    ?? ($isCustomPayment && $otherOption ? $otherOption['id'] : '');
                                $showPaymentsCustom = ($matchedPaymentOption['allows_custom'] ?? false)
                                    || $isCustomPayment
                                    || ($selectedPaymentId !== '' && $otherOption && $selectedPaymentId === ($otherOption['id'] ?? null));
                                $paymentsCustomValue = $isCustomPayment
                                    ? $currentPayments
                                    : '';
                                if ($paymentsCustomValue === '' && $showPaymentsCustom) {
                                    $customerNote = trim((string) ($header->customer->payment_terms_note ?? ''));
                                    if ($customerNote !== '') {
                                        $paymentsCustomValue = $customerNote;
                                    }
                                }
                            @endphp
                            <div class="ls-form-grid ls-form-grid--2 mt-2">
                            <div class="ls-field">
                                <label class="ls-field__label">Payments</label>
                                <div class="ls-field__control">
                                <select name="payments_account_id" id="quote-payments" class="ls-field__input form-control">
                                    <option value="">Choose payment terms...</option>
                                    @foreach($paymentOptions as $option)
                                        <option
                                            value="{{ $option['id'] }}"
                                            data-allows-custom="{{ ! empty($option['allows_custom']) ? '1' : '0' }}"
                                            data-label="{{ $option['label'] }}"
                                            {{ (string) $selectedPaymentId === (string) $option['id'] ? 'selected' : '' }}
                                        >{{ $option['label'] }}</option>
                                    @endforeach
                                </select>
                                </div>
                            </div>
                            <div class="ls-field" id="quote-payments-custom-wrap" style="{{ $showPaymentsCustom ? '' : 'display:none;' }}">
                                <label class="ls-field__label">Custom payment terms</label>
                                <div class="ls-field__control">
                                <textarea class="ls-field__input form-control" rows="3" name="payments_custom" id="quote-payments-custom" placeholder="Enter custom payment terms for this quotation...">{{ $paymentsCustomValue }}</textarea>
                                </div>
                                <p class="ls-field__hint">Shown when “Other” is selected. Saved on this quotation only.</p>
                            </div>
                            </div>
                            <div class="ls-field mt-2">
                                <label class="ls-field__label">Proposal Acceptance</label>
                                <div class="ls-field__control">
                                <textarea class="ls-field__input form-control" rows="2" name="quote_specification" placeholder="Proposal Acceptance...">{{ $termsOfSale['quote_specification'] ?? '' }}</textarea>
                                </div>
                            </div>

                        </div>
                        </div>

                        <div class="ls-form-panel mb-0">
                        <h4 class="ls-form-panel__title"><i class="mdi mdi-information-outline"></i> Additional Information</h4>

                        <div class="additional-info">
                            <div class="ls-field">
                                <label class="ls-field__label">Technical questions / Inquiries / Complaints</label>
                                <div class="ls-field__control">
                                <textarea class="ls-field__input form-control" rows="2" name="additional_info" placeholder="Technical questions / Inquiries / Complaints...">{{ $termsOfSale['additional_info'] ?? '' }}</textarea>
                                </div>
                            </div>
                            <div class="ls-field mt-2">
                                <label class="ls-field__label">Information for Purchase Order / Sample Shipment</label>
                                <div class="ls-field__control">
                                <textarea class="ls-field__input form-control" rows="5" name="payment_info" placeholder="Information for Purchase Order / Sample Shipment...">{{ $termsOfSale['payment_info'] ?? '' }}</textarea>
                                </div>
                            </div>
                            <div class="ls-field mt-2">
                                <label class="ls-field__label">Quotation T&amp;C Override <span class="text-muted" style="font-weight:500;">(optional — one term per line)</span></label>
                                <div class="ls-field__control">
                                <textarea class="ls-field__input form-control" rows="4" name="terms_override" placeholder="Leave blank to use system Quotation Terms and Conditions">{{ $header->terms_override }}</textarea>
                                </div>
                            </div>
                        </div>
                        </div>

                    </form>
    </div>
        </div>
    </div>

</main>


<link rel="stylesheet" href="/css/quilljs.css" />
<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
@endsection

@livewire('billing.quotation-send-for-approval', ['quotationHeaderId' => (string) $header->id], key('billing-send-for-approval-'.$header->id))
@section('script2')
@include('layouts.lab.invoice.partials.quotation-show-header-scripts')
@if($header->status == 'Quote In Preparation' && $header->quotation_type != 'General')
<div class="modal fade ls-quote-commercial-modal" id="quote-commercial-modal" role="dialog" aria-labelledby="quote-commercial-modal-title">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quote-commercial-modal-title">
                    <i class="mdi mdi-format-list-bulleted-type"></i> Customer pricelist
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @include('layouts.lab.invoice.partials.quotation-commercial-chooser', [
                    'header' => $header,
                    'details' => $details,
                    'pricelistChooser' => $pricelistChooser ?? null,
                ])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm ls-quote-commercial-done" data-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>
@endif
<div class="modal fade ls-quotation-workflow-modal" id="print-quotation" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="mdi mdi-printer"></i> Quote {{$header->quote_number}} PDF Processing</h5>
            </div>
            <div class="modal-body text-center">
                <div class="loading">
                    
                </div>
            </div>
            <div class="modal-footer">
                <a href="/billing-add-quote-detail-index/{{$header->id}}" class="btn btn-sm btn-outline-secondary">Close</a>
                
            </div>
        </div>
    </div>
</div>
<div class="modal fade ls-quotation-workflow-modal" id="approve-quote" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('approve-workflow')}}" method="post">
            @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="mdi mdi-check-decagram"></i> Approve Quotation</h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="header_id" value="{{$header->id}}">
                    <input type="hidden" name="stage" value="Quote Complete">
                    @if($header->approved_by == Auth::user()->id)
                    <div class="alert alert-success mb-3">
                       <i class="mdi mdi-alert-decagram"></i> Notify {{getUserById($header->prepared_by_id)->name}} that you have approved Quote {{$header->quote_number}}?
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="notification" id="approve-email" />
                        <label class="form-check-label" for="approve-email">
                            Send Email Notification
                        </label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="send_message" id="approve-msg" />
                        <label class="form-check-label" for="approve-msg">
                            Send Message
                        </label>
                    </div>
                    @else
                    <div class="alert alert-danger mb-0">
                        <i class="mdi mdi-alert-decagram"></i> You are not allowed to approve this quotation.
                    </div>
                    @endif
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Close</button>
                    @if($header->approved_by == Auth::user()->id)
                    <button type="submit" class="btn btn-sm btn-quotation-primary"><i class="mdi mdi-check"></i> Approve</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade ls-quotation-workflow-modal" id="delete-quotation" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{route('delete_quotation',['id'=>$header->id])}}" method="post">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="mdi mdi-delete-empty"></i> Delete Quotation {{$header->quote_number}}
                    </h4>
                </div>
                <div class="modal-body text-center">
                    <input type="hidden" name="header_id" value="{{$header->id}}" class="form-control">
                    Are you sure you want to delete Quotation {{$header->quote_number}}?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-quotation-primary"><i class="mdi mdi-delete-empty"></i> Yes, delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade ls-quotation-workflow-modal" id="save-draft" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{route('save_draft',['id'=>$header->id])}}" method="post">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title"><i class="mdi mdi-download-outline"></i> Save As Draft — {{$header->quote_number}}</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-0">
                        <i class="mdi mdi-alert-outline"></i>
                        Ensure you have saved all the details first before saving as draft.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-quotation-primary"><i class="mdi mdi-content-save"></i> Save draft</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade ls-quotation-workflow-modal" id="detail-edit-mode" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="post" action="{{route('edit_quotation_detail')}}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success text-center mb-0">
                        Kindly wait for the page to load…
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-quotation-primary btn-sm"><i class="mdi mdi-content-save-outline"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade ls-quotation-workflow-modal ls-quote-params-modal" id="edit-detail-analytes" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="mdi mdi-pencil"></i> Edit Quotation Parameters
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body ls-quote-params-modal-body">
                @include('layouts.lab.invoice.partials.quotation-params-modal-panel', ['prefix' => 'edit'])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-quotation-primary btn-sm" id="save-edit" disabled><i class="mdi mdi-content-save-outline"></i> Save</button>
            </div>
        </div>
    </div>
</div>
@foreach($details as $detail)
<div class="modal fade" id="delete-detail-{{$detail->id}}" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('delete_quotation_detail',['id'=>$detail->id])}}" method="get">
                <div class="modal-header">
                    <h4 class="modal-title">

                        <i class="mdi mdi-delete-empty text-danger"></i> Delete Item {{$loop->iteration}}
                    </h4>
                </div>
                <div class="modal-body text-center">
                    @if($header->quotation_type == 'General')
                    <div class="alert alert-danger">
                        Are you sure you want to delete item {{$detail->item_name}}
                    </div>
                    @else


                    Are you sure you want to delete {{$detail->sample_type_name}} ?
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-success"><i class="mdi mdi-content-save"></i> Yes</button>
                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="edit-detail-quotation-{{$detail->id}}" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{route('edit_quotation_detail')}}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-pencil"></i> Edit Quote Detail
                    </h5>
                </div>
                <div class="modal-body">

                    <div class="form-group">
                        <input type="hidden" name="detail_id" value="{{$detail->id}}">
                        <label class="control-label">Part no</label>
                        <input type="text" id="part_number" name="part_no" value="{{$detail->part_no}}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Item Name</label>
                        <textarea class="form-control" name="item" required rows="1">{{$detail->item_name}}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Description</label>
                        <textarea class="form-control" id="quotation-description" name="description" required rows="2">{{$detail->description}}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Quantity</label>
                        <input type="number" name="quantity" id="" class="form-control" value="{{$detail->quantity}}" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Unit Price</label>
                        <input type="float" name="unit_price" class="form-control" value="{{$detail->unit_price}}" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Tax</label>
                        <input type="text" name="tax" value="{{$detail->tax}}" class="form-control" required>
                    </div>
                    @if($header->quotation_type == 'General')
                    <div class="form-group">
                        <label class="control-label">Photo</label>
                        <div class="row">
                            <div class="col-md-2 col-lg-2 col-sm-2">
                                <img src="{{$detail->photo_url}}" style="height:100px;width:auto" alt="">
                            </div>
                            <div class="col-md-10 col-lg-10 col-sm-10">
                                <input type="file" name="photo" id="" class="form-control">
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-success"><i class="mdi mdi-content-save"></i> Yes</button>
                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endforeach
<div class="modal fade ls-quotation-workflow-modal ls-quote-params-modal" id="quote-description-analytes" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="mdi mdi-flask-outline"></i> Quotation Parameters
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body ls-quote-params-modal-body">
                @include('layouts.lab.invoice.partials.quotation-params-modal-panel', ['prefix' => 'quote'])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                <button type="button" id="save-analytes" class="btn btn-quotation-primary btn-sm" disabled><i class="mdi mdi-content-save-outline"></i> Save</button>
            </div>
        </div>
    </div>
</div>
<script>
    var analysis = [];
    $(function() {
        // Keep prototype fields out of HTML5 validation/submit (disabled controls are skipped).
        var proto = document.getElementById('ls-quote-analysis-line-prototype');
        if (proto) {
            Array.prototype.forEach.call(proto.querySelectorAll('input, select, textarea, button'), function (el) {
                el.disabled = true;
                el.required = false;
            });
        }

        var quotationPricingConfig = {
            suggestUrl: @json(route('quotation.suggest_line_pricing', $header->id)),
            packageDefaultsUrl: @json(route('quotation.package_defaults', $header->id)),
            updateLoqUrl: @json(route('quotation.update_element_loq')),
            quotationHeaderId: @json((string) $header->id),
            csrf: @json(csrf_token()),
            labSectionNames: @json($header->labSections->pluck('name')->values()->all()),
            activeTaxPercent: @json(app(\App\Services\Billing\QuotationLineTaxResolver::class)->activeTaxRegimePercent()),
        };

        var quotationImportConfig = {
            importUrl: @json(route('quotation.import_prep_lines', $header->id)),
            capabilityUrl: @json(route('quotation.import_prep_capability')),
            csrf: quotationPricingConfig.csrf,
            format: 'excel',
            pricingMode: 'per_package',
        };

        var $quoteWorkspace = $('#ls-quote-analysis-lines-workspace');

        function boundPricelistId() {
            var fromChooser = String($('#ls-quote-commercial-chooser').data('selected-pricelist') || '');
            var fromWorkspace = String($quoteWorkspace.data('pricelist-id') || '');
            return fromChooser || fromWorkspace;
        }

        function independentDefaultTax() {
            var tax = parseFloat(quotationPricingConfig.activeTaxPercent);
            return isNaN(tax) ? 0 : tax;
        }

        function formatTaxDisplay(tax) {
            var value = parseFloat(tax);
            if (isNaN(value) || value <= 0) {
                return '0%';
            }
            return value.toFixed(2) + '%';
        }

        function applyDefaultTaxToRow($row) {
            var tax = boundPricelistId() !== '' ? 0 : independentDefaultTax();
            $row.find('.quotation-tax').val(tax);
            $row.find('.quotation-tax-display').text(formatTaxDisplay(tax));
        }

        function currentQuotePricingMode() {
            if (boundPricelistId() !== '') {
                var listMode = String($('#ls-quote-commercial-chooser').data('pricelist-billing-mode') || 'per_package');
                return listMode === 'per_test' ? 'per_test' : 'per_package';
            }
            return 'per_package';
        }

        function applyManualLineModeUi($row) {
            var locked = boundPricelistId() !== '';
            var mode = currentQuotePricingMode();
            var $wrap = $row.find('.ls-quote-line-mode-wrap');
            var $toggle = $row.find('.ls-quote-line-mode-input');
            if ($wrap.length) {
                if (locked) {
                    $wrap.addClass('d-none');
                    $toggle.prop('checked', mode === 'per_test').prop('disabled', true);
                } else {
                    $wrap.removeClass('d-none');
                    $toggle.prop('disabled', false);
                    if (!$toggle.data('userTouched')) {
                        $toggle.prop('checked', mode === 'per_test');
                    }
                }
            }
            var rowMode = ($toggle.length && $toggle.is(':checked')) ? 'per_test' : 'per_package';
            if (locked) {
                rowMode = mode;
            }
            $row.find('.quotation-pricing-mode').val(rowMode);
            return rowMode;
        }

        function syncRowPricingModes(mode) {
            mode = mode === 'per_test' ? 'per_test' : 'per_package';
            quotationImportConfig.pricingMode = mode;
            $('#create-detail tr').each(function () {
                applyManualLineModeUi($(this));
            });
            applyManualLineModeUi($('#ls-quote-analysis-line-prototype'));
            var hint = mode === 'per_test'
                ? 'Per parameter: each test is billed separately.'
                : 'Per package: samples × one package price (tests listed for scope).';
            $('#create-detail .quotation-price-hint').each(function () {
                var $hint = $(this);
                if (!$hint.closest('tr').data('pricelistSuggestion')) {
                    $hint.text(hint);
                }
            });
        }

        (function initQuoteCommercialChooser() {
            var $root = $('#ls-quote-commercial-chooser');
            if (!$root.length) {
                return;
            }

            syncRowPricingModes(currentQuotePricingMode());

            function initCommercialTooltips() {
                var $tips = $('#quote-commercial-modal [data-toggle="tooltip"]');
                if (!$tips.length || typeof $tips.tooltip !== 'function') {
                    return;
                }
                $tips.each(function () {
                    var $el = $(this);
                    if ($el.data('bs.tooltip')) {
                        $el.tooltip('dispose');
                    }
                    $el.tooltip({
                        container: 'body',
                        trigger: 'hover focus',
                        boundary: 'window',
                        template: '<div class="tooltip ls-quote-commercial-tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
                    });
                });
            }

            initCommercialTooltips();
            $('#quote-commercial-modal').on('shown.bs.modal', initCommercialTooltips);
            $('#quote-commercial-modal').on('hide.bs.modal', function () {
                $('#quote-commercial-modal [data-toggle="tooltip"]').each(function () {
                    var $el = $(this);
                    if ($el.data('bs.tooltip')) {
                        $el.tooltip('hide');
                    }
                });
                $('.tooltip.ls-quote-commercial-tooltip').remove();
            });

            $root.on('click', '.ls-quote-pricelist-tile', function () {
                var $btn = $(this);
                var pricelistId = String($btn.data('pricelist-id') || '');
                var url = $root.data('select-pricelist-url');
                var $status = $('#ls-quote-pricelist-status');
                if (!pricelistId || !url) {
                    return;
                }

                $root.find('.ls-quote-pricelist-tile')
                    .removeClass('is-active is-saving ls-motion-shake-soft')
                    .attr('aria-checked', 'false');
                $btn.addClass('is-active is-saving').attr('aria-checked', 'true');
                $btn.find('.ls-icon-tile__glyph i')
                    .removeClass('mdi-format-list-bulleted-type mdi-check-decagram ls-motion-check')
                    .addClass('mdi-loading ls-motion-spin');
                $status.removeClass('is-success is-error').text('Applying pricelist…');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: quotationPricingConfig.csrf,
                        pricelist_id: pricelistId,
                    },
                }).done(function (res) {
                    var billingMode = String(res.billing_mode || $btn.data('billing-mode') || 'package');
                    var pricingMode = billingMode === 'per_test' ? 'per_test' : 'per_package';
                    $root.attr('data-selected-pricelist', pricelistId).data('selected-pricelist', pricelistId);
                    $root.attr('data-pricelist-billing-mode', pricingMode).data('pricelist-billing-mode', pricingMode);
                    $quoteWorkspace.attr('data-pricelist-id', pricelistId).data('pricelist-id', pricelistId);
                    $root.attr('data-needs-choice', '0').data('needs-choice', 0);
                    $root.find('.ls-quote-commercial-chooser__nudge').remove();
                    $root.find('.ls-quote-pricelist-tile').removeClass('ls-motion-shake-soft');
                    $btn.find('.ls-icon-tile__glyph i')
                        .removeClass('mdi-loading ls-motion-spin')
                        .addClass('mdi-check-decagram ls-motion-check');
                    var code = res.code || $btn.data('pricelist-code') || pricelistId;
                    var modeLabel = pricingMode === 'per_test' ? 'Per test' : 'Per package';
                    $('#ls-quote-commercial-summary-pricelist').text(code);
                    $('#ls-quote-commercial-summary-mode').text(modeLabel);
                    var created = parseInt(res.lines_created, 10) || 0;
                    var statusMsg = 'Using ' + code + ' (' + modeLabel + ')';
                    if (created > 0) {
                        statusMsg += ' — appended ' + created + ' line(s).';
                    }
                    $status.addClass('is-success').text(statusMsg + (res.description ? ' ' + res.description : ''));
                    syncRowPricingModes(pricingMode);
                    if (res.reload) {
                        window.location.reload();
                    }
                }).fail(function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.error)
                        ? xhr.responseJSON.error
                        : 'Could not apply pricelist.';
                    $btn.find('.ls-icon-tile__glyph i')
                        .removeClass('mdi-loading ls-motion-spin mdi-check-decagram')
                        .addClass('mdi-format-list-bulleted-type');
                    $btn.removeClass('is-active');
                    $status.addClass('is-error').text(msg);
                }).always(function () {
                    $btn.removeClass('is-saving');
                });
            });

            $root.on('click', '#ls-quote-detach-pricelist', function () {
                var url = $root.data('detach-pricelist-url');
                if (!url || !confirm('Detach this quotation from the pricelist? New lines will use manual pricing.')) {
                    return;
                }
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: { _token: quotationPricingConfig.csrf },
                }).done(function () {
                    $root.attr('data-selected-pricelist', '').data('selected-pricelist', '');
                    $quoteWorkspace.attr('data-pricelist-id', '').data('pricelist-id', '');
                    $('#ls-quote-commercial-summary-pricelist').text('—');
                    $('#ls-quote-commercial-summary-mode').text('—');
                    $('#ls-quote-pricelist-status').text('Pricelist detached. Manual pricing enabled.');
                    syncRowPricingModes('per_package');
                    window.location.reload();
                });
            });
        })();

        (function initQuoteLineBulkSelect() {
            var $bulkBtn = $('#ls-quote-bulk-delete');
            var $selectAll = $('#ls-quote-select-all-lines');
            if (!$bulkBtn.length) {
                return;
            }

            function selectedLineCheckboxes() {
                return $('#create-detail .ls-quote-line-select:checked');
            }

            function refreshBulkUi() {
                var count = selectedLineCheckboxes().length;
                $bulkBtn.toggleClass('d-none', count === 0);
            }

            $(document).on('change', '.ls-quote-line-select', refreshBulkUi);
            $selectAll.on('change', function () {
                var checked = $(this).is(':checked');
                $('#create-detail .ls-quote-line-select').prop('checked', checked);
                refreshBulkUi();
            });

            $bulkBtn.on('click', function () {
                var $checked = selectedLineCheckboxes();
                if (!$checked.length) {
                    return;
                }
                if (!confirm('Delete ' + $checked.length + ' selected line(s)?')) {
                    return;
                }
                var savedIds = [];
                $checked.each(function () {
                    var val = String($(this).val() || '');
                    var $row = $(this).closest('tr');
                    if (val !== '') {
                        savedIds.push(val);
                    } else {
                        $row.remove();
                    }
                });
                if (savedIds.length === 0) {
                    refreshBulkUi();
                    $selectAll.prop('checked', false);
                    return;
                }
                $.ajax({
                    url: String($quoteWorkspace.data('bulk-delete-url') || ''),
                    method: 'POST',
                    data: {
                        _token: quotationPricingConfig.csrf,
                        detail_ids: savedIds,
                    },
                }).done(function () {
                    window.location.reload();
                }).fail(function () {
                    if (typeof window.showImaraToast === 'function') {
                        window.showImaraToast({
                            type: 'error',
                            title: 'Could not delete',
                            message: 'Could not delete selected lines.',
                            variant: 'shake',
                        });
                    }
                });
            });
        })();

        $(document).on('change', '.ls-quote-line-mode-input', function () {
            var $input = $(this);
            $input.data('userTouched', true);
            var $row = $input.closest('tr');
            var mode = applyManualLineModeUi($row);
            if (mode !== 'per_test') {
                collapsePerTestGroupToPackage($row);
                return;
            }
            var ids = collectElementIdsFromRow($row);
            if (ids.length < 2) {
                return;
            }
            // Already has multiple chips on a package-style row — explode now.
            var state = {
                tests: [],
                analysisTypeIds: String($row.find('input.select-part-final, input[name="part_number_final[]"]').val() || '')
                    .split(',')
                    .map(function (s) { return s.trim(); })
                    .filter(Boolean),
                analytes_accreditted: splitCsvIds($row.find('input[name="accreditted_analytes[]"]').val()),
                analyte_sub: splitCsvIds($row.find('input[name="sub_analytes[]"]').val()),
                sub_acc: splitCsvIds($row.find('input[name="sub_acc[]"]').val()),
                default_analytes: splitCsvIds($row.find('input[name="default_analytes[]"]').val()),
                loqMap: {},
                spans: [],
            };
            var loqRaw = $row.find('input[name="element_loq_json[]"]').val();
            if (loqRaw) {
                try {
                    state.loqMap = JSON.parse(loqRaw) || {};
                } catch (err) {
                    state.loqMap = {};
                }
            }
            var nameById = {};
            $row.find('.ls-quote-param-chip').each(function (idx) {
                if (ids[idx]) {
                    nameById[ids[idx]] = $(this).clone().children().remove().end().text().trim();
                }
            });
            ids.forEach(function (id) {
                var isAcc = state.analytes_accreditted.indexOf(id) !== -1 || state.sub_acc.indexOf(id) !== -1;
                var isSub = state.analyte_sub.indexOf(id) !== -1 || state.sub_acc.indexOf(id) !== -1;
                state.tests.push({
                    id: id,
                    name: nameById[id] || id,
                    analysisTypeId: state.analysisTypeIds[0] || '',
                    isAcc: isAcc,
                    isSub: isSub,
                    loq: state.loqMap[id] || '',
                });
            });
            if (state.tests.length > 1) {
                explodeRowIntoPerTestLines($row, state);
            }
        });

        (function initQuoteImportUi() {
            var $modal = $('#ls-quote-import-modal');
            if (!$modal.length) {
                return;
            }

            var $fmtBtns = $modal.find('.ls-amspec-fmt');
            var $modeBtns = $modal.find('.ls-amspec-mode');
            var $file = $modal.find('#ls-quote-import-file');
            var $err = $modal.find('#ls-amspec-import-error');
            var $pdfBtn = $modal.find('#ls-amspec-fmt-pdf');
            var $excelExplain = $modal.find('.ls-amspec-fmt-explain--excel');
            var $pdfExplain = $modal.find('.ls-amspec-fmt-explain--pdf');
            var $packageExplain = $modal.find('.ls-amspec-mode-explain--package');
            var $testExplain = $modal.find('.ls-amspec-mode-explain--test');
            var $dropHint = $modal.find('.ls-upload__drop-hint');

            function setFormat(fmt) {
                quotationImportConfig.format = fmt;
                $fmtBtns.removeClass('is-active');
                $fmtBtns.filter('[data-format="' + fmt + '"]').addClass('is-active');
                if (fmt === 'pdf') {
                    $file.attr('accept', '.pdf,application/pdf');
                    $excelExplain.prop('hidden', true);
                    $pdfExplain.prop('hidden', false);
                    $dropHint.text('Amspec Quotation – preparation PDF · up to 20 MB');
                } else {
                    $file.attr('accept', '.xlsx,.xls,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    $excelExplain.prop('hidden', false);
                    $pdfExplain.prop('hidden', true);
                    $dropHint.text('Excel (.xlsx / .xls / .csv) · up to 20 MB');
                }
                $err.prop('hidden', true).text('');
            }

            function setPricingMode(mode) {
                quotationImportConfig.pricingMode = mode;
                $modeBtns.removeClass('is-active');
                $modeBtns.filter('[data-mode="' + mode + '"]').addClass('is-active');
                if (mode === 'per_test') {
                    $packageExplain.prop('hidden', true);
                    $testExplain.prop('hidden', false);
                } else {
                    $packageExplain.prop('hidden', false);
                    $testExplain.prop('hidden', true);
                }
            }

            $fmtBtns.on('click', function () {
                if ($(this).prop('disabled')) {
                    return;
                }
                setFormat($(this).data('format'));
            });

            $modeBtns.on('click', function () {
                setPricingMode($(this).data('mode'));
            });

            $.getJSON(quotationImportConfig.capabilityUrl).done(function (data) {
                var pdf = (data && data.pdf) ? data.pdf : {};
                if (!pdf.available) {
                    $pdfBtn.prop('disabled', true).attr('title', pdf.hint || 'Install poppler-utils');
                }
            });

            $('#ls-amspec-import-submit').on('click', function () {
                var fileInput = $file.get(0);
                $err.prop('hidden', true).text('');
                if (!fileInput || !fileInput.files || !fileInput.files[0]) {
                    $err.text('Choose a file to import.').prop('hidden', false);
                    return;
                }

                var fd = new FormData();
                fd.append('file', fileInput.files[0]);
                fd.append('format', quotationImportConfig.format);
                fd.append('pricing_mode', quotationImportConfig.pricingMode);
                fd.append('_token', quotationImportConfig.csrf);

                var $btn = $(this);
                $btn.prop('disabled', true);
                $.ajax({
                    url: quotationImportConfig.importUrl,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                }).done(function (res) {
                    var created = (res && res.created) ? res.created : 0;
                    var warnings = (res && res.warnings) ? res.warnings : [];
                    var msg = 'Imported ' + created + ' quotation line(s).';
                    if (warnings.length) {
                        msg += ' Warnings: ' + warnings.slice(0, 3).join(' ');
                    }
                    $modal.modal('hide');
                    if (typeof window.showImaraToast === 'function') {
                        window.showImaraToast({
                            type: created > 0 ? 'success' : 'error',
                            title: created > 0 ? 'Success' : 'Import',
                            message: msg,
                        });
                    }
                    setTimeout(function () { window.location.reload(); }, 650);
                }).fail(function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.error)
                        ? xhr.responseJSON.error
                        : 'Import failed.';
                    $err.text(msg).prop('hidden', false);
                    if (typeof window.showImaraToast === 'function') {
                        window.showImaraToast({ type: 'error', title: 'Error', message: msg });
                    }
                }).always(function () {
                    $btn.prop('disabled', false);
                });
            });

            $modal.on('hidden.bs.modal', function () {
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
                document.querySelectorAll('.modal-backdrop').forEach(function (el) { el.remove(); });
            });

            setFormat('excel');
        })();

        function escapeQuoteHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function buildParamChipHtml(label, accredited, subcontracted) {
            var classes = 'ls-select2-view__chip ls-quote-param-chip';
            if (accredited) { classes += ' ls-quote-param-chip--acc'; }
            if (subcontracted) { classes += ' ls-quote-param-chip--sub'; }
            var badges = '';
            if (subcontracted) {
                badges += ' <i class="mdi mdi-asterisk" title="Subcontracted"></i>';
            }
            if (accredited) {
                badges += ' <i class="mdi mdi-check-decagram" title="Accredited"></i>';
            }
            return '<span class="' + classes + '">' + escapeQuoteHtml(label) + badges + '</span>';
        }

        /**
         * Parameters card under a quotation line.
         * Amspec math: chips are scope (tests); commercial total = qty × unit price once.
         * When chip count > 7, start collapsed with a numeric badge only (e.g. "12").
         */
        function buildParamsPanelHtml(title, rowNo, sampleTypeId, sampleTypeName, chipsHtml, openIcon, options) {
            options = options || {};
            var icon = openIcon || 'mdi-pencil';
            var safeTitle = escapeQuoteHtml(title || sampleTypeName || 'Parameters');
            var safeCode = escapeQuoteHtml(sampleTypeName || '');
            var $tmp = $('<div>' + (chipsHtml || '') + '</div>');
            var chipCount = $tmp.find('.ls-quote-param-chip').length;
            var collapsed = !options.compact && chipCount > 7;
            var countBadge = collapsed
                ? '<span class="ls-quote-params__count">' + chipCount + '</span>'
                : '';
            var titleHtml = options.compact
                ? ''
                : ('<button type="button" class="ls-quote-params__title js-quote-params-toggle" title="' + (collapsed ? 'Show parameters' : 'Hide parameters') + '">' +
                    safeTitle + countBadge +
                   '</button>');
            return '' +
                '<div class="ls-quote-params' + (collapsed ? ' is-collapsed' : '') + (options.compact ? ' ls-quote-params--compact' : '') + '" data-param-count="' + chipCount + '">' +
                    '<div class="ls-quote-params__toolbar' + (options.compact ? ' ls-quote-params__toolbar--compact' : '') + '">' +
                        '<button type="button" class="ls-btn ls-quote-params__edit" data-row="' + rowNo + '" data-samplecode="' + safeCode + '" data-sampletype="' + escapeQuoteHtml(sampleTypeId || '') + '" title="Edit parameters">' +
                            '<i class="mdi ' + icon + '"></i>' +
                        '</button>' +
                        titleHtml +
                    '</div>' +
                    '<div class="ls-select2-view ls-quote-params__chips">' + (chipsHtml || '<span class="text-muted small">No parameters</span>') + '</div>' +
                '</div>';
        }

        $(document).on('click', '.js-quote-params-toggle', function(e) {
            e.preventDefault();
            var $card = $(this).closest('.ls-quote-params');
            $card.toggleClass('is-collapsed');
            var collapsed = $card.hasClass('is-collapsed');
            $(this).attr('title', collapsed ? 'Show parameters' : 'Hide parameters');
            var count = parseInt($card.attr('data-param-count'), 10) || 0;
            var $badge = $(this).find('.ls-quote-params__count');
            if (collapsed && count > 0) {
                if (!$badge.length) {
                    $(this).append($('<span class="ls-quote-params__count"></span>').text(count));
                } else {
                    $badge.text(count);
                }
            } else {
                $badge.remove();
            }
        });

        function initLsSampleTypeSelect($el) {
            if (!$.fn.select2 || !$el.length) { return; }
            if ($el.data('select2') || $el.hasClass('select2-hidden-accessible')) {
                try { $el.select2('destroy'); } catch (err) {}
            }
            $el.addClass('ls-select2-multi-dropdown-search-el');
            var isSingle = $el.data('ls-single') === 1 || $el.data('ls-single') === '1' || !$el.prop('multiple');
            $el.select2({
                width: '100%',
                placeholder: $el.data('placeholder') || 'Choose sample type…',
                allowClear: true,
                closeOnSelect: isSingle,
                dropdownCssClass: 'ls-select2-dropdown-search',
                templateResult: function (data) {
                    if (!data.id) { return data.text; }
                    if (isSingle) { return data.text; }
                    var selected = ($el.val() || []).indexOf(String(data.id)) !== -1;
                    var $row = $('<span class="ls-select2-meta-row"><span class="ls-select2-check">' + (selected ? '✓' : '') + '</span><span class="ls-select2-meta-row__label"></span></span>');
                    $row.find('.ls-select2-meta-row__label').text(data.text);
                    return $row;
                },
                escapeMarkup: function (m) { return m; },
            });
            $el.off('select2:open.lsSampleDd select2:close.lsSampleDd select2:select.lsSampleDd select2:unselect.lsSampleDd')
                .on('select2:open.lsSampleDd', function () {
                    var $container = $el.next('.select2-container');
                    $container.find('.select2-search--inline .select2-search__field').attr(
                        'style',
                        'width:0!important;min-width:0!important;max-width:0!important;height:0!important;margin:0!important;padding:0!important;border:0!important;'
                    );
                    $container.css({ maxWidth: '100%', overflow: 'hidden' });
                    var $dropdown = $('.select2-container--open .select2-dropdown');
                    if ($dropdown.find('.ls-dd-search').length) {
                        $dropdown.find('.ls-dd-search input').val('').trigger('focus');
                        return;
                    }
                    var $box = $('<div class="ls-dd-search"><i class="mdi mdi-magnify" aria-hidden="true"></i><input type="search" placeholder="Search…" autocomplete="off"></div>');
                    $dropdown.prepend($box);
                    $box.find('input').on('input keyup', function () {
                        var q = $(this).val();
                        var $hidden = $el.data('select2') && $el.data('select2').$selection
                            ? $el.data('select2').$selection.find('.select2-search__field')
                            : $('.select2-container--open .select2-search--inline .select2-search__field');
                        $hidden.val(q).trigger('input').trigger('keyup');
                    });
                    setTimeout(function () { $box.find('input').trigger('focus'); }, 0);
                })
                .on('select2:close.lsSampleDd select2:select.lsSampleDd select2:unselect.lsSampleDd', function () {
                    var $container = $el.next('.select2-container');
                    $container.find('.select2-search--inline .select2-search__field').attr(
                        'style',
                        'width:0!important;min-width:0!important;max-width:0!important;height:0!important;margin:0!important;padding:0!important;border:0!important;'
                    );
                });
        }

        function initLsPartSelect($el) {
            if (!$.fn.select2 || !$el.length) { return; }
            if ($el.data('select2') || $el.hasClass('select2-hidden-accessible')) {
                try { $el.select2('destroy'); } catch (err) {}
            }
            $el.addClass('ls-select2-multi-dropdown-search-el');
            $el.select2({
                width: '100%',
                placeholder: $el.data('placeholder') || 'Select analysis type…',
                closeOnSelect: false,
                dropdownCssClass: 'ls-select2-dropdown-search',
                templateResult: function (data) {
                    if (!data.id) { return data.text; }
                    var selected = ($el.val() || []).indexOf(String(data.id)) !== -1;
                    var $row = $('<span class="ls-select2-meta-row"><span class="ls-select2-check">' + (selected ? '✓' : '') + '</span><span class="ls-select2-meta-row__label"></span></span>');
                    $row.find('.ls-select2-meta-row__label').text(data.text);
                    return $row;
                },
                escapeMarkup: function (m) { return m; },
            });
            $el.off('select2:open.lsLineDd select2:close.lsLineDd select2:select.lsLineDd select2:unselect.lsLineDd')
                .on('select2:open.lsLineDd', function () {
                    var $container = $el.next('.select2-container');
                    $container.find('.select2-search--inline .select2-search__field').attr(
                        'style',
                        'width:0!important;min-width:0!important;max-width:0!important;height:0!important;margin:0!important;padding:0!important;border:0!important;'
                    );
                    $container.css({ maxWidth: '100%', overflow: 'hidden' });
                    var $dropdown = $('.select2-container--open .select2-dropdown');
                    if ($dropdown.find('.ls-dd-search').length) {
                        $dropdown.find('.ls-dd-search input').val('').trigger('focus');
                        return;
                    }
                    var $box = $('<div class="ls-dd-search"><i class="mdi mdi-magnify" aria-hidden="true"></i><input type="search" placeholder="Search…" autocomplete="off"></div>');
                    $dropdown.prepend($box);
                    $box.find('input').on('input keyup', function () {
                        var q = $(this).val();
                        var $hidden = $el.data('select2') && $el.data('select2').$selection
                            ? $el.data('select2').$selection.find('.select2-search__field')
                            : $('.select2-container--open .select2-search--inline .select2-search__field');
                        $hidden.val(q).trigger('input').trigger('keyup');
                    });
                    setTimeout(function () { $box.find('input').trigger('focus'); }, 0);
                })
                .on('select2:close.lsLineDd select2:select.lsLineDd select2:unselect.lsLineDd', function () {
                    var $container = $el.next('.select2-container');
                    $container.find('.select2-search--inline .select2-search__field').attr(
                        'style',
                        'width:0!important;min-width:0!important;max-width:0!important;height:0!important;margin:0!important;padding:0!important;border:0!important;'
                    );
                });
        }

        function countAnalytePayload(data) {
            var count = 0;
            if (!data || typeof data !== 'object') {
                return 0;
            }
            Object.keys(data).forEach(function (key) {
                var rows = data[key];
                if (Array.isArray(rows)) {
                    count += rows.length;
                }
            });
            return count;
        }

        function labSectionHintText() {
            var names = quotationPricingConfig.labSectionNames || [];
            if (!names.length) {
                return '';
            }
            return 'This quotation is limited to lab section(s): ' + names.join(', ') + '.';
        }

        /**
         * Stage surfaces: loading | table | empty.
         * Table shell stays mounted; loading is an overlay. Never hide all panes at once.
         */
        function setParamsStageVisible(prefix, which) {
            var isEdit = prefix === 'edit';
            var $empty = $('#' + (isEdit ? 'edit-params-empty' : 'quote-params-empty'));
            var $wrap = $('#' + (isEdit ? 'edit-params-table-wrap' : 'quote-params-table-wrap'));
            var $loading = $('#' + (isEdit ? 'edit-params-loading' : 'quote-params-loading'));
            var $save = $('#' + (isEdit ? 'save-edit' : 'save-analytes'));

            if (!$wrap.length) {
                return;
            }

            if (which === 'empty') {
                $wrap.removeClass('is-stage-visible').prop('hidden', true).attr('hidden', 'hidden');
                $loading.prop('hidden', true).attr('hidden', 'hidden').removeClass('is-stage-visible');
                $empty.prop('hidden', false).removeAttr('hidden').addClass('is-stage-visible');
                $save.prop('disabled', true);
                return;
            }

            // loading | table — table shell always visible
            $empty.prop('hidden', true).attr('hidden', 'hidden').removeClass('is-stage-visible');
            $wrap.prop('hidden', false).removeAttr('hidden').addClass('is-stage-visible');

            if (which === 'loading') {
                $loading.prop('hidden', false).removeAttr('hidden').addClass('is-stage-visible');
                $save.prop('disabled', true);
            } else {
                $loading.prop('hidden', true).attr('hidden', 'hidden').removeClass('is-stage-visible');
                $save.prop('disabled', false);
            }
        }

        function setQuoteParamsStageVisible(which) {
            // Map legacy 'skeleton' to 'loading' overlay.
            setParamsStageVisible('quote', which === 'skeleton' ? 'loading' : which);
        }

        function showQuoteParamsEmpty(title, message) {
            $('#quote-params-empty-title').text(title || 'No parameters available');
            $('#quote-params-empty-text').text(message || '');
            setParamsStageVisible('quote', 'empty');
        }

        function showQuoteParamsLoading() {
            setParamsStageVisible('quote', 'loading');
        }

        function showQuoteParamsSkeleton() {
            showQuoteParamsLoading();
        }

        function showQuoteParamsTable() {
            setParamsStageVisible('quote', 'table');
        }

        function showEditParamsEmpty(title, message) {
            if (!$('#edit-params-empty').length) {
                return;
            }
            $('#edit-params-empty-title').text(title || 'No parameters available');
            $('#edit-params-empty-text').text(message || '');
            setParamsStageVisible('edit', 'empty');
        }

        function showEditParamsLoading() {
            setParamsStageVisible('edit', 'loading');
        }

        function showEditParamsSkeleton() {
            showEditParamsLoading();
        }

        function showEditParamsTable() {
            setParamsStageVisible('edit', 'table');
        }

        function applyPricelistSuggestionToRow($row, data) {
            $row.data('pricelistSuggestion', data);
            $row.find('.quotation-price-hint').text(data.hint || '');
            if (data && typeof data.unit_price !== 'undefined') {
                $row.find('.quotation-unit-price').val(data.unit_price);
            }
            if (data && typeof data.tax !== 'undefined') {
                $row.find('.quotation-tax').val(data.tax);
                $row.find('.quotation-tax-display').text(formatTaxDisplay(data.tax));
            }
            updateQuoteLineTotal($row);
        }

        function updateQuoteLineTotal($row) {
            var qty = parseFloat($row.find('.quotation-qty, input[name="quantity[]"]').val()) || 0;
            var unit = parseFloat($row.find('.quotation-unit-price').val()) || 0;
            // Amspec: Total = No. of samples × Unit price (once per package line).
            $row.find('.quotation-line-total').val((qty * unit).toFixed(2));
        }

        $(document).on('input change', '#create-detail .quotation-qty, #create-detail .quotation-unit-price', function () {
            updateQuoteLineTotal($(this).closest('tr'));
        });

        function clearRowParameterFields($row) {
            var $desc = $row.find('.quote-description-cell').first();
            $desc.empty().append(
                $('<div class="ls-quote-params ls-quote-params--empty"><span class="text-muted small">Select sample type, then Parameters</span></div>')
            );
            $row.find('input.select-part-final').val('');
            $row.find('.quotation-unit-price').val(0);
            applyDefaultTaxToRow($row);
            $row.removeData('pricelistSuggestion');
            $row.find('.quotation-price-hint').text('Pick tests in Parameters to load package / pricelist price.');
            updateQuoteLineTotal($row);
        }

        function writePackageDefaultsToRow($row, data, sampleTypeId, sampleTypeName) {
            var rowNo = $row.attr('id').replace('detail-row-', '');
            var elementIds = data.element_ids || [];
            var accreditedIds = data.accredited_ids || [];
            var defaultIds = data.default_ids || [];
            var parameters = data.parameters || [];
            var $desc = $row.find('.quote-description-cell').first();
            $desc.empty().removeClass('text-center');

            if (!defaultIds.length && elementIds.length) {
                var accreditedSet = {};
                accreditedIds.forEach(function(id) { accreditedSet[String(id)] = true; });
                defaultIds = elementIds.filter(function(id) { return !accreditedSet[String(id)]; });
            }

            var chipsHtml = '';
            if (parameters.length) {
                parameters.forEach(function(param) {
                    var label = param.label || param.id || '';
                    chipsHtml += buildParamChipHtml(label, !!param.accredited, false);
                });
            }
            $desc.append(buildParamsPanelHtml(sampleTypeName, rowNo, sampleTypeId, sampleTypeName, chipsHtml, 'mdi-pencil'));

            $desc.append($('<input type="hidden" name="accreditted_analytes[]">').val(accreditedIds.toString()));
            $desc.append($('<input type="hidden" name="sub_analytes[]">').val(''));
            $desc.append($('<input type="hidden" name="sub_acc[]">').val(''));
            $desc.append($('<input type="hidden" name="default_analytes[]">').val(defaultIds.toString()));
            $desc.append($('<input type="hidden" name="element_loq_json[]">').val('{}'));

            applyPricelistSuggestionToRow($row, data);
        }

        function refreshPackageDefaultsForRow($row) {
            var sampleTypeId = $row.find('select[name="sample_type[]"]').val() || '';
            var analysisTypeIds = $row.find('input.select-part-final, input[name="part_number_final[]"]').val() || '';
            var sampleTypeName = $row.find('select[name="sample_type[]"] option:selected').text() || '';
            var $hint = $row.find('.quotation-price-hint');

            if (!sampleTypeId) {
                clearRowParameterFields($row);
                $hint.text('Select sample type, then open Parameters.');
                return;
            }

            if (!analysisTypeIds) {
                var rowNoEmpty = $row.attr('id').replace('detail-row-', '');
                var $descEmpty = $row.find('.quote-description-cell').first();
                $descEmpty.empty().removeClass('text-center');
                $descEmpty.append($(buildParamsPanelHtml(sampleTypeName, rowNoEmpty, sampleTypeId, sampleTypeName, '', 'mdi-eye-outline')));
                $hint.text('Open Parameters to select tests for this sample type.');
                return;
            }

            $.post(quotationPricingConfig.packageDefaultsUrl, {
                _token: quotationPricingConfig.csrf,
                sample_type_id: sampleTypeId,
                analysis_type_ids: analysisTypeIds,
            }).done(function(data) {
                if (data && data.found) {
                    writePackageDefaultsToRow($row, data, sampleTypeId, sampleTypeName);
                    return;
                }

                // Keep eye button for manual param selection; clear prior package CSVs.
                var rowNo = $row.attr('id').replace('detail-row-', '');
                var $desc = $row.find('.quote-description-cell').first();
                var hasManualParams = $desc.find('input[name="default_analytes[]"]').length > 0
                    && ($desc.find('input[name="default_analytes[]"]').val() || '').length > 0;

                if (!hasManualParams) {
                    $desc.empty().removeClass('text-center');
                    $desc.append($(buildParamsPanelHtml(sampleTypeName, rowNo, sampleTypeId, sampleTypeName, '', 'mdi-eye-outline')));
                    $row.find('.quotation-unit-price').val(0);
                    applyDefaultTaxToRow($row);
                    $row.removeData('pricelistSuggestion');
                    $hint.text(data && data.hint ? data.hint : 'Pick tests in Parameters to load package / pricelist price.');
                } else {
                    refreshQuotationRowPricingHint($row, true);
                }
            }).fail(function() {
                $hint.text('Could not load package defaults.');
            });
        }

        function collectElementIdsFromRow($row) {
            var ids = [];
            $row.find('input[name="accreditted_analytes[]"], input[name="sub_analytes[]"], input[name="sub_acc[]"], input[name="default_analytes[]"]').each(function() {
                var value = ($(this).val() || '').trim();
                if (!value) {
                    return;
                }
                value.split(',').forEach(function(id) {
                    id = id.trim();
                    if (id) {
                        ids.push(id);
                    }
                });
            });

            return ids.filter(function(id, index) {
                return ids.indexOf(id) === index;
            });
        }

        function refreshQuotationRowPricingHint($row, applyValues) {
            var sampleTypeId = $row.find('select[name="sample_type[]"]').val() || '';
            var analysisTypeIds = $row.find('input.select-part-final, input#select-part-final, input[name="part_number_final[]"]').val() || '';
            var elementIds = collectElementIdsFromRow($row);
            var $hint = $row.find('.quotation-price-hint');
            var pricingMode = String($row.find('.quotation-pricing-mode').val() || 'per_package');

            if (!sampleTypeId || !analysisTypeIds) {
                $hint.text('Select sample type and analysis type.');
                $row.removeData('pricelistSuggestion');
                return;
            }

            if (elementIds.length === 0) {
                $hint.text('Pick tests in Parameters to load pricelist total.');
                $row.removeData('pricelistSuggestion');
                return;
            }

            $.post(quotationPricingConfig.suggestUrl, {
                _token: quotationPricingConfig.csrf,
                sample_type_id: sampleTypeId,
                analysis_type_ids: analysisTypeIds,
                element_ids: elementIds.join(','),
                pricing_mode: pricingMode,
            }).done(function(data) {
                if (applyValues) {
                    applyPricelistSuggestionToRow($row, data);
                } else {
                    $row.data('pricelistSuggestion', data);
                    $hint.text(data.hint || '');
                }
            }).fail(function() {
                $hint.text('Could not load pricelist suggestion.');
            });
        }

        $('form.ls-quotation-lines-form select[name="currency_id"]').on('change', function() {
            var currencyId = $(this).val();
            var label = $(this).find('option:selected').text();
            var hidden = document.getElementById('currency-id-edit');
            if (hidden) {
                hidden.value = currencyId || '';
                hidden.dispatchEvent(new Event('change', { bubbles: true }));
            }
            $('#display-currency-edit').val(currencyId ? label : '');
            var root = hidden ? hidden.closest('[data-ls-search-basic]') : null;
            if (root && window.Alpine && typeof Alpine.$data === 'function') {
                var data = Alpine.$data(root);
                data.selected = currencyId ? String(currencyId) : null;
                data.q = currencyId ? label : '';
            }
        });

        $('#print-quotation').on('show.bs.modal', function(e) {
            var header_id = $(e.relatedTarget).data('header');
            var load_text = $(`
                    <img src="/images/load.gif" width="70%" height="70%" alt="">
                    <div class="alert alert-primary">
                        <i class="mdi mdi-alert-octagon"></i> Kindly wait as the quotation pdf is being proccessed!
                    </div>`);
            $(this).find('.loading').empty();
            $(this).find('.loading').append(load_text);
            $.ajax({
                url: '/billing/print_quotation/' + header_id,
                success:function(data){
                    console.log(data);
                },
                complete:function(data){
                    $('#print-quotation').find('.loading').empty();
                    var complete_text = $(`
                    <img src="/images/suc.gif" width="50%" height="50%" alt="">
                    <div class="alert alert-success">
                        <i class="mdi mdi-alert-octagon"></i> Quote PDF generated successfully!
                    </div>
                    `);
                    $('#print-quotation').find('.loading').append(complete_text);
                },
                error:function(data){
                    console.log(data);
                }
            })
        })

        // Client / contact / location handled by quotation-show-header-scripts

        // Handle Dynamics customer selection and auto-populate currency for edit form
        $('#select-zoho-customer-edit').on('change', function() {
            var zohoCustomerId = $(this).val();
            var $selectedOption = $(this).find('option:selected');
            var currencyCode = $selectedOption.data('currency-code');

            if(zohoCustomerId && currencyCode) {
                // Fetch currency details from server
                $.ajax({
                    url: '/api/get-currency-by-code',
                    method: 'POST',
                    data: {
                        currency_code: currencyCode,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(currency) {
                        if(currency) {
                            $('#display-currency-edit').val(currency.code + ' - ' + currency.description);
                            var hidden = document.getElementById('currency-id-edit');
                            if (hidden) {
                                hidden.value = currency.id;
                            }
                            var root = hidden ? hidden.closest('[data-ls-search-basic]') : null;
                            if (root && window.Alpine && typeof Alpine.$data === 'function') {
                                var data = Alpine.$data(root);
                                data.selected = String(currency.id);
                                data.q = currency.code + ' - ' + currency.description;
                            }
                        } else {
                            $('#display-currency-edit').val('Currency not found');
                            $('#currency-id-edit').val('');
                        }
                    },
                    error: function(err) {
                        console.log(err);
                        $('#display-currency-edit').val('Error loading currency');
                        $('#currency-id-edit').val('');
                    }
                });
            } else {
                $('#display-currency-edit').val('');
                $('#currency-id-edit').val('');
            }
        });

        $('#add-row').on('click', function() {
            var roeNo = nextDetailRowNo();
            var $row = cloneBlankAnalysisLine(roeNo);
            $('#create-detail').append($row);
            renumberQuoteAnalysisLines();
        });

        var quotationDetailProp = function(data, sample_type, rowNo, sample_code) {
            var $row = $('#create-detail').find('tr#detail-row-' + rowNo);
            $row.find('input.select-part-final').val('');
            $row.find('.quote-description-cell').first().empty().append($(buildParamsPanelHtml(sample_code, rowNo, sample_type, sample_code, '', 'mdi-eye-outline')));
            $row.find('.quotation-price-hint').text('Open Parameters to select tests for this sample type.');
        }

        $('#detail-edit-mode').on('show.bs.modal', function(e) {
            var detail_data = $(e.relatedTarget).data('detail')
            var sample_name = $(e.relatedTarget).data('sample');
            $(this).find('.modal-title').empty();
            var header = $(`<span><i class="mdi mdi-pencil"></i> Edit ${escapeQuoteHtml(sample_name)} Details</span>`)
            $(this).find('.modal-title').append(header);
            if (detail_data != '') {
                $.ajax({
                    url: '/fetch-detail-data/' + detail_data,
                    beforeSend: function() {
                        $(this).find('h5.modal-title').empty();
                        $('#detail-edit-mode').find('.modal-body').empty();
                    },
                    success: function(data) {
                        // console.log(data['analysis'])
                        var row = detail_edit_body(data['detail'], data['analysis_data'])
                        $('#detail-edit-mode').find('.modal-body').append(row);
                    },
                    error: function(data) {
                        console.log(data);
                    }
                })
            }
        })
        var detail_edit_body = function(data, analysis) {
            var taxLabel = (data.tax !== null && typeof data.tax !== 'undefined')
                ? parseFloat(data.tax).toFixed(2)
                : '0.00';
            var editRow = $(`
            <div class="ls-form-grid ls-form-grid--2">
                <div class="ls-field ls-compact is-disabled">
                    <label class="ls-field__label">Sample Type</label>
                    <div class="ls-field__control">
                        <input type="text" class="ls-field__input" value="${escapeQuoteHtml(data.sample_type_name || '')}" disabled>
                    </div>
                    <input type="hidden" id="sample-type-id" value="${data.sample_type}">
                    <input type="hidden" name="detail_id" id="detail-id" value="${data.id}">
                </div>
                <div class="ls-field ls-compact">
                    <label class="ls-field__label" for="edit-part-no">Analysis Type</label>
                    <div class="ls-select2-multi ls-select2-slate">
                        <select class="ls-select2-view-edit-el form-control" name="part_no[]" multiple id="edit-part-no" data-placeholder="Select analysis type…" style="width:100%;"></select>
                    </div>
                </div>
                <div class="ls-field ls-compact">
                    <label class="ls-field__label">Quantity Required</label>
                    <div class="ls-field__control">
                        <input type="text" name="quantity_required" value="${escapeQuoteHtml(data.quantity_required || '')}" class="ls-field__input" placeholder="e.g. Per Sample Swab">
                    </div>
                </div>
                <div class="ls-field ls-compact">
                    <label class="ls-field__label">No. of Samples</label>
                    <div class="ls-field__control">
                        <input type="text" name="quantity" value="${data.quantity}" class="ls-field__input">
                    </div>
                </div>
                <div class="ls-field ls-compact">
                    <label class="ls-field__label">Unit Price</label>
                    <div class="ls-field__control">
                        <input type="number" name="unit_price" value="${data.unit_price}" class="ls-field__input" min="0" step="1" placeholder="0 = pricelist">
                    </div>
                    <p class="ls-field__hint">Total = samples × unit price (once per package).</p>
                </div>
                <div class="ls-field ls-compact">
                    <label class="ls-field__label">Tax %</label>
                    <input type="hidden" name="tax" value="${data.tax}" class="quotation-tax">
                    <span class="ls-quote-tax-display">${taxLabel}% (from pricelist)</span>
                </div>
            </div>
            <div class="ls-field ls-compact mt-3 mb-0">
                <div class="ls-quote-params__toolbar mb-2">
                    <label class="ls-field__label mb-0">Parameters</label>
                    <button type="button" class="ls-btn ls-quote-params__edit" data-toggle="modal" data-target="#edit-detail-analytes" title="Edit parameters">
                        <i class="mdi mdi-pencil"></i>
                    </button>
                </div>
                <div class="description-analytes ls-quote-params ls-quote-params__chips-host">
                    <div class="ls-select2-view ls-quote-params__chips"></div>
                </div>
            </div>
            `).clone();

            var $part = $(editRow).find('#edit-part-no');
            $.each(analysis, function(j, s) {
                $part.append($(`<option value="${s.id}">${s.name}</option>`));
            });
            $part.val(data.part_no_value);
            initLsPartSelect($part);

            var $chips = $(editRow).find('.ls-quote-params__chips');
            $.each(data.default || [], function(i, e) { $chips.append($(buildParamChipHtml(e, false, false))); });
            $.each(data.acc_analytes || [], function(i, e) { $chips.append($(buildParamChipHtml(e, true, false))); });
            $.each(data.sub_analytes || [], function(i, e) { $chips.append($(buildParamChipHtml(e, false, true))); });
            $.each(data.sub_acc || [], function(i, e) { $chips.append($(buildParamChipHtml(e, true, true))); });

            var $analyteHolder = $(editRow).find('.description-analytes');
            $analyteHolder.append($('<input type="hidden" name="accreditted_analytes">').val(data.accredited_analytes || ''));
            $analyteHolder.append($('<input type="hidden" name="sub_analytes">').val(data.subcontracted_analytes || ''));
            $analyteHolder.append($('<input type="hidden" name="sub_acc">').val(data.sub_acc_analytes || ''));
            $analyteHolder.append($('<input type="hidden" name="default_analytes">').val(data.default_analytes || ''));

            return editRow;
        }
        $('#edit-detail-analytes').on('show.bs.modal', function(e) {
            var sample_type = $('#detail-edit-mode').find('#sample-type-id').val();
            var analysis_s = $('#detail-edit-mode').find('#edit-part-no').val();
            var detail = $('#detail-edit-mode').find('#detail-id').val();
            var savedState = readRowParameterState($('#detail-edit-mode'));
            var sectionHint = labSectionHintText();
            var $tbody = $('#edit-description');

            $('#edit-analyte-all, #edit-accreditted, #edit-sub').prop('checked', false);
            // Keep existing rows under loading overlay; only clear when replacing.
            showEditParamsLoading();

            if (!sample_type) {
                $tbody.empty();
                showEditParamsEmpty('Sample type required', 'This line is missing a sample type.');
                return;
            }
            if (!analysis_s || (Array.isArray(analysis_s) && analysis_s.length === 0)) {
                $tbody.empty();
                showEditParamsEmpty(
                    'Analysis type required',
                    'Select at least one analysis type before editing parameters.'
                    + (sectionHint ? ' ' + sectionHint : '')
                );
                return;
            }

            $.ajax({
                url: '/fetch-sample-analytes/' + sample_type + '/' + encodeURIComponent(analysis_s.toString()) + '/' + detail + '?quotation_header_id=' + encodeURIComponent(quotationPricingConfig.quotationHeaderId),
                beforeSend: function() {
                    $('#edit-analyte-all, #edit-accreditted, #edit-sub').prop('checked', false);
                    showEditParamsLoading();
                },
                success: function(data) {
                    if (countAnalytePayload(data) === 0) {
                        var msg = 'No tests/parameters were found for the selected analysis type.';
                        if (sectionHint) {
                            msg = sectionHint
                                + ' No matching parameters exist for this analysis type within those section(s). '
                                + 'Try another analysis type, or update the quotation lab section(s) in the header.';
                        }
                        $tbody.empty();
                        showEditParamsEmpty('No parameters available', msg);
                        return;
                    }

                    var rowNodes = [];
                    $.each(data, function(i, e) {
                        rowNodes.push($('<tr class="ls-quote-analyte-group"><td colspan="7">' + escapeQuoteHtml(i) + '</td></tr>')[0]);
                        $.each(e, function(j, s) {
                            var $rowEl = quoteAnalytesRow(s);
                            if ($rowEl && $rowEl.length) {
                                rowNodes.push($rowEl[0]);
                            }
                        });
                    });
                    $tbody.empty().append(rowNodes);
                    showEditParamsTable();
                    applyParameterStateToModal($('#edit-detail-analytes'), savedState);
                },
                error: function() {
                    $tbody.empty();
                    showEditParamsEmpty(
                        'Could not load parameters',
                        'Something went wrong while loading parameters. Check analysis type and lab section settings, then try again.'
                    );
                }
            });
        });

        var quoteDescriptionModalRowNo = null;
        var quoteDescriptionModalSampleCode = null;
        var quoteDescriptionModalTypeName = null;

        function persistEditedLoqs($modal) {
            var requests = [];
            $modal.find('input.analyte-loq').each(function() {
                var $input = $(this);
                var elementId = $input.data('element-id');
                var original = String($input.data('original-loq') ?? '');
                var current = String($input.val() ?? '').trim();
                if (!elementId || current === original) {
                    return;
                }
                requests.push($.post(quotationPricingConfig.updateLoqUrl, {
                    _token: quotationPricingConfig.csrf,
                    element_id: elementId,
                    loq: current,
                }).done(function(resp) {
                    $input.data('original-loq', resp.loq || current);
                    $input.val(resp.loq || current);
                }));
            });
            if (requests.length === 0) {
                return $.Deferred().resolve().promise();
            }
            return $.when.apply($, requests);
        }

        function splitCsvIds(value) {
            return String(value || '')
                .split(',')
                .map(function(id) { return id.trim(); })
                .filter(function(id) { return id !== ''; });
        }

        function readRowParameterState($row) {
            var accredited = splitCsvIds($row.find('input[name="accreditted_analytes[]"], input[name="accreditted_analytes"]').val());
            var sub = splitCsvIds($row.find('input[name="sub_analytes[]"], input[name="sub_analytes"]').val());
            var subAcc = splitCsvIds($row.find('input[name="sub_acc[]"], input[name="sub_acc"]').val());
            var defaults = splitCsvIds($row.find('input[name="default_analytes[]"], input[name="default_analytes"]').val());
            var loqMap = {};
            var loqRaw = $row.find('input[name="element_loq_json[]"], input[name="element_loq_json"]').val();
            if (loqRaw) {
                try {
                    var parsed = JSON.parse(loqRaw);
                    if (parsed && typeof parsed === 'object') {
                        loqMap = parsed;
                    }
                } catch (e) {
                    loqMap = {};
                }
            }

            return {
                selectedIds: accredited.concat(sub, subAcc, defaults).filter(function(id, index, all) {
                    return all.indexOf(id) === index;
                }),
                accreditedIds: accredited,
                subIds: sub,
                subAccIds: subAcc,
                defaultIds: defaults,
                loqMap: loqMap,
                hasSavedState: accredited.length + sub.length + subAcc.length + defaults.length > 0,
            };
        }

        function applyParameterStateToModal($scope, state) {
            if (!state || !state.hasSavedState) {
                return;
            }

            var selectedSet = {};
            state.selectedIds.forEach(function(id) { selectedSet[id] = true; });
            var accreditedSet = {};
            state.accreditedIds.forEach(function(id) { accreditedSet[id] = true; });
            var subSet = {};
            state.subIds.forEach(function(id) { subSet[id] = true; });
            var subAccSet = {};
            state.subAccIds.forEach(function(id) { subAccSet[id] = true; });
            $scope.find('tr').each(function() {
                var $tr = $(this);
                var $selected = $tr.find('.analyte-selected');
                if (!$selected.length) {
                    return;
                }

                var elementId = String($selected.val() || '');
                $selected.prop('checked', !!selectedSet[elementId]);

                var isBoth = !!subAccSet[elementId];
                var isAcc = isBoth || !!accreditedSet[elementId];
                var isSub = isBoth || !!subSet[elementId];
                $tr.find('.analyte-accredited').prop('checked', isAcc);
                $tr.find('.analyte-subcontracted').prop('checked', isSub);
                syncMethodPillAccreditation($tr);

                if (Object.prototype.hasOwnProperty.call(state.loqMap, elementId)) {
                    var $loq = $tr.find('input.analyte-loq');
                    var loqValue = String(state.loqMap[elementId] ?? '');
                    $loq.val(loqValue);
                    $loq.data('original-loq', loqValue);
                }
            });
        }

        function collectSelectedAnalyteState($scope) {
            var analytes_accreditted = [];
            var analyte_sub = [];
            var sub_acc = [];
            var default_analytes = [];
            var analysisTypeIds = [];
            var loqMap = {};
            var maxTat = null;
            var spans = [];
            var tests = [];

            $scope.find('.analyte-selected').each(function() {
                var $selected = $(this);
                var parent_tr = $selected.closest('tr');
                if (!$selected.prop('checked')) {
                    return;
                }

                var analyte_name = String(
                    parent_tr.find('.ls-quote-analyte-row__label').first().text()
                    || parent_tr.find('.analyte-name').val()
                    || ''
                ).trim();
                var analyte_id = String($selected.val() || '');
                var analysisTypeId = String(parent_tr.attr('data-analysis-type-id') || '').trim();
                if (analysisTypeId && analysisTypeIds.indexOf(analysisTypeId) === -1) {
                    analysisTypeIds.push(analysisTypeId);
                }
                var loqInput = parent_tr.find('input.analyte-loq');
                if (analyte_id) {
                    loqMap[analyte_id] = String(loqInput.val() || '').trim();
                }
                var reportingTime = parseInt(parent_tr.attr('data-reporting-time'), 10);
                if (!isNaN(reportingTime) && reportingTime > 0) {
                    maxTat = maxTat === null ? reportingTime : Math.max(maxTat, reportingTime);
                }

                var isAcc = parent_tr.find('.analyte-accredited').prop('checked');
                var isSub = parent_tr.find('.analyte-subcontracted').prop('checked');
                if (isAcc && !isSub) {
                    analytes_accreditted.push(analyte_id);
                } else if (isSub && !isAcc) {
                    analyte_sub.push(analyte_id);
                } else if (isAcc && isSub) {
                    sub_acc.push(analyte_id);
                } else {
                    default_analytes.push(analyte_id);
                }
                spans.push(buildParamChipHtml(analyte_name, isAcc, isSub));
                tests.push({
                    id: analyte_id,
                    name: analyte_name,
                    analysisTypeId: analysisTypeId,
                    isAcc: !!isAcc,
                    isSub: !!isSub,
                    loq: analyte_id ? String(loqMap[analyte_id] || '') : '',
                });
            });

            return {
                analytes_accreditted: analytes_accreditted,
                analyte_sub: analyte_sub,
                sub_acc: sub_acc,
                default_analytes: default_analytes,
                analysisTypeIds: analysisTypeIds,
                loqMap: loqMap,
                maxTat: maxTat,
                spans: spans,
                tests: tests,
            };
        }

        $('#save-edit').off('click').on('click', function(e) {
            e.preventDefault();
            var $modal = $('#edit-detail-analytes');
            persistEditedLoqs($modal).always(function() {
                var state = collectSelectedAnalyteState($modal);
                var $holder = $('#detail-edit-mode').find('.description-analytes').first();
                $holder.empty();
                $holder.append($('<div class="ls-select2-view ls-quote-params__chips"></div>').append(state.spans.join('')));
                $holder.append($('<input type="hidden" name="accreditted_analytes">').val(state.analytes_accreditted.toString()));
                $holder.append($('<input type="hidden" name="sub_analytes">').val(state.analyte_sub.toString()));
                $holder.append($('<input type="hidden" name="sub_acc">').val(state.sub_acc.toString()));
                $holder.append($('<input type="hidden" name="default_analytes">').val(state.default_analytes.toString()));
                $holder.append($('<input type="hidden" name="element_loq_json">').val(JSON.stringify(state.loqMap)));
                $('#edit-detail-analytes').modal('hide');
            });
        });
        var quoteParamsLoadToken = 0;
        var quoteParamsOpenContext = null;
        var quoteParamsXhr = null;
        var quoteParamsLoadTimer = null;

        function readQuoteParamsButtonContext($btn) {
            if (!$btn || !$btn.length) {
                return null;
            }
            // Prefer .attr() over .data() — jQuery data() caches/coerces and can go stale after explode/rebuild.
            return {
                row: $btn.attr('data-row') || $btn.data('row'),
                sampletype: $btn.attr('data-sampletype') || $btn.data('sampletype'),
                samplecode: $btn.attr('data-samplecode') || $btn.data('samplecode'),
            };
        }

        function readRowPricingModeSafe($row) {
            if (!$row || !$row.length) {
                return 'per_package';
            }
            var mode = String($row.find('.quotation-pricing-mode').val() || '');
            if (mode !== 'per_test' && $row.find('.ls-quote-line-mode-input').is(':checked')) {
                mode = 'per_test';
            }
            return mode === 'per_test' ? 'per_test' : 'per_package';
        }

        function scheduleLoadQuoteParametersModal() {
            if (quoteParamsLoadTimer) {
                clearTimeout(quoteParamsLoadTimer);
            }
            // Collapse click + show.bs.modal (and any double-fire) into one load.
            quoteParamsLoadTimer = setTimeout(function () {
                quoteParamsLoadTimer = null;
                loadQuoteParametersModal();
            }, 0);
        }

        function loadQuoteParametersModal() {
            var ctx = quoteParamsOpenContext || {};
            var sample_code = String(ctx.sampletype || '');
            var type_name = String(ctx.samplecode || '');
            var row_no = ctx.row || '';
            quoteDescriptionModalRowNo = row_no;
            quoteDescriptionModalSampleCode = sample_code;
            quoteDescriptionModalTypeName = type_name;

            var $detailRow = (row_no !== '' && row_no !== null && typeof row_no !== 'undefined')
                ? $('#create-detail').find('tr#detail-row-' + row_no)
                : $();

            var groupId = $detailRow.length ? String($detailRow.attr('data-per-test-group') || '') : '';
            if (groupId) {
                var $lead = $('#create-detail > tr.ls-quote-line--per-test-lead[data-per-test-group="' + groupId + '"]').first();
                if ($lead.length) {
                    $detailRow = $lead;
                    quoteDescriptionModalRowNo = String($lead.attr('id') || '').replace('detail-row-', '');
                    var leadType = String($lead.find('select[name="sample_type[]"]').val() || '');
                    if (leadType) {
                        sample_code = leadType;
                    }
                    type_name = $lead.find('select[name="sample_type[]"] option:selected').text() || type_name;
                    quoteDescriptionModalSampleCode = sample_code;
                    quoteDescriptionModalTypeName = type_name;
                }
            }

            if ((!sample_code || sample_code === 'undefined') && $detailRow.length) {
                sample_code = String(
                    $detailRow.find('select[name="sample_type[]"]').val()
                    || $detailRow.find('input.ls-quote-per-test-sample-type').val()
                    || ''
                );
                type_name = $detailRow.find('select[name="sample_type[]"] option:selected').text() || type_name || '';
                quoteDescriptionModalSampleCode = sample_code;
                quoteDescriptionModalTypeName = type_name;
            }

            var savedState = $detailRow.length
                ? readRowParameterState($detailRow)
                : { hasSavedState: false, selectedIds: [] };
            if (groupId) {
                var merged = {
                    selectedIds: [],
                    accreditedIds: [],
                    subIds: [],
                    subAccIds: [],
                    defaultIds: [],
                    loqMap: {},
                    hasSavedState: false,
                };
                $('#create-detail > tr[data-per-test-group="' + groupId + '"]').each(function () {
                    var part = readRowParameterState($(this));
                    merged.selectedIds = merged.selectedIds.concat(part.selectedIds);
                    merged.accreditedIds = merged.accreditedIds.concat(part.accreditedIds);
                    merged.subIds = merged.subIds.concat(part.subIds);
                    merged.subAccIds = merged.subAccIds.concat(part.subAccIds);
                    merged.defaultIds = merged.defaultIds.concat(part.defaultIds);
                    Object.assign(merged.loqMap, part.loqMap || {});
                });
                var uniq = function (arr) {
                    return arr.filter(function (id, index, all) { return id && all.indexOf(id) === index; });
                };
                merged.selectedIds = uniq(merged.selectedIds);
                merged.accreditedIds = uniq(merged.accreditedIds);
                merged.subIds = uniq(merged.subIds);
                merged.subAccIds = uniq(merged.subAccIds);
                merged.defaultIds = uniq(merged.defaultIds);
                merged.hasSavedState = merged.selectedIds.length > 0;
                savedState = merged;
            }

            var sectionHint = labSectionHintText();
            var rowMode = readRowPricingModeSafe($detailRow);
            var $caption = $('#quote-description-analytes .ls-quote-params-hero__caption');
            if ($caption.length) {
                $caption.text(rowMode === 'per_test'
                    ? 'Select tests for this sample. Each selected test becomes its own billed line (qty × unit price). Grouped by analysis type; only lab sections on the quotation are shown.'
                    : 'Select tests for this sample package. Billing is qty × unit price once — not per test. Grouped by analysis type; only lab sections on the quotation are shown.');
            }

            $('#select-analyte-all, #select-accredited-all, #select-sub-all').prop('checked', false);

            if (!sample_code || sample_code === 'undefined') {
                $('#analysis-analytes-holder').empty();
                showQuoteParamsEmpty(
                    'Sample type required',
                    'Choose a sample type on this line before opening Quotation Parameters.'
                );
                return;
            }

            // Loading overlay on mounted table — do not clear tbody until replacement rows are ready.
            showQuoteParamsLoading();

            if (quoteParamsXhr && typeof quoteParamsXhr.abort === 'function') {
                try { quoteParamsXhr.abort(); } catch (err) {}
            }

            var loadToken = ++quoteParamsLoadToken;
            var headerId = quotationPricingConfig && quotationPricingConfig.quotationHeaderId
                ? quotationPricingConfig.quotationHeaderId
                : '';
            quoteParamsXhr = $.ajax({
                url: '/fetch-sample-analytes/' + encodeURIComponent(sample_code) + '/' + encodeURIComponent('all')
                    + '?quotation_header_id=' + encodeURIComponent(headerId),
                success: function (data) {
                    if (loadToken !== quoteParamsLoadToken) {
                        return;
                    }
                    try {
                        var analyteCount = countAnalytePayload(data);
                        if (analyteCount === 0) {
                            var msg = 'No tests/parameters were found for this sample type.';
                            if (sectionHint) {
                                msg = sectionHint
                                    + ' No matching parameters exist for this sample within those section(s). '
                                    + 'Update the quotation lab section(s) in the header, or check sample setup.';
                            }
                            $('#analysis-analytes-holder').empty();
                            showQuoteParamsEmpty('No parameters available', msg);
                            return;
                        }

                        var rowNodes = [];
                        $.each(data, function (i, e) {
                            rowNodes.push($('<tr class="ls-quote-analyte-group"><td colspan="7">' + escapeQuoteHtml(i) + '</td></tr>')[0]);
                            $.each(e, function (j, s) {
                                var $rowEl = quoteAnalytesRow(s);
                                if ($rowEl && $rowEl.length) {
                                    rowNodes.push($rowEl[0]);
                                }
                            });
                        });
                        $('#analysis-analytes-holder').empty().append(rowNodes);
                        showQuoteParamsTable();

                        if (savedState && savedState.hasSavedState) {
                            applyParameterStateToModal($('#quote-description-analytes'), savedState);
                        } else if (rowMode === 'per_package') {
                            $('#quote-description-analytes').find('.analyte-selected').prop('checked', true);
                            $('#select-analyte-all').prop('checked', true);
                        } else {
                            $('#quote-description-analytes').find('.analyte-selected').prop('checked', false);
                            $('#select-analyte-all').prop('checked', false);
                        }
                    } catch (err) {
                        $('#analysis-analytes-holder').empty();
                        showQuoteParamsEmpty(
                            'Could not load parameters',
                            'Something went wrong while rendering parameters. Close and try again.'
                        );
                    }
                },
                error: function (xhr, status) {
                    if (loadToken !== quoteParamsLoadToken || status === 'abort') {
                        return;
                    }
                    $('#analysis-analytes-holder').empty();
                    showQuoteParamsEmpty(
                        'Could not load parameters',
                        'Something went wrong while loading parameters. Check the sample type and lab section settings, then try again.'
                    );
                },
            });
        }

        $(document).on('click', '#create-detail .ls-quote-params__edit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            quoteParamsOpenContext = readQuoteParamsButtonContext($(this));
            var $modal = $('#quote-description-analytes');
            // Loading overlay immediately so the stage is never blank white during the open animation.
            showQuoteParamsLoading();
            if ($modal.hasClass('show')) {
                scheduleLoadQuoteParametersModal();
            } else {
                $modal.modal('show');
            }
        });

        $('#quote-description-analytes')
            .off('show.bs.modal.lsQuoteParams shown.bs.modal.lsQuoteParams hide.bs.modal.lsQuoteParams')
            .on('show.bs.modal.lsQuoteParams', function () {
                showQuoteParamsLoading();
                scheduleLoadQuoteParametersModal();
            })
            .on('shown.bs.modal.lsQuoteParams', function () {
                // Re-assert a non-blank stage without wiping an in-flight loading overlay.
                var $loading = $('#quote-params-loading');
                var $empty = $('#quote-params-empty');
                var hasRows = $('#analysis-analytes-holder tr.ls-quote-analyte-row').length > 0;
                if ($loading.hasClass('is-stage-visible') || !$loading.prop('hidden')) {
                    showQuoteParamsLoading();
                } else if ($empty.hasClass('is-stage-visible')) {
                    setParamsStageVisible('quote', 'empty');
                } else if (hasRows) {
                    showQuoteParamsTable();
                } else {
                    showQuoteParamsLoading();
                }
            })
            .on('hide.bs.modal.lsQuoteParams', function () {
                if (quoteParamsLoadTimer) {
                    clearTimeout(quoteParamsLoadTimer);
                    quoteParamsLoadTimer = null;
                }
                quoteParamsLoadToken += 1;
                if (quoteParamsXhr && typeof quoteParamsXhr.abort === 'function') {
                    try { quoteParamsXhr.abort(); } catch (err) {}
                }
            });
        function nextDetailRowNo() {
            var max = 0;
            $('#create-detail tr[id^="detail-row-"]').each(function () {
                var n = parseInt(String(this.id).replace('detail-row-', ''), 10);
                if (!isNaN(n) && n > max) {
                    max = n;
                }
            });
            return max + 1;
        }

        function renumberQuoteAnalysisLines() {
            $('#create-detail > tr').each(function (index) {
                $(this).find('.ls-quote-line-no').first().text(index + 1);
            });
        }

        function newPerTestGroupId() {
            return 'ptg-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 8);
        }

        function rowPricingMode($row) {
            applyManualLineModeUi($row);
            return String($row.find('.quotation-pricing-mode').val() || 'per_package');
        }

        function removePerTestGroupSiblings($keepRow, groupId) {
            if (!groupId) {
                return;
            }
            $('#create-detail > tr').each(function () {
                var $tr = $(this);
                if ($tr[0] === $keepRow[0]) {
                    return;
                }
                if (String($tr.attr('data-per-test-group') || '') === String(groupId)) {
                    $tr.remove();
                }
            });
        }

        function clearPerTestVisuals($row) {
            $row.removeClass('ls-quote-line--per-test ls-quote-line--per-test-lead ls-quote-line--per-test-cont');
            $row.removeAttr('data-per-test-group');
            $row.find('.ls-quote-per-test-band, .ls-quote-per-test-cont-label, .ls-quote-per-test-sample-stack').remove();
            $row.find('.ls-quote-col-sample .select2-container, .ls-quote-line-mode-wrap').css('display', '');
            $row.find('.ls-quote-col-sample .ls-quote-line-mode-wrap').removeClass('d-none');
            var $sample = $row.find('td.ls-quote-col-sample').first();
            $sample.removeAttr('rowspan').removeClass('ls-quote-col-sample--merged-away ls-quote-col-sample--per-test-stack').css('display', '');
        }

        /**
         * Uncheck Per parameter: merge exploded sibling rows back into one package line.
         */
        function collapsePerTestGroupToPackage($row) {
            var groupId = String($row.attr('data-per-test-group') || '');
            var $lead = $row;
            if (groupId) {
                var $group = $('#create-detail > tr[data-per-test-group="' + groupId + '"]');
                if ($group.length) {
                    $lead = $group.filter('.ls-quote-line--per-test-lead').first();
                    if (!$lead.length) {
                        $lead = $group.first();
                    }
                }
            }

            var sampleTypeId = String($lead.find('select[name="sample_type[]"]').val()
                || $lead.find('input.ls-quote-per-test-sample-type').val()
                || '');
            var sampleTypeName = String(
                $lead.find('select[name="sample_type[]"] option:selected').text()
                || sampleTypeId
            );

            var merged = {
                tests: [],
                analysisTypeIds: [],
                analytes_accreditted: [],
                analyte_sub: [],
                sub_acc: [],
                default_analytes: [],
                loqMap: {},
                spans: [],
            };

            var $rows = groupId
                ? $('#create-detail > tr[data-per-test-group="' + groupId + '"]')
                : $lead;
            $rows.each(function () {
                var $tr = $(this);
                var part = readRowParameterState($tr);
                merged.analytes_accreditted = merged.analytes_accreditted.concat(part.accreditedIds || []);
                merged.analyte_sub = merged.analyte_sub.concat(part.subIds || []);
                merged.sub_acc = merged.sub_acc.concat(part.subAccIds || []);
                merged.default_analytes = merged.default_analytes.concat(part.defaultIds || []);
                Object.assign(merged.loqMap, part.loqMap || {});
                var analysisTypeId = String($tr.find('input.select-part-final, input[name="part_number_final[]"]').val() || '');
                if (analysisTypeId) {
                    analysisTypeId.split(',').forEach(function (id) {
                        id = String(id || '').trim();
                        if (id && merged.analysisTypeIds.indexOf(id) === -1) {
                            merged.analysisTypeIds.push(id);
                        }
                    });
                }
                (part.selectedIds || []).forEach(function (id) {
                    if (!id) {
                        return;
                    }
                    var $chip = $tr.find('.ls-quote-param-chip').first();
                    var label = $chip.length
                        ? $chip.clone().children().remove().end().text().trim()
                        : id;
                    merged.spans.push(buildParamChipHtml(
                        label,
                        (part.accreditedIds || []).indexOf(id) !== -1 || (part.subAccIds || []).indexOf(id) !== -1,
                        (part.subIds || []).indexOf(id) !== -1 || (part.subAccIds || []).indexOf(id) !== -1
                    ));
                });
            });

            var uniq = function (arr) {
                return arr.filter(function (id, index, all) { return id && all.indexOf(id) === index; });
            };
            merged.analytes_accreditted = uniq(merged.analytes_accreditted);
            merged.analyte_sub = uniq(merged.analyte_sub);
            merged.sub_acc = uniq(merged.sub_acc);
            merged.default_analytes = uniq(merged.default_analytes);

            if (groupId) {
                removePerTestGroupSiblings($lead, groupId);
            }
            writePackageParameterFields($lead, merged, sampleTypeId, sampleTypeName);
            $lead.find('.ls-quote-line-mode-input').prop('checked', false).data('userTouched', true);
            applyManualLineModeUi($lead);
            renumberQuoteAnalysisLines();
        }

        function writeSingleTestParameterFields($row, test, sampleTypeId, sampleTypeName) {
            var rowNo = String($row.attr('id') || '').replace('detail-row-', '');
            var $desc = $row.find('.quote-description-cell').first();
            $desc.attr('id', 'quote-description-' + rowNo);
            $desc.empty().removeClass('text-center');

            var chipsHtml = buildParamChipHtml(test.name, test.isAcc, test.isSub);
            // Compact panel: chip only (no duplicate title above the pill).
            $desc.append(buildParamsPanelHtml('', rowNo, sampleTypeId, sampleTypeName, chipsHtml, 'mdi-pencil', { compact: true }));

            var acc = test.isAcc && !test.isSub ? test.id : '';
            var sub = test.isSub && !test.isAcc ? test.id : '';
            var both = test.isAcc && test.isSub ? test.id : '';
            var def = (!test.isAcc && !test.isSub) ? test.id : '';
            var loqMap = {};
            if (test.id && test.loq !== undefined && test.loq !== null && String(test.loq) !== '') {
                loqMap[test.id] = String(test.loq);
            }

            $desc.append($('<input type="hidden" name="accreditted_analytes[]">').val(acc));
            $desc.append($('<input type="hidden" name="sub_analytes[]">').val(sub));
            $desc.append($('<input type="hidden" name="sub_acc[]">').val(both));
            $desc.append($('<input type="hidden" name="default_analytes[]">').val(def));
            $desc.append($('<input type="hidden" name="element_loq_json[]">').val(JSON.stringify(loqMap)));

            $row.find('input.select-part-final, input[name="part_number_final[]"]').val(test.analysisTypeId || '');
            $row.find('.quotation-pricing-mode').val('per_test');
            $row.find('.ls-quote-line-mode-input').prop('checked', true).data('userTouched', true);
        }

        function markPerTestLead($row, groupId, testCount, sampleTypeName) {
            clearPerTestVisuals($row);
            $row.addClass('ls-quote-line--per-test ls-quote-line--per-test-lead');
            $row.attr('data-per-test-group', groupId);
            var $sample = $row.find('.ls-quote-col-sample').first();
            $sample.addClass('ls-quote-col-sample--per-test-stack');
            $sample.find('.ls-quote-per-test-band, .ls-quote-per-test-sample-stack').remove();
            var safeName = escapeQuoteHtml(sampleTypeName || 'Sample');
            var manyTests = testCount > 6;
            $sample.prepend(
                $('<div class="ls-quote-per-test-sample-stack"></div>').html(
                    '<div class="ls-quote-per-test-sample-stack__name" title="' + safeName + '">' + safeName + '</div>'
                    + '<div class="ls-quote-per-test-band">'
                    + '<i class="mdi mdi-view-list-outline"></i> Per parameter · '
                    + testCount + ' test' + (testCount === 1 ? '' : 's')
                    + '</div>'
                    + (manyTests
                        ? '<details class="ls-quote-per-test-tests-drop">'
                            + '<summary>Browse ' + testCount + ' tests</summary>'
                            + '<div class="ls-quote-per-test-tests-drop__list" data-per-test-list="' + escapeQuoteHtml(groupId) + '"></div>'
                            + '</details>'
                        : '')
                )
            );
            // Keep mode toggle visible on the lead so users can revert to package.
            $sample.find('.ls-quote-line-mode-wrap').removeClass('d-none').css('display', '');
        }

        function markPerTestContinuation($row, groupId, sampleTypeName) {
            clearPerTestVisuals($row);
            $row.addClass('ls-quote-line--per-test ls-quote-line--per-test-cont');
            $row.attr('data-per-test-group', groupId);
            $row.find('.ls-quote-line-mode-wrap').addClass('d-none');
            // Keep sample_type[] for submit, but hide the cell visually (merged into lead via rowspan CSS/JS).
            $row.find('.ls-quote-per-test-cont-label').remove();
        }

        function syncPerTestSampleTypeMerge(groupId) {
            if (!groupId) {
                return;
            }
            var $rows = $('#create-detail > tr[data-per-test-group="' + groupId + '"]');
            if (!$rows.length) {
                return;
            }
            var $lead = $rows.filter('.ls-quote-line--per-test-lead').first();
            if (!$lead.length) {
                $lead = $rows.first().addClass('ls-quote-line--per-test-lead').removeClass('ls-quote-line--per-test-cont');
            }
            var sampleTypeId = String($lead.find('select[name="sample_type[]"]').val() || '');
            var $leadSample = $lead.find('td.ls-quote-col-sample').first();
            if (!$leadSample.length) {
                return;
            }
            $leadSample.attr('rowspan', $rows.length);
            $leadSample
                .removeClass('ls-quote-col-sample--merged-away')
                .addClass('ls-quote-col-sample--per-test-stack')
                .css({ display: '', verticalAlign: 'top' });

            // Populate browse-tests dropdown when many tests.
            var $list = $leadSample.find('.ls-quote-per-test-tests-drop__list');
            if ($list.length) {
                var items = [];
                $rows.each(function (idx) {
                    var label = $(this).find('.ls-quote-param-chip').first().clone().children().remove().end().text().trim();
                    if (!label) {
                        label = 'Test ' + (idx + 1);
                    }
                    items.push('<div class="ls-quote-per-test-tests-drop__item">' + escapeQuoteHtml(label) + '</div>');
                });
                $list.html(items.join(''));
            }

            $rows.each(function () {
                var $tr = $(this);
                if ($tr[0] === $lead[0]) {
                    return;
                }
                var $sample = $tr.find('td.ls-quote-col-sample').first();
                var val = sampleTypeId;
                if ($sample.length) {
                    val = String($sample.find('select[name="sample_type[]"]').val() || sampleTypeId);
                    $sample.remove();
                }
                var $host = $tr.find('td.quote-description-cell, #quote-description').first();
                if ($host.length) {
                    $host.find('input.ls-quote-per-test-sample-type').remove();
                    $host.prepend(
                        $('<input type="hidden" class="ls-quote-per-test-sample-type" name="sample_type[]">').val(val)
                    );
                }
            });
        }

        function cloneBlankAnalysisLine(rowNo) {
            var $row = $('#ls-quote-analysis-line-prototype').clone(false, false);
            $row.attr('id', 'detail-row-' + rowNo);
            $row.find('.ls-quote-line-no').text(rowNo);
            $row.find('.quote-description-cell').attr('id', 'quote-description-' + rowNo);
            $row.find('.select-part-final').attr('id', 'select-part-final-' + rowNo).val('');
            $row.find('select.select-sample-type').attr('id', 'select-sample-type-' + rowNo).val('');
            $row.find('.select2-container').remove();
            $row.find('select').removeClass('select2-hidden-accessible').removeAttr('data-select2-id').removeAttr('aria-hidden').removeAttr('tabindex');
            $row.find('select option').removeAttr('data-select2-id');
            $row.find('input, select, textarea, button').prop('disabled', false);
            $row.find('input[data-ls-quote-required="1"]').prop('required', true);
            $row.find('.ls-quote-line-select').prop('disabled', false).val('');
            applyManualLineModeUi($row);
            applyDefaultTaxToRow($row);
            initLsSampleTypeSelect($row.find('select.select-sample-type'));

            $row.find('.js-delete-line').on('click', function () {
                if (confirm('Are you sure you want to delete this row?')) {
                    var $tr = $(this).closest('tr');
                    var groupId = String($tr.attr('data-per-test-group') || '');
                    $tr.remove();
                    if (groupId) {
                        var $remaining = $('#create-detail > tr[data-per-test-group="' + groupId + '"]');
                        if ($remaining.length) {
                            if (!$remaining.filter('.ls-quote-line--per-test-lead').length) {
                                var $next = $remaining.first();
                                $next.removeClass('ls-quote-line--per-test-cont').addClass('ls-quote-line--per-test-lead');
                                // Restore a sample-type cell if it was merged away.
                                if (!$next.find('td.ls-quote-col-sample').length) {
                                    var $protoSample = $('#ls-quote-analysis-line-prototype td.ls-quote-col-sample').clone(false, false);
                                    $protoSample.find('select').attr('id', 'select-sample-type-' + String($next.attr('id') || '').replace('detail-row-', ''));
                                    $next.find('td.ls-quote-line-actions').after($protoSample);
                                    initLsSampleTypeSelect($protoSample.find('select.select-sample-type'));
                                    var st = String($next.find('input.ls-quote-per-test-sample-type').val() || '');
                                    if (st) {
                                        $protoSample.find('select.select-sample-type').val(st).trigger('change.select2');
                                    }
                                }
                            }
                            syncPerTestSampleTypeMerge(groupId);
                        }
                    }
                    renumberQuoteAnalysisLines();
                }
            });

            $row.find('select.select-sample-type').on('change', function () {
                if ($row.data('lsSkipSampleWipe')) {
                    $row.removeData('lsSkipSampleWipe');
                    return;
                }
                $(this).addClass('choose');
                var sample_type = $(this).val();
                var sample_code = $(this).find('option:selected').text() || '';
                var thisRowNo = String($row.attr('id') || '').replace('detail-row-', '');
                if (sample_type != '') {
                    $row.find('input.select-part-final').val('');
                    $row.find('.quote-description-cell').first().empty().append(
                        $(buildParamsPanelHtml(sample_code, thisRowNo, sample_type, sample_code, '', 'mdi-eye-outline'))
                    );
                    $row.find('.quotation-unit-price').val(0);
                    applyDefaultTaxToRow($row);
                    $row.removeData('pricelistSuggestion');
                    clearPerTestVisuals($row);
                    var rowMode = applyManualLineModeUi($row);
                    $row.find('.quotation-price-hint').text(
                        rowMode === 'per_test'
                            ? 'Open Parameters to select tests (each test billed separately).'
                            : 'Open Parameters to select tests (package billed as qty × unit price once).'
                    );
                } else {
                    clearRowParameterFields($row);
                    clearPerTestVisuals($row);
                }
            });

            return $row;
        }

        function applyElementPriceToRow($row, elementId, elementPrices) {
            var priceInfo = elementPrices && elementId ? elementPrices[elementId] : null;
            if (!priceInfo) {
                $row.find('.quotation-price-hint').text('Enter unit price for this test.');
                updateQuoteLineTotal($row);
                return;
            }
            applyPricelistSuggestionToRow($row, {
                unit_price: priceInfo.unit_price,
                tax: priceInfo.tax,
                hint: 'Per-test pricelist price.',
                is_package: false,
                pricing_mode: 'per_test',
            });
        }

        function explodeRowIntoPerTestLines($sourceRow, state) {
            var tests = (state && state.tests) ? state.tests.slice() : [];
            if (tests.length === 0) {
                return;
            }

            var sampleTypeId = $sourceRow.find('select[name="sample_type[]"]').val() || '';
            var sampleTypeName = $sourceRow.find('select[name="sample_type[]"] option:selected').text() || '';
            var qty = $sourceRow.find('.quotation-qty, input[name="quantity[]"]').val();
            var qtyReq = $sourceRow.find('.quotation-qty-required, input[name="quantity_required[]"]').val() || '';
            var groupId = String($sourceRow.attr('data-per-test-group') || '') || newPerTestGroupId();

            removePerTestGroupSiblings($sourceRow, groupId);

            writeSingleTestParameterFields($sourceRow, tests[0], sampleTypeId, sampleTypeName);
            $sourceRow.find('.quotation-qty, input[name="quantity[]"]').val(qty);
            $sourceRow.find('.quotation-qty-required, input[name="quantity_required[]"]').val(qtyReq);
            markPerTestLead($sourceRow, groupId, tests.length, sampleTypeName);

            var $insertAfter = $sourceRow;
            var createdRows = [$sourceRow];

            for (var i = 1; i < tests.length; i++) {
                var rowNo = nextDetailRowNo();
                var $new = cloneBlankAnalysisLine(rowNo);
                $new.data('lsSkipSampleWipe', 1);
                $new.find('select.select-sample-type').val(sampleTypeId).trigger('change');
                writeSingleTestParameterFields($new, tests[i], sampleTypeId, sampleTypeName);
                $new.find('.quotation-qty, input[name="quantity[]"]').val(qty);
                $new.find('.quotation-qty-required, input[name="quantity_required[]"]').val(qtyReq);
                markPerTestContinuation($new, groupId, sampleTypeName);
                $insertAfter.after($new);
                $insertAfter = $new;
                createdRows.push($new);
            }

            renumberQuoteAnalysisLines();
            syncPerTestSampleTypeMerge(groupId);

            var allIds = tests.map(function (t) { return t.id; }).filter(Boolean);
            var analysisCsv = (state.analysisTypeIds || []).join(',');
            $.post(quotationPricingConfig.suggestUrl, {
                _token: quotationPricingConfig.csrf,
                sample_type_id: sampleTypeId,
                analysis_type_ids: analysisCsv,
                element_ids: allIds.join(','),
                pricing_mode: 'per_test',
            }).done(function (data) {
                var elementPrices = (data && data.element_prices) ? data.element_prices : {};
                createdRows.forEach(function ($r, idx) {
                    var test = tests[idx];
                    applyElementPriceToRow($r, test ? test.id : '', elementPrices);
                });
            }).fail(function () {
                createdRows.forEach(function ($r) {
                    $r.find('.quotation-price-hint').text('Could not load per-test prices — enter manually.');
                    updateQuoteLineTotal($r);
                });
            });
        }

        function writePackageParameterFields($row, state, sampleTypeId, sampleTypeName) {
            var rowNo = String($row.attr('id') || '').replace('detail-row-', '');
            var $desc = $row.find('.quote-description-cell').first();
            $desc.attr('id', 'quote-description-' + rowNo);
            $desc.empty().removeClass('text-center');
            clearPerTestVisuals($row);
            $desc.append(buildParamsPanelHtml(sampleTypeName, rowNo, sampleTypeId, sampleTypeName, state.spans.join(''), 'mdi-pencil'));
            $desc.append($('<input type="hidden" name="accreditted_analytes[]">').val(state.analytes_accreditted.toString()));
            $desc.append($('<input type="hidden" name="sub_analytes[]">').val(state.analyte_sub.toString()));
            $desc.append($('<input type="hidden" name="sub_acc[]">').val(state.sub_acc.toString()));
            $desc.append($('<input type="hidden" name="default_analytes[]">').val(state.default_analytes.toString()));
            $desc.append($('<input type="hidden" name="element_loq_json[]">').val(JSON.stringify(state.loqMap)));
            $row.find('input.select-part-final, input[name="part_number_final[]"]').val((state.analysisTypeIds || []).join(','));
            $row.find('.quotation-pricing-mode').val('per_package');
            $row.find('.ls-quote-line-mode-input').prop('checked', false);
            refreshQuotationRowPricingHint($row, true);
        }

        $('#save-analytes').off('click').on('click', function(e) {
            e.preventDefault();
            var row_no = quoteDescriptionModalRowNo;
            var sample_code = quoteDescriptionModalSampleCode;
            var type_name = quoteDescriptionModalTypeName;
            if (!row_no) {
                return;
            }

            var $modal = $('#quote-description-analytes');
            persistEditedLoqs($modal).always(function() {
                var state = collectSelectedAnalyteState($modal);
                var selectedCount = (state.tests && state.tests.length)
                    ? state.tests.length
                    : (state.analytes_accreditted.length
                        + state.analyte_sub.length
                        + state.sub_acc.length
                        + state.default_analytes.length);
                if (selectedCount === 0) {
                    if (typeof window.showImaraToast === 'function') {
                        window.showImaraToast({
                            type: 'warning',
                            title: 'Select tests',
                            message: 'Select at least one test (left checkbox / Select all) before saving parameters.',
                            variant: 'pulse',
                        });
                    }
                    return;
                }

                var $row = $('#create-detail').find('tr#detail-row-' + row_no);
                if (!$row.length) {
                    return;
                }

                var mode = rowPricingMode($row);
                if (mode === 'per_test') {
                    explodeRowIntoPerTestLines($row, state);
                } else {
                    var existingGroup = String($row.attr('data-per-test-group') || '');
                    if (existingGroup) {
                        removePerTestGroupSiblings($row, existingGroup);
                    }
                    writePackageParameterFields($row, state, sample_code, type_name);
                    renumberQuoteAnalysisLines();
                }
                $('#quote-description-analytes').modal('hide');
            });
        });

        var quoteAnalytesRow = function(data) {
            var check_acc = '';
            var check_sub = '';
            // Leave unchecked by default; loadQuoteParametersModal applies package/per-test selection state.
            var selected = '';

            // Element is accredited unless explicitly marked non-accredited.
            if (!(parseInt(data.non_accredited, 10) === 1 || data.non_accredited === true || data.non_accredited === 'true')) {
                check_acc = 'checked';
            }

            if (data.present == 1) {
                check_acc = data.acc == 1 || data.both == 1 ? 'checked' : '';
                check_sub = data.sub == 1 || data.both == 1 ? 'checked' : '';
                selected = data.selected == 0 ? '' : 'checked';
            } else {
                if (data.sub == 1) {
                    check_sub = 'checked';
                }
                if (data.both == 1) {
                    check_acc = 'checked';
                    check_sub = 'checked';
                }
                if (data.selected == 0) {
                    selected = '';
                }
            }

            var loqVal = (data.loq !== null && typeof data.loq !== 'undefined') ? data.loq : '';
            var muVal = (data.mu_percent !== null && typeof data.mu_percent !== 'undefined') ? data.mu_percent : '';
            var reportingTime = (data.reporting_time !== null && typeof data.reporting_time !== 'undefined' && data.reporting_time !== '')
                ? data.reporting_time
                : '';
            var tatDisplay = reportingTime !== '' ? reportingTime : '—';
            var elementId = data.id || '';
            var analyteLabel = escapeQuoteHtml(data.analyte_name);
            var analysisTypeId = escapeQuoteHtml(data.analysis_type_id || '');
            var labSection = String(data.lab_section_name || '').trim();
            var methodLabel = String(data.method_label || data.test_method || '').trim();
            var isAccredited = check_acc === 'checked';
            var metaPills = '';
            if (labSection) {
                metaPills += '<span class="ls-quote-meta-pill ls-quote-meta-pill--lab" title="Lab section (sample analysis stage)">' + escapeQuoteHtml(labSection) + '</span>';
            }
            if (methodLabel) {
                var methodPillClass = 'ls-quote-meta-pill ls-quote-meta-pill--method'
                    + (isAccredited ? '' : ' is-non-accredited');
                var methodTitle = isAccredited ? 'Method (accredited)' : 'Method (not accredited)';
                metaPills += '<span class="' + methodPillClass + '" title="' + methodTitle + '">' + escapeQuoteHtml(methodLabel) + '</span>';
            }

            return $(`
                <tr class="ls-quote-analyte-row" data-reporting-time="${reportingTime}" data-element-id="${elementId}" data-analysis-type-id="${analysisTypeId}">
                    <td class="ls-quote-analyte-row__select">
                        <label class="ls-quote-check">
                            <input type="checkbox" class="analyte-selected" name="selected_analyte[]" value="${elementId}" ${selected}>
                            <span class="ls-quote-check__box" aria-hidden="true"></span>
                        </label>
                    </td>
                    <td class="ls-quote-analyte-row__name">
                        <div class="ls-quote-analyte-row__name-inner">
                            <span class="ls-quote-analyte-row__label">${analyteLabel}</span>
                            <span class="ls-quote-analyte-row__meta">${metaPills}</span>
                        </div>
                        <input type="hidden" name="analyte_name[]" class="analyte-name" value="${analyteLabel}">
                        <input type="hidden" name="analyte_id[]" class="analyte-analyte-id" value="${data.analyte_id}">
                    </td>
                    <td class="ls-quote-analyte-row__flag">
                        <label class="ls-quote-check ls-quote-check--acc">
                            <input type="checkbox" class="analyte-accredited" name="accreditted" ${check_acc}>
                            <span class="ls-quote-check__box" aria-hidden="true"></span>
                        </label>
                    </td>
                    <td class="ls-quote-analyte-row__flag">
                        <label class="ls-quote-check ls-quote-check--sub">
                            <input type="checkbox" class="analyte-subcontracted" name="sub_contracted" ${check_sub}>
                            <span class="ls-quote-check__box" aria-hidden="true"></span>
                        </label>
                    </td>
                    <td class="ls-quote-analyte-row__loq">
                        <input type="text" class="ls-quote-analyte-loq analyte-loq" name="analyte_loq[]" value="${escapeQuoteHtml(loqVal)}" data-original-loq="${escapeQuoteHtml(loqVal)}" data-element-id="${elementId}" placeholder="—">
                    </td>
                    <td class="ls-quote-analyte-row__mu">
                        <span class="ls-quote-metric">${muVal !== '' ? escapeQuoteHtml(muVal) : '—'}</span>
                    </td>
                    <td class="ls-quote-analyte-row__tat" title="Element TAT (days); line uses the highest selected">
                        <span class="ls-quote-tat">${tatDisplay}${reportingTime !== '' ? 'd' : ''}</span>
                    </td>
                </tr>
            `);
        }
        function syncMethodPillAccreditation($row) {
            if (!$row || !$row.length) {
                return;
            }
            var $pill = $row.find('.ls-quote-meta-pill--method');
            if (!$pill.length) {
                return;
            }
            var accredited = !!$row.find('.analyte-accredited').prop('checked');
            $pill.toggleClass('is-non-accredited', !accredited);
            $pill.attr('title', accredited ? 'Method (accredited)' : 'Method (not accredited)');
        }

        // Acc/Sub / select toggles must not thrash stage visibility (was blanking the list).
        $(document).on('change', '#quote-description-analytes .analyte-accredited, #edit-detail-analytes .analyte-accredited', function(e) {
            e.stopPropagation();
            syncMethodPillAccreditation($(this).closest('tr.ls-quote-analyte-row'));
        });

        $(document).on('change click', '#quote-description-analytes .analyte-selected, #edit-detail-analytes .analyte-selected', function(e) {
            e.stopPropagation();
        });

        $(document).on('click', '#quote-description-analytes .ls-quote-check--acc, #edit-detail-analytes .ls-quote-check--acc', function(e) {
            e.stopPropagation();
        });

        $('#select-analyte-all').on('change', function() {
            $('#quote-description-analytes').find('.analyte-selected').prop('checked', $(this).is(':checked'));
        });
        $('#select-accredited-all').on('change', function() {
            var checked = $(this).is(':checked');
            $('#quote-description-analytes').find('.analyte-accredited').prop('checked', checked).each(function() {
                syncMethodPillAccreditation($(this).closest('tr'));
            });
        });
        $('#select-sub-all').on('change', function() {
            $('#quote-description-analytes').find('.analyte-subcontracted').prop('checked', $(this).is(':checked'));
        });
        $('#edit-sub').on('change', function() {
            $('#edit-detail-analytes').find('.analyte-subcontracted').prop('checked', $(this).is(':checked'));
        });
        $('#edit-accreditted').on('change', function() {
            var checked = $(this).is(':checked');
            $('#edit-detail-analytes').find('.analyte-accredited').prop('checked', checked).each(function() {
                syncMethodPillAccreditation($(this).closest('tr'));
            });
        });
        $('#edit-analyte-all').on('change', function() {
            $('#edit-detail-analytes').find('.analyte-selected').prop('checked', $(this).is(':checked'));
        });


    });

    function getCustomer() {
        var customer = document.getElementsByName('client')[0].value;

        var text = document.getElementById(customer);
        var text2 = document.getElementsByClassName('contacts')
        for (i = 0; i < text2.length; i++) {
            text2[i].style.display = 'none';
        }

        text.style.display = "block";
    }


    function getPricelist(index) {

        var count = index - 1;
        var analyte = document.getElementsByName('analyte[]')[count].value;
        var my = JSON.parse(analyte);
        var sample_type = document.getElementsByName('sample_type[]')[count].value = my.sample_type_name;
        var tax = document.getElementsByName('tax[]')[count].value = my.tax;
        var unit_price = document.getElementsByName('unit_price[]')[count].value = my.selling_price;
        var analyte_id = document.getElementsByName('analysis_id[]')[count].value = my.analysis_id;




    }

    function getCustomer() {
        var customer = document.getElementsByName('client')[0].value;

        var text = document.getElementById(customer);
        var text2 = document.getElementsByClassName('contacts')
        for (i = 0; i < text2.length; i++) {

            $(text2[i]).addClass('hidden');
        }


        $(text).removeClass('hidden');
    }

    function deleterow(index) {
        var detail = '.detail-' + index;
        var row = $(detail).parent('tr');

        if (confirm("Are you sure you want to delete this row?")) {
            $(row).remove();
        }
    }

    function getPricelistedit(indexed) {
        var counted = indexed - 1;
        var analyte = document.getElementsByName('analyte_edit[]')[counted].value;
        console.log(analyte);
        var my = JSON.parse(analyte);
        var sample_type = document.getElementsByName('sample_type_edit[]')[counted].value = my.sample_type_name;
        var tax = document.getElementsByName('tax_edit[]')[counted].value = my.tax;
        var unit_price = document.getElementsByName('unit_price_edit[]')[counted].value = my.selling_price;
        var analyte_id = document.getElementsByName('analysis_id_edit[]')[counted].value = my.analysis_id;

    }

    function addrow() {


    }

    function addrowgeneral() {

        var len = $('#part_number').length;

        var current = len + 1;
        // console.log(len2);
        var $row = $(`
        <tr>
                               <td class="text-center detail-${current}">
                               <i class="mdi mdi-minus-circle-outline text-danger btn " onclick="deleterow(${current})"></i>
                               </td> 
                               
                               <td>
                                   <div class="form-group">
                                       <input type="text" id="part_number" name="part_no[]" value="" class="form-control">
                                   </div>
                               </td>
                               <td >

                               <div class="form-group">
                                       
                                       <textarea  class="form-control"  name="item[]" required rows="1" ></textarea>  
                                   </div>
                                  
                               </td>
                               <td >
                                    
                                    <textarea  class="form-control" id="quotation-description"  name="description[]" required rows="2" ></textarea>  
                                    
                                </td>
                                <td nowrap>

                                    <div class="form-group id="">
                                        <input type="file" name="photo[]" value="" class="form-control"  id="">
                                    
                                    </div>
                                    
                                </td>
                               
                               <td>
                                   <div class="form-group">
                                       <input type="number" name="quantity[]" id="" class="form-control" value="" required>
                                   </div>
                               </td>
                               <td>
                                   
                                   <div class="form-group">
                                    <input type="number" name="unit_price[]" class="form-control" min="0" step="1" value="0" placeholder="0 = pricelist">
                                </div>
                                   
                               </td>
                               <td >
                                  
                                  <div class="form-group">
                                  <input type="number" name="tax[]" class="form-control quotation-tax" min="0" step="0.01" value="0" placeholder="0 = pricelist">
                                 
                                  
                                </div>
                                  
                               </td>
                            </tr>
        `).clone();
        tinymce.init({
            selector: $row.find('#quotation-description')
        });
        $('tbody').append($row);
    }

    (function () {
        var select = document.getElementById('quote-payments');
        var wrap = document.getElementById('quote-payments-custom-wrap');
        var custom = document.getElementById('quote-payments-custom');
        if (!select || !wrap || !custom) {
            return;
        }

        function syncPaymentsCustom(clearWhenHidden) {
            var option = select.options[select.selectedIndex];
            var allowsCustom = option && option.getAttribute('data-allows-custom') === '1';
            wrap.style.display = allowsCustom ? '' : 'none';
            custom.required = false;
            if (!allowsCustom && clearWhenHidden) {
                custom.value = '';
            }
        }

        select.addEventListener('change', function () {
            syncPaymentsCustom(true);
        });
        syncPaymentsCustom(false);
    })();
</script>


@endsection