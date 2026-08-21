@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])



@section('title2')
<title> Lab-Invoice </title>
<style type="text/css">
    .tab-card {
        border: 1px solid #eee;
    }

    .tab-card-header {
        background: none;
    }

    /* Default mode */
    .tab-card-header>.nav-tabs {
        border: none;
        margin: 0px;
    }

    .tab-card-header>.nav-tabs>li {
        margin-right: 2px;
    }

    .tab-card-header>.nav-tabs>li>a {
        border: 0;
        border-bottom: 2px solid transparent;
        margin-right: 0;
        color: #737373;
        padding: 2px 20px;
    }

    .tab-card-header>.nav-tabs>li>a.show {
        border-bottom: 2px solid #007bff;
        color: #007bff;
    }

    .tab-card-header>.nav-tabs>li>a:hover {
        color: #007bff;
    }

    .tab-card .nav-link.active {
        background-color: #dadccd !important;
        border: 1px solid #cccebf !important;
    }

    .tab-card-header>.tab-content {
        padding-bottom: 0;
    }

    .my-small-text {
        font-size: 13px !important;
    }

    .removeThis {
        z-index: 12;
        position: absolute;
        cursor: pointer;
        top: 0px;
        right: 2px;
        padding: 1px 4px;
        font-size: 12px;
        background-color: red;
        border-radius: 50%;
        color: #fff;
        box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.08);
    }

    .select2-selection {
        min-width: 200px !important;
    }

    .quotation-lines-toolbar {
        min-height: 38px;
        position: relative;
        z-index: 2;
    }
</style>
@endsection
@section('content2')
<main class="container-fluid workflow-board-page lab-panel-theme workflow-theme">
    @include('layouts.lab.partials.lab-panel-theme-styles')
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
    @include('layouts.lab.invoice.partials.quotation-preview-hover-styles')
    <h2 class="p-4 quotation-preview-hover-parent">
        <span class="float-left">
            <i class="mdi mdi-file-table"></i>Billing | Quotations
        </span>

        <div class="nav-item dropdown float-right" style="margin-top: 0px !important;">
            <a class="nav-link dropdown-toggle btn btn-sm btn-outline-secondary" href="#" id="quotationActionsDropdown" style="color:black;font-size:14px" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                <span class="dropdown-item text-dark" style="cursor: pointer;" data-target="#request-approval" data-toggle="modal"><i class="mdi mdi-share-circle"></i> Request For Approval</span>
                @endif
                @endif
                @if($header->status == 'Quote In Approval' && sizeof($details)>0 && (int) $header->is_approved !== 1 && (string) $header->approved_by === (string) auth()->id())
                <div class="dropdown-divider"></div>
                <span class="dropdown-item text-success" style="cursor: pointer;" data-target="#approve-quote" data-toggle="modal"><i class="mdi mdi-share-circle"></i> Approve Quotation</span>
                @endif
            </div>
        </div>
        @if($header->status == 'Quote In Approval')
            @if((int) $header->is_approved === 1)
                <span class="badge badge-pill ml-2 bg-white text-success p-2 float-right mt-1 {{ $header->approved_by < 0 ? 'hidden' : '' }}" style="font-size: 10px;"><i class="mdi mdi-thumb-up"></i> Approved</span>
            @else
                <span class="badge badge-pill bg-white ml-2 text-primary p-2 float-right mt-1" style="font-size: 10px;"><i class="mdi mdi-alert-decagram"></i> Awaiting Approval</span>
                @if((string) $header->approved_by !== (string) auth()->id())
                <span class="badge badge-pill bg-white ml-2 text-danger p-2 float-right mt-1" style="font-size: 10px;"><i class="mdi mdi-alert-decagram"></i> Required Approver — {{ getUserById($header->approved_by)->name ?? '-' }}</span>
                @endif
            @endif
        @endif
        <div class="nav-item dropdown float-right mr-2" style="margin-top: 0px !important;">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" style="color:black;font-size:14px" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="mdi mdi-compare-vertical"></i> Move To workflow
            </a>
            <div class="dropdown-menu" style="font-size: 13px;" aria-labelledby="navbarDropdown">
                <a class="dropdown-item" href="{{route('change_quotation_workflow',['id'=>$header->id,'stage'=>'Quote In Preparation'])}}"><i class="mdi mdi-subdirectory-arrow-right"></i> Quotation In Preparation</a>
                @if(in_array($header->status,['Quote In Approval','Quote Complete'], true))
                <a class="dropdown-item" href="{{route('change_quotation_workflow',['id'=>$header->id,'stage'=>'Quote In Approval'])}}"><i class="mdi mdi-subdirectory-arrow-right"></i> Quotation In Approval</a>
                <a class="dropdown-item" href="{{route('change_quotation_workflow',['id'=>$header->id,'stage'=>'Quote Complete'])}}"><i class="mdi mdi-subdirectory-arrow-right"></i> Quotation Complete</a>
                @endif

            </div>
        </div>
    </h2>
    <div class="card mt-5" style="clear: both;">

        <h3 class=" text-center card-header">
            <i class="mdi mdi-check-decagram mb-1" style="position: absolute;left:47.4%"></i><br> Quotation | {{$header->quote_number}}
            @if(($header->revision_number ?? 1) > 1)
                <span class="badge badge-info ml-1">Rev. {{ $header->revision_number }}</span>
            @endif
            @if($header->isSuperseded())
                <span class="badge badge-secondary ml-1">Superseded</span>
            @endif
        </h3>

        @if(($revisionFamily ?? collect())->count() > 1)
        <div class="card-body border-bottom py-2">
            <small class="text-muted d-block mb-1"><strong>Revision history</strong></small>
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
        <div class="card-body border-bottom py-2">
            <small class="text-muted d-block mb-1"><strong>Linked enquiries</strong></small>
            <ul class="mb-0 pl-3" style="font-size: 12px;">
                @foreach($linkedEnquiryEngagements as $engagement)
                    <li>
                        @if($engagement->enquiry)
                            <a href="{{ $engagement->enquiry->staffViewUrl() }}">{{ $engagement->enquiry->reference_number ?? $engagement->enquiry->id }}</a>
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

        <div class="card-body">

            <div class="row no-gutter">

                <div class="col-xl-4 col-sm-4">
                    <form action="{{route('add-quotation-header')}}" method="POST" class="bg-light ">
                        @csrf
                        <div class="card-body p-2">
                            <div class="form-group">
                                <label class="control-label">Quotation Number</label>
                                <input type="text" name="quote_code" readonly value="{{$header->quote_number}}" id="" class="form-control">
                                <input type="hidden" name="quote_id" value="{{$header->id}}">
                            </div>

                            <div class="form-group">
                                <label class="control-label">Client *</label>
                                <select name="client" class="form-control no-select2" id="select-client" data-contact="{{ $header->crm_customer_contact_id }}" data-zoho-customer-id="{{$header->customer->zoho_customer_id ?? ''}}" required>
                                    <option value="" disabled {{ empty($header->crm_customer_id) ? 'selected' : '' }}>Choose Client...</option>
                                    @foreach($customers as $customer)
                                    <option value="{{$customer->id}}" data-zoho-customer-id="{{$customer->zoho_customer_id}}" {{$customer->id == $header->crm_customer_id ? 'selected':''}}>{{$customer->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group contacts" id="choose-client">
                                <label class="control-label">Client Contact *</label>
                                <select name="client_contact" id="select-client-contact" class="form-control no-select2" required>
                                    @php
                                    $client_contacts = getQuotationCustomerContacts(
                                        (string) $header->crm_customer_id,
                                        $header->crm_customer_contact_id ? (string) $header->crm_customer_contact_id : null
                                    );
                                    @endphp
                                    @if($client_contacts->isEmpty())
                                    <option value="" disabled selected>No contacts available</option>
                                    @else
                                    @if(empty($header->crm_customer_contact_id))
                                    <option value="" disabled selected>Select Client Contact...</option>
                                    @endif
                                    @foreach($client_contacts as $contact)
                                    <option value="{{$contact->id}}" {{ (string) $contact->id === (string) $header->crm_customer_contact_id ? 'selected' : '' }}>{{ trim($contact->first_name . ' ' . ($contact->middle_name ?? '') . ' ' . $contact->last_name) }}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="control-label">Currency</label>
                                <input type="text" id="display-currency-edit" class="form-control" readonly placeholder="Set via Quote Currency..." value="{{$header->currency ? $header->currency->code . ' - ' . $header->currency->description : ''}}" style="background-color: #f5f5f5;">
                                <input type="hidden" name="currency_id" id="currency-id-edit" value="{{$header->currency_id ?? ''}}" required>
                            </div>

                            <div class="form-group">
                                <label class="control-label">Quotation Type</label>
                                <select name="quotation_type" required class="form-control no-select2">
                                    <option value="">Choose Quotation Type</option>
                                    <option value="General" {{$header->quotation_type == 'General' ? 'selected' : '' }}>General Quotation</option>
                                    <option value="Analysis" {{$header->quotation_type == 'Analysis' ? 'selected' : '' }}>Analysis Quotation</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Lab Section(s)</label>
                                @php
                                    $selectedLabSectionIds = $header->labSections->pluck('id')->map(fn ($id) => (string) $id)->all();
                                @endphp
                                <select name="lab_section_ids[]" class="form-control ls-select2" multiple data-placeholder="Select lab section(s)...">
                                    @foreach($labSections ?? [] as $section)
                                        <option value="{{ $section->id }}" @selected(in_array((string) $section->id, $selectedLabSectionIds, true))>
                                            {{ $section->name }}@if($section->code) ({{ $section->code }})@endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    Analysis lines can use any sample/analysis type; selectable <strong>tests/parameters</strong> are limited to the selected lab section(s).
                                </small>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Quotation Date *</label>
                                <input type="date" name="quotation_date" class="form-control" placeholder="Quotation Date..." value="{{$header->quote_date}}" required>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Expiry Date *</label>
                                <input type="date" name="expire_date" class="form-control" placeholder="Expiration Date..." value="{{$header->expiring_date}}" required>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Prepared By</label>
                                <input type="text" name="prepared_by" readonly value="{{$header->prepared_by_name}}" id="" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Laboratory Ref</label>
                                <input type="text" name="laboratory_ref" class="form-control" value="{{ $header->laboratory_ref }}" placeholder="Auto-generated if empty">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Subject</label>
                                <input type="text" name="subject" class="form-control" value="{{ $header->subject }}" placeholder="Quotation for ...">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Sampling Location</label>
                                <select name="sample_point_id" class="form-control no-select2">
                                    <option value="">Select sample point...</option>
                                    @foreach($samplePoints ?? [] as $point)
                                        <option value="{{ $point->id }}" {{ $header->sample_point_id == $point->id ? 'selected' : '' }}>{{ $point->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="card-footer text-center">
                            <button style="width:70%;" class="btn btn-outline-success" type="submit">Save</button>
                        </div>
                    </form>


                </div>
                <div class="col-xl-8 col-sm-8">
                    <form action="{{ route('add_quotation_detail',['id'=>$header->id]) }}" method="POST" enctype="multipart/form-data" class="bg-light p-1">
                        @csrf
                        @if($header->quotation_type == 'General')
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 quotation-lines-toolbar" style="gap: 8px;">
                            <button type="submit" class="btn btn-outline-success btn-sm">
                                Save <i class="mdi mdi-share-circle"></i>
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="addrowgeneral()">
                                <i class="mdi mdi-plus"></i> Add line
                            </button>
                        </div>
                        <div class="table-responsive">

                            <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm livewire-table" style="min-width: 180%;">
                                <thead class="bg-light">
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
                                <button type="submit" class="btn btn-outline-success btn-sm">
                                    Save <i class="mdi mdi-share-circle"></i>
                                </button>
                                @if($header->labSections->isNotEmpty())
                                    <span class="small text-muted">
                                        Parameters limited to lab section(s):
                                        {{ $header->labSections->pluck('name')->implode(', ') }}
                                    </span>
                                @endif
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
                            @if($header->status == 'Quote In Preparation')
                            <button type="button" class="btn btn-outline-info btn-sm" id="add-row">
                                <i class="mdi mdi-plus"></i> Add line
                            </button>
                            @endif
                        </div>
                        <div class="table-responsive quotation-analysis-lines">

                            <table class="table table-condensed table-striped table-hover table-bordered table-sm mb-0 livewire-table" style="width: 100%; min-width: 980px;">
                                <thead class="bg-light">

                                    <th style="width: 48px;">No</th>
                                    <th nowrap>Sample Type <span class="text-danger">*</span></th>
                                    <th nowrap>Analysis Type <span class="text-danger">*</span></th>
                                    <th nowrap>Parameters<span class="text-danger">*</span></th>
                                    <th nowrap style="width: 88px;">Quantity<span class="text-danger">*</span></th>
                                    <th nowrap style="width: 130px;">Unit Price</th>
                                    <th nowrap style="width: 90px;">Tax%</th>



                                </thead>
                                <tbody id="create-detail">
                                    @foreach($details as $detail)
                                    <tr>
                                        <td class="text-nowrap align-middle">

                                            <span style="font-size:11px;" data-sample="{{$detail->sample_type_name}}" data-detail={{$detail->id}} data-toggle="modal" data-target="#detail-edit-mode" class="btn btn-sm p-0 mdi mdi-pencil" data-toggle="tooltip" title="Edit"></span>

                                            <span style="font-size:12px;" data-toggle="modal" data-target="#delete-detail-{{$detail->id}}" class="btn btn-sm p-0 mdi mdi-delete-empty text-danger" data-toggle="tooltip" title="Delete"></span>


                                        </td>
                                        <td class="align-middle" style="min-width: 120px;">

                                            <div class="form-group mb-0" id="">
                                                <input type="text" class="form-control form-control-sm" value="{{$detail->sample_type_name}}" disabled id="">

                                            </div>

                                        </td>
                                        <td class="align-middle" style="min-width: 140px;">
                                            <span class="small">{{$detail->part_no_final}}</span>
                                        </td>
                                        <td class="align-middle" style="min-width: 220px; max-width: 320px;">
                                            <p class="mb-0 small"><b>{{$detail->sample_type_name}}</b></p>
                                            <p class="mb-1 small text-muted">Parameters:</p>
                                            <div class="small" style="white-space: normal;">
                                            @foreach($detail->default as $da)
                                            <span>{{$da}}, </span>
                                            @endforeach
                                            @foreach($detail->sub_acc as $sb)
                                            <span>{{$sb}}* <img src="/images/tick.png" height="8" width="8" alt="">, </span>
                                            @endforeach
                                            @foreach($detail->sub_analytes as $sa)
                                            <span>{{$sa}}*, </span>

                                            @endforeach
                                            @foreach($detail->acc_analytes as $acc)
                                            <span class="">{{$acc}} <img src="/images/tick.png" height="8" width="8" alt="">, </span>
                                            @endforeach
                                            </div>


                                        </td>

                                        <td class="align-middle">
                                            <div class="form-group mb-0">
                                                <input type="number" id="" class="form-control form-control-sm" value="{{$detail->quantity}}" disabled>
                                            </div>
                                        </td>
                                        <td class="align-middle text-right">

                                            <div class="form-group mb-0">
                                                <input type="text" class="form-control form-control-sm text-right" value="{{number_format($detail->unit_price,2)}}" disabled>
                                            </div>

                                        </td>
                                        <td class="align-middle">

                                            <div class="form-group mb-0">
                                                <input type="text" value="{{$detail->tax}}" readonly class="form-control form-control-sm" disabled>
                                            </div>


                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif

                        <h5 style="font-size: 11px; margin-top:15px"><b><u>Terms of Sale</u></b></h5>

                        <div class="terms ml-4">
                            <div class="form-group">
                                <label class="control-label">Quote Currency <span class="text-danger">*</span></label>
                                <select name="currency_id" class="form-control" required>
                                    <option value="">Choose Currency...</option>
                                    @foreach($currencies as $currency)
                                    <option value="{{ $currency->id }}" {{ $header->currency_id == $currency->id ? 'selected' : '' }}>{{ $currency->code }} - {{ $currency->description }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="control-label">Delivery of results</label>
                                <textarea class="form-control" rows="2" name="service_delivery" placeholder="Delivery of results...">{{ $termsOfSale['service_delivery'] ?? '' }}</textarea>
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
                            <div class="form-group">
                                <label class="control-label">Payments</label>
                                <select name="payments_account_id" id="quote-payments" class="form-control">
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
                            <div class="form-group" id="quote-payments-custom-wrap" style="{{ $showPaymentsCustom ? '' : 'display:none;' }}">
                                <label class="control-label">Custom payment terms</label>
                                <textarea class="form-control" rows="3" name="payments_custom" id="quote-payments-custom" placeholder="Enter custom payment terms for this quotation...">{{ $paymentsCustomValue }}</textarea>
                                <small class="text-muted">Shown when “Other” is selected. Saved on this quotation only.</small>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Proposal Acceptance</label>
                                <textarea class="form-control" rows="2" name="quote_specification" placeholder="Proposal Acceptance...">{{ $termsOfSale['quote_specification'] ?? '' }}</textarea>
                            </div>

                        </div>
                        <small style="font-size: 11px;"><b><u>Additional Information</u></b></small>

                        <div class="additional-info ml-4">
                            <div class="form-group">
                                <label class="control-label">Technical questions / Inquiries / Complaints</label>
                                <textarea class="form-control" rows="2" name="additional_info" placeholder="Technical questions / Inquiries / Complaints...">{{ $termsOfSale['additional_info'] ?? '' }}</textarea>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Information for Purchase Order / Sample Shipment</label>
                                <textarea class="form-control" rows="5" name="payment_info" placeholder="Information for Purchase Order / Sample Shipment...">{{ $termsOfSale['payment_info'] ?? '' }}</textarea>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Quotation T&amp;C Override <small class="text-muted">(optional — one term per line)</small></label>
                                <textarea class="form-control" rows="4" name="terms_override" placeholder="Leave blank to use system Quotation Terms and Conditions">{{ $header->terms_override }}</textarea>
                            </div>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>



</main>


<link rel="stylesheet" href="/css/quilljs.css" />
<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
@endsection
@section('script2')
<div class="modal fade" id="print-quotation" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="mdi mdi-printer"></i> Quote {{$header->quote_number}} PDF Proccessing </h5>
            </div>
            <div class="modal-body text-center">
                <div class="loading">
                    
                </div>
            </div>
            <div class="modal-footer">
                <a href="/billing-add-quote-detail-index/{{$header->id}}" class="btn  btn-sm btn-outline-danger">Close</a>
                
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="request-approval" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('approve-workflow')}}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-share-circle"></i> Request For Approval
                    </h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="header_id" value="{{$header->id}}">
                    <input type="hidden" name="stage" value="Quote In Approval">
                    <div class="form-group">
                        <label class="control-label">To Be Approved BY: <span class="text-danger">*</span> </label>
                        <select name="user_id" class="form-control" required>
                            <option value="">Select Approver...</option>
                            @foreach($users as $user)
                            @if($user->id != $header->prepared_by_id)
                            <option value="{{$user->id}}">{{$user->name}}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" class="form-control" name="notification" />
                        <label class="form-check-label">
                            Send Email Notification
                        </label>
                    </div>
                    <br>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" class="form-control" name="send_message" />
                        <label class="form-check-label">
                            Send Message
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-success"><i class="mdi mdi-content-save"></i> Approve</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="approve-quote" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('approve-workflow')}}" method="post">
            @csrf
                <div class="modal-body">
                    <input type="hidden" name="header_id" value="{{$header->id}}">
                    <input type="hidden" name="stage" value="Quote Complete">
                    @if($header->approved_by == Auth::user()->id)
                    <div class="alert alert-success">
                       <i class="mdi mdi-alert-decagram"></i> Notify {{getUserById($header->prepared_by_id)->name}} that you have approved Quote {{$header->quote_number}} by ?
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" class="form-control" name="notification" />
                        <label class="form-check-label">
                            Send Email Notification
                        </label>
                    </div>
                    <br>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" class="form-control" name="send_message" />
                        <label class="form-check-label">
                            Send Message
                        </label>
                    </div>
                    @else
                    <div class="alert alert-danger">
                        <i class="mdi mdi-alert-decagram"></i> You are not allowed to approve this quotation.
                    </div>
                    @endif
                    
                </div>
                <div class="modal-footer">
                    @if($header->approved_by == Auth::user()->id)
                    <button type="submit" class="btn btn-sm btn-outline-success"><i class="mdi mdi-content-save"></i> Approve</button>
                    @endif
                    <button type="button" class="btn btn-sm btn-outline-danger" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="delete-quotation" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('delete_quotation',['id'=>$header->id])}}" method="post">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title">

                        <i class="mdi mdi-delete-empty text-danger"></i> Delete Item {{$header->quote_number}}

                    </h4>
                </div>
                <div class="modal-body text-center">

                    <input type="hidden" name="header_id" value="{{$header->id}}" class="form-control">
                    Are you sure you want to delete Quotation {{$header->quote_number}}?
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-success"><i class="mdi mdi-content-save"></i> Yes</button>
                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="save-draft" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('save_draft',['id'=>$header->id])}}" method="post">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title"><i class="mdi mdi-download-outline text-warning"></i> Save As Draft Quotation {{$header->quote_number}}</h4>
                </div>
                <div class="modal-body">
                    <div class="card p-3" style="background-color: turquoise;">
                        Ensure you have saved all the details first before saving as draft.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i>Save</button>
                    <button type="button" class="btn btn-outline-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="detail-edit-mode" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{route('edit_quotation_detail')}}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="submit" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-content-save"></i>Save</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success text-center">
                        kindly wait for the page to load!
                    </div>
                </div>
                <div class="modal-footer">

                    <button type="button" class="btn btn-outline-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="edit-detail-analytes" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-light">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="mdi mdi-pencil text-primary"></i> Edit Quotation Parameters
                </h5>
                <button type="button" class="btn btn-success float-right btn-sm" id="save-edit"><i class="mdi mdi-content-save"></i> Save</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table style="width: 100%;" class="table-condensed table-hover table-stripped table-sm table-bordered">
                        <thead class="bg-light">
                            <th>No <input type="checkbox" class="float-right" id="edit-analyte-all"></th>
                            <th>Analyte Name</th>
                            <th>Accredited <input type="checkbox" id="edit-accreditted" class="float-right"></th>
                            <th>Sub-Contracted <input type="checkbox" id="edit-sub" class="float-right"></th>
                            <th nowrap>LOQ</th>
                            <th nowrap>MU%</th>
                            <th nowrap title="Turnaround time (days)">TAT</th>
                        </thead>
                        <tbody id="edit-description"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-default" data-dismiss="modal">Close</button>
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
<div class="modal fade" id="quote-description-analytes" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="mdi mdi-google-circles-extended text-success"></i> Quotation Parameters
                </h5>
                <button type="button" id="save-analytes" class="btn btn-outline-success btn-sm float-right"><i class="mdi mdi-content-save"></i> Save</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table-condensed table-hover table-stripped table-sm table-bordered" style="width: 100%;">
                        <thead class="bg-light">
                            <th>No <input type="checkbox" id="select-analyte-all" class="float-right"></th>
                            <th>Analyte </th>
                            <th>Accredited <input type="checkbox" id="select-accredited-all" class="float-right"></th>
                            <th>Subcontracted <input type="checkbox" id="select-sub-all" class="float-right"></th>
                            <th nowrap>LOQ</th>
                            <th nowrap>MU%</th>
                            <th nowrap title="Turnaround time (days)">TAT</th>
                        </thead>
                        <tbody id="analysis-analytes-holder">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var analysis = [];
    $(function() {
        var quotationPricingConfig = {
            suggestUrl: @json(route('quotation.suggest_line_pricing', $header->id)),
            packageDefaultsUrl: @json(route('quotation.package_defaults', $header->id)),
            updateLoqUrl: @json(route('quotation.update_element_loq')),
            quotationHeaderId: @json((string) $header->id),
            csrf: @json(csrf_token()),
        };

        function applyPricelistSuggestionToRow($row, data) {
            $row.data('pricelistSuggestion', data);
            $row.find('.quotation-price-hint').text(data.hint || '');
            if (data && typeof data.unit_price !== 'undefined') {
                $row.find('.quotation-unit-price').val(data.unit_price);
            }
            if (data && typeof data.tax !== 'undefined') {
                $row.find('.quotation-tax').val(data.tax);
                $row.find('.quotation-tax-display').text(
                    parseFloat(data.tax) > 0 ? parseFloat(data.tax).toFixed(2) + '%' : '0%'
                );
            }
        }

        function clearRowParameterFields($row) {
            var $desc = $row.find('#quote-description');
            $desc.find('input[name="accreditted_analytes[]"], input[name="sub_analytes[]"], input[name="sub_acc[]"], input[name="default_analytes[]"], input[name="element_loq_json[]"]').remove();
            $row.find('.quotation-unit-price').val(0);
            $row.find('.quotation-tax').val(0);
            $row.find('.quotation-tax-display').text('0%');
            $row.removeData('pricelistSuggestion');
            $row.find('.quotation-price-hint').text('Pick tests in Parameters to load pricelist total.');
        }

        function writePackageDefaultsToRow($row, data, sampleTypeId, sampleTypeName) {
            var rowNo = $row.attr('id').replace('detail-row-', '');
            var elementIds = data.element_ids || [];
            var accreditedIds = data.accredited_ids || [];
            var defaultIds = data.default_ids || [];
            var parameters = data.parameters || [];
            var $desc = $row.find('#quote-description');
            $desc.empty().removeClass('text-center');

            if (!defaultIds.length && elementIds.length) {
                var accreditedSet = {};
                accreditedIds.forEach(function(id) { accreditedSet[String(id)] = true; });
                defaultIds = elementIds.filter(function(id) { return !accreditedSet[String(id)]; });
            }

            var header = $(`
                <p class="mb-0"><b>${sampleTypeName || 'Parameters'}</b>
                <span data-target="#quote-description-analytes" data-row="${rowNo}" data-samplecode="${sampleTypeName || ''}" data-toggle="modal" data-sampletype="${sampleTypeId}" class="btn btn-outline-success btn-sm mdi mdi-pencil float-right"></span>
                </p>
                <p class="mb-0"><b>Parameters:</b></p>
            `);
            $desc.append(header);

            if (parameters.length) {
                parameters.forEach(function(param) {
                    var label = param.label || param.id || '';
                    if (param.accredited) {
                        $desc.append($(`<span>${label} <img src="/images/tick.png" alt="tick" height="8" width="8">, </span>`));
                    } else {
                        $desc.append($(`<span>${label}, </span>`));
                    }
                });
            } else {
                $desc.append($('<span class="text-muted">No parameters</span>'));
            }

            $desc.append($('<input type="hidden" name="accreditted_analytes[]">').val(accreditedIds.toString()));
            $desc.append($('<input type="hidden" name="sub_analytes[]">').val(''));
            $desc.append($('<input type="hidden" name="sub_acc[]">').val(''));
            $desc.append($('<input type="hidden" name="default_analytes[]">').val(defaultIds.toString()));
            $desc.append($('<input type="hidden" name="element_loq_json[]">').val('{}'));

            applyPricelistSuggestionToRow($row, data);
        }

        function refreshPackageDefaultsForRow($row) {
            var sampleTypeId = $row.find('select[name="sample_type[]"]').val() || '';
            var analysisTypeIds = $row.find('input#select-part-final, input[name="part_number_final[]"]').val() || '';
            var sampleTypeName = $row.find('select[name="sample_type[]"] option:selected').text() || '';
            var $hint = $row.find('.quotation-price-hint');

            if (!sampleTypeId || !analysisTypeIds) {
                clearRowParameterFields($row);
                $hint.text('Select sample type and analysis type.');
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
                var $desc = $row.find('#quote-description');
                var hasManualParams = $desc.find('input[name="default_analytes[]"]').length > 0
                    && ($desc.find('input[name="default_analytes[]"]').val() || '').length > 0;

                if (!hasManualParams) {
                    $desc.empty().removeClass('text-center');
                    $desc.append($(
                        `<span data-target="#quote-description-analytes" data-row="${rowNo}" data-samplecode="${sampleTypeName}" data-toggle="modal" data-sampletype="${sampleTypeId}" class="btn btn-outline-success btn-sm mdi mdi-eye"></span>`
                    ));
                    $row.find('.quotation-unit-price').val(0);
                    $row.find('.quotation-tax').val(0);
                    $row.find('.quotation-tax-display').text('0%');
                    $row.removeData('pricelistSuggestion');
                    $hint.text(data && data.hint ? data.hint : 'Pick tests in Parameters to load pricelist total.');
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
            var analysisTypeIds = $row.find('input#select-part-final, input[name="part_number_final[]"]').val() || '';
            var elementIds = collectElementIdsFromRow($row);
            var $hint = $row.find('.quotation-price-hint');

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

        $(document).on('click', '.quotation-apply-pricelist', function() {
            var $row = $(this).closest('tr');
            var data = $row.data('pricelistSuggestion');
            if (data && parseFloat(data.unit_price) > 0) {
                applyPricelistSuggestionToRow($row, data);
                return;
            }
            refreshQuotationRowPricingHint($row, true);
        });

        $('select[name="currency_id"]').on('change', function() {
            var selected = $(this).find('option:selected');
            var currencyId = $(this).val();
            var label = selected.text();
            $('#currency-id-edit').val(currencyId || '');
            $('#display-currency-edit').val(currencyId ? label : '');
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

        $('#select-client').on('change', function() {
            var client = $(this).val();
            var contact = $(this).data('contact');
            var $selectedOption = $(this).find('option:selected');
            var zohoCustomerId = $selectedOption.data('zoho-customer-id');
            var $contactSelect = $('#select-client-contact');

            $.ajax({
                url: '/fetch-customer-contacts/' + client + (contact ? '?assigned=' + encodeURIComponent(contact) : ''),
                beforeSend: function() {
                    $contactSelect.empty();
                },
                success: function(data) {
                    if (!data || data.length === 0) {
                        $contactSelect.append('<option value="" disabled selected>No contacts available</option>');
                        return;
                    }

                    var hasSelected = false;
                    $.each(data, function(j, s) {
                        var label = [s.first_name, s.middle_name, s.last_name].filter(Boolean).join(' ').trim();
                        var isSelected = contact && String(s.id) === String(contact);
                        if (isSelected) {
                            hasSelected = true;
                        }
                        $contactSelect.append(
                            $('<option></option>')
                                .val(s.id)
                                .text(label || 'Contact')
                                .prop('selected', isSelected)
                        );
                    });

                    if (!hasSelected) {
                        $contactSelect.prepend('<option value="" disabled selected>Select Client Contact...</option>');
                    }
                },
                error: function() {
                    $contactSelect.append('<option value="" disabled selected>Unable to load contacts</option>');
                }
            });
            
            // Pre-select Dynamics customer if linked
            if(zohoCustomerId) {
                $('#select-zoho-customer-edit').val(zohoCustomerId).trigger('change');
            } else {
                $('#select-zoho-customer-edit').val('');
                $('#display-currency-edit').val('');
                $('#currency-id-edit').val('');
            }
        });

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
                            $('#currency-id-edit').val(currency.id);
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

            // console.log(len2);

            var roeNo = $('#create-detail').find('tr').length + 1;

            var $row = $(`
                <tr id="detail-row-${roeNo}">
                                    <td class="text-center ">${roeNo}
                                    <span class="mdi mdi-minus-circle-outline text-danger btn " id="delete-row"></span>
                                    </td> 
                                    <td>      
                                        <div class="form-group">
                                            <select name="sample_type[]" class="form-control" id ="select-sample-type">
                                                <option value ="">Choose Sample Type</option>
                                                @foreach($sample_types as $type)
                                                <option value="{{$type->id}}">{{$type->name}}</option>
                                                @endforeach
                                            </select> 
                                        </div> 
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <select name="part_number[]" class="form-control select2 select-part" multiple required>
                                                
                                            </select>
                                            <input type="hidden" class="form-control" value="" name ="part_number_final[]" id="select-part-final">
                                        </div>
                                    </td>
                                    <td class="text-center" id="quote-description" >
                                        <span>-</span>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <input type="number" name="quantity[]" id="" class="form-control" value="" required>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group mb-1">
                                            <div class="input-group input-group-sm">
                                                <input type="number" name="unit_price[]" class="form-control quotation-unit-price" min="0" step="0.01" value="0" placeholder="0 = pricelist">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-outline-secondary quotation-apply-pricelist" title="Apply pricelist suggestion">$</button>
                                                </div>
                                            </div>
                                            <small class="text-muted quotation-price-hint d-block"></small>
                                        </div>
                                    </td>
                                    <td >
                                        <div class="form-group mb-0">
                                            <input type="hidden" name="tax[]" class="quotation-tax" value="0">
                                            <span class="form-control-plaintext quotation-tax-display px-2">0%</span>
                                        </div>
                                    </td>
                                    </tr>
                `).clone();
            $row.find('#delete-row').on('click', function() {
                if (confirm("Are you sure you want to delete this row?")) {
                    var row = $(this).parents('tr');
                    $(row).remove();
                }
            });


            $row.find('#select-sample-type').on('change', function(analysis) {
                $(this).addClass('choose');
                var sample_type = $(this).val();
                if (sample_type != '') {

                    $.ajax({
                        url: '/fetch-sample-type/' + sample_type + '?quotation_header_id=' + encodeURIComponent(quotationPricingConfig.quotationHeaderId),
                        beforeSend: function() {
                            $('#part-number-test').empty();
                            analysis = [];
                        },
                        success: function(data) {
                            analysis = data['analysis']

                            quotationDetailRow(data['analysis'], sample_type, roeNo, data['sample_type']);
                            $('#create-detail').find('tr#detail-row-' + roeNo).find('select.select-part').select2();
                        },
                        error: function(data) {
                            console.log(data);
                        }
                    })
                }


            });
            $row.find('select.select-part').on('change', function() {
                var value = $(this).val();
                $row.find('input#select-part-final').val(value ? value.toString() : '');
                refreshPackageDefaultsForRow($row);
            });

            $('#create-detail').append($row);

        });

        var quotationDetailRow = function(data, sample_type, rowNo, sample_code) {
            var $partSelect = $('#create-detail').find('tr#detail-row-' + rowNo).find('select.select-part');
            $partSelect.empty();
            $.each(data, function(i, e) {
                $partSelect.append(`<option value ="${e.id}">${e.name}</option>`);
            });
            // Leave analysis type unselected so the analyst chooses; package defaults run on change.
            $partSelect.val(null).trigger('change.select2');
            $('#create-detail').find('tr#detail-row-' + rowNo).find('input#select-part-final').val('');
            $partSelect.select2();

            var text = $(`
                <span data-target="#quote-description-analytes" data-row = ${rowNo} data-samplecode = "${sample_code}"  data-toggle="modal" data-sampletype=${sample_type} class="btn btn-outline-success btn-sm mdi mdi-eye"></span>
            `);
            var $row = $('#create-detail').find('tr#detail-row-' + rowNo);
            $row.find('#quote-description').empty().append(text);
            $row.find('.quotation-price-hint').text('Select analysis type to load package parameters.');
        }
        $('#detail-edit-mode').on('show.bs.modal', function(e) {
            var detail_data = $(e.relatedTarget).data('detail')
            var sample_name = $(e.relatedTarget).data('sample');
            $(this).find('.modal-title').empty();
            var header = $(`<span><i class="mdi mdi-pencil text-primary"></i> Edit ${sample_name} Details </span>`)
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
            var editRow = $(`
            <div class="form-group">
                <label class="cotrol-label">Sample Type</label>
                <span class="form-control">${data.sample_type_name}</span>
                <input type="hidden" id="sample-type-id" value="${data.sample_type}">
                <input type="hidden" name="detail_id" id="detail-id" value="${data.id}">
            </div>
            <div class="form-group">
                <label class="control-label">Analysis Type</label>
                <select class="form-group select2" name="part_no[]" multiple id="edit-part-no">
                   
                    
                </select>
            </div>
            <div class="form-group">
                <label class="control-label">Quantity</label>
                <input type="text" name="quantity" value="${data.quantity}" class="form-control">
            </div>
            <div class="form-group">
                <label class="control-label">Unit Price</label>
                <input type="number" name="unit_price" value="${data.unit_price}" class="form-control" min="0" step="0.01" placeholder="0 = pricelist">
                <small class="text-muted">Leave 0 to use pricelist on save.</small>
            </div>
            <div class="form-group">
                <label class="control-label">Tax %</label>
                <input type="hidden" name="tax" value="${data.tax}" class="quotation-tax">
                <span class="form-control-plaintext">${data.tax !== null && typeof data.tax !== 'undefined' ? parseFloat(data.tax).toFixed(2) : '0.00'}% (from pricelist)</span>
            </div>
            <div class="form-group">
                <label class="control-label">Parameters</label>
                <span data-toggle="modal" data-target="#edit-detail-analytes" class="float-right" ><i class="mdi mdi-pencil"></i></span>
                <div class="description-analytes p-2" style="border:1px solid grey;">
                </div>
            </div>
            
            `).clone();
            $(editRow).find('#edit-part-no').select2();
            $.each(analysis, function(j, s) {
                // console.log(s);
                var option_text = $(`<option value="${s.id}">${s.name}</option>`)
                $(editRow).find('#edit-part-no').append(option_text);
            })

            $(editRow).find('#edit-part-no').val(data.part_no_value)

            $.each(data.sub_analytes, function(i, e) {
                var text = $(`<span>${e}*, </span>`);
                $(editRow).find('.description-analytes').append(text);
            });
            $.each(data.acc_analytes, function(i, e) {
                var text = $(`<span>${e} <img src="/images/tick.png" height="8" width="8">, </span>`);
                $(editRow).find('.description-analytes').append(text);
            });
            $.each(data.sub_acc, function(i, e) {
                var text = $(`<span>${e}* <img src="/images/tick.png" height="8" width="8">, </span>`);
                $(editRow).find('.description-analytes').append(text);
            });
            $.each(data.default, function(i, e) {
                var text = $(`<span>${e} , </span>`);
                $(editRow).find('.description-analytes').append(text);
            });

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

            $.ajax({
                url: '/fetch-sample-analytes/' + sample_type + '/' + analysis_s.toString() + '/' + detail + '?quotation_header_id=' + encodeURIComponent(quotationPricingConfig.quotationHeaderId),
                beforeSend: function() {
                    $('#edit-description').empty();
                    $('#edit-analyte-all, #edit-accreditted, #edit-sub').prop('checked', false);
                },
                success: function(data) {
                    $.each(data, function(i, e) {
                        var analysis_text = $(`<tr>
                                <td colspan="7"><b>${i}</b></td>
                            </tr>`)
                        $('#edit-description').append(analysis_text);
                        $.each(e, function(j, s) {
                            var rows = quoteAnalytesRow(s);
                            $('#edit-description').append(rows);
                        })
                    });
                    // Prefer in-modal edits that have not been submitted yet.
                    applyParameterStateToModal($('#edit-detail-analytes'), savedState);
                }
            })
        })

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
            var loqMap = {};
            var maxTat = null;
            var spans = [];

            $scope.find('.analyte-selected').each(function() {
                var $selected = $(this);
                var parent_tr = $selected.closest('tr');
                if (!$selected.prop('checked')) {
                    return;
                }

                var analyte_name = parent_tr.find('.analyte-name').val();
                var analyte_id = String($selected.val() || '');
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
                var analyte_text = '';
                if (isAcc && !isSub) {
                    analyte_text = `<span>${analyte_name} <img src="/images/tick.png" alt="tick" height="8" width="8">, </span>`;
                    analytes_accreditted.push(analyte_id);
                } else if (isSub && !isAcc) {
                    analyte_text = `<span>${analyte_name}*, </span>`;
                    analyte_sub.push(analyte_id);
                } else if (isAcc && isSub) {
                    analyte_text = `<span>${analyte_name} * <img src="/images/tick.png" alt="tick" height="8" width="8">,</span> `;
                    sub_acc.push(analyte_id);
                } else {
                    analyte_text = `<span>${analyte_name}, </span>`;
                    default_analytes.push(analyte_id);
                }
                spans.push(analyte_text);
            });

            return {
                analytes_accreditted: analytes_accreditted,
                analyte_sub: analyte_sub,
                sub_acc: sub_acc,
                default_analytes: default_analytes,
                loqMap: loqMap,
                maxTat: maxTat,
                spans: spans,
            };
        }

        $('#save-edit').off('click').on('click', function(e) {
            e.preventDefault();
            var $modal = $('#edit-detail-analytes');
            persistEditedLoqs($modal).always(function() {
                var state = collectSelectedAnalyteState($modal);
                var $holder = $('#detail-edit-mode').find('.description-analytes').first();
                $holder.empty();
                state.spans.forEach(function(html) {
                    $holder.append(html);
                });
                $holder.append($('<input type="hidden" name="accreditted_analytes">').val(state.analytes_accreditted.toString()));
                $holder.append($('<input type="hidden" name="sub_analytes">').val(state.analyte_sub.toString()));
                $holder.append($('<input type="hidden" name="sub_acc">').val(state.sub_acc.toString()));
                $holder.append($('<input type="hidden" name="default_analytes">').val(state.default_analytes.toString()));
                $holder.append($('<input type="hidden" name="element_loq_json">').val(JSON.stringify(state.loqMap)));
                $('#edit-detail-analytes').modal('hide');
            });
        });
        $('#quote-description-analytes').on('show.bs.modal', function(e) {
            var sample_code = $(e.relatedTarget).data('sampletype');
            var type_name = $(e.relatedTarget).data('samplecode');
            var row_no = $(e.relatedTarget).data('row');
            quoteDescriptionModalRowNo = row_no;
            quoteDescriptionModalSampleCode = sample_code;
            quoteDescriptionModalTypeName = type_name;
            var $detailRow = $('#create-detail').find('tr#detail-row-' + row_no);
            var part_no_analysis = $detailRow.find('#select-part-final').val();
            var savedState = readRowParameterState($detailRow);
            if (sample_code != '') {
                $.ajax({
                    url: '/fetch-sample-analytes/' + sample_code + '/' + part_no_analysis + '?quotation_header_id=' + encodeURIComponent(quotationPricingConfig.quotationHeaderId),
                    beforeSend: function() {
                        $('#analysis-analytes-holder').empty();
                        $('#select-analyte-all, #select-accredited-all, #select-sub-all').prop('checked', false);
                    },
                    success: function(data) {
                        $.each(data, function(i, e) {
                            var analysis_text = $(`<tr>
                                <td colspan="7"><b>${i}</b></td>
                            </tr>`);
                            $('#analysis-analytes-holder').append(analysis_text);
                            $.each(e, function(j, s) {
                                var rows = quoteAnalytesRow(s);
                                $('#analysis-analytes-holder').append(rows);
                            });
                        });
                        applyParameterStateToModal($('#quote-description-analytes'), savedState);
                    },
                    error: function(data) {
                        console.log(data);
                    }
                });
            }
        });

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
                var $desc = $('#create-detail').find('tr#detail-row-' + row_no).find('#quote-description');
                $desc.empty().removeClass('text-center');
                var header = $(`
                                <p class="mb-0"><b>${type_name}</b>
                                <span data-target="#quote-description-analytes" data-row="${row_no}" data-samplecode="${type_name}" data-toggle="modal" data-sampletype="${sample_code}" class="btn btn-outline-success btn-sm mdi mdi-pencil float-right"></span>
                                </p>
                                <p class="mb-0"><b>Parameters:</b></p>
                            `);
                $desc.append(header);
                state.spans.forEach(function(html) {
                    $desc.append(html);
                });
                $desc.append($('<input type="hidden" name="accreditted_analytes[]">').val(state.analytes_accreditted.toString()));
                $desc.append($('<input type="hidden" name="sub_analytes[]">').val(state.analyte_sub.toString()));
                $desc.append($('<input type="hidden" name="sub_acc[]">').val(state.sub_acc.toString()));
                $desc.append($('<input type="hidden" name="default_analytes[]">').val(state.default_analytes.toString()));
                $desc.append($('<input type="hidden" name="element_loq_json[]">').val(JSON.stringify(state.loqMap)));

                var $row = $('#create-detail').find('tr#detail-row-' + row_no);
                refreshQuotationRowPricingHint($row, true);
                $('#quote-description-analytes').modal('hide');
            });
        });

        var quoteAnalytesRow = function(data) {
            var check_acc = '';
            var check_sub = '';
            var selected = 'checked';

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

            return $(`
                <tr data-reporting-time="${reportingTime}" data-element-id="${elementId}">
                    <td><input type="checkbox" class="analyte-selected" name="selected_analyte[]" value="${elementId}" ${selected}></td>
                    <td>
                        <input type="text" name="analyte_name[]" class="analyte-name border-0" readonly="true" value="${data.analyte_name}" >
                        <input type="hidden" name="analyte_id[]" class="analyte-analyte-id border-0" readonly="true" value="${data.analyte_id}" >
                    </td>
                    <td><input type="checkbox" class="analyte-accredited" name="accreditted" ${check_acc}></td>
                    <td><input type="checkbox" class="analyte-subcontracted" name="sub_contracted" ${check_sub}></td>
                    <td style="min-width: 110px;">
                        <input type="text" class="form-control form-control-sm analyte-loq" name="analyte_loq[]" value="${loqVal}" data-original-loq="${loqVal}" data-element-id="${elementId}">
                    </td>
                    <td class="text-center align-middle">
                        <span class="analyte-mu text-muted">${muVal !== '' ? muVal : '—'}</span>
                    </td>
                    <td class="text-center align-middle" style="min-width: 56px;" title="Element TAT (days); line uses the highest selected">
                        <span class="font-weight-bold">${tatDisplay}</span>
                    </td>
                </tr>
            `);
        }
        $('#select-analyte-all').on('change', function() {
            $('#quote-description-analytes').find('.analyte-selected').prop('checked', $(this).is(':checked'));
        });
        $('#select-accredited-all').on('change', function() {
            $('#quote-description-analytes').find('.analyte-accredited').prop('checked', $(this).is(':checked'));
        });
        $('#select-sub-all').on('change', function() {
            $('#quote-description-analytes').find('.analyte-subcontracted').prop('checked', $(this).is(':checked'));
        });
        $('#edit-sub').on('change', function() {
            $('#edit-detail-analytes').find('.analyte-subcontracted').prop('checked', $(this).is(':checked'));
        });
        $('#edit-accreditted').on('change', function() {
            $('#edit-detail-analytes').find('.analyte-accredited').prop('checked', $(this).is(':checked'));
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
                                    <input type="number" name="unit_price[]" class="form-control" min="0" step="0.01" value="0" placeholder="0 = pricelist">
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