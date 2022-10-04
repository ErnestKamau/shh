@extends('layouts.crm.dashboard.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title> {{ $customer->name }} - Customer | CRM</title>
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
        padding: 2px 15px;
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
        font-size: 12px !important;
    }
</style>
@endsection
@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => '/dasboard/crm/client-home',
            'name' => 'CRM',
            'icon' => null
        ),

        array(
            'link' => '#',
            'name' => $customer->name,
            'icon' => null
        ),
        array(
            'link' => '#',
            'name' => 'Laboratory Booking',
            'icon' => null
        ),
        array(
            'link' => '#',
            'name' => $book->id,
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h4 class="pt-4 pr-4 pl-4 pb-3">
        {!! !isset($batch->id) ? 'New batch' : $batch->batch_code.' Batch Info' !!}
    </h4>
    <div class="ro no-gutters">
        <div class="col-sm-4 p-2">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="mdi mdi-pencil-outline"></i> Batch Info
                    </h5>
                    <hr>
                    <form action="{{ route('add-batch-info', ['batch'=>$batchID]) }}" method="POST">
                        <?php $maxDate = getTodayDate(); ?>
                        @csrf

                        <div class="form-group">
                            <label class="control-label">Date Collected <span class="text-danger">*</span></label>
                            <input type="date" max="{{ $maxDate }}" placeholder="Lab Receiption Date" value="{{ $batch->date_collected ?? '' }}" class="form-control " name="date_collected" required>

                        </div>
                        <div class="form-group">
                            <label class="control-label">Lab Reception Date <span class="text-danger">*</span> </label>
                            <input type="date" max="{{ $maxDate }}" placeholder="Lab Receiption Date" value="{{ $batch->receipt_date ?? '' }}" class="form-control " name="receipt_date" {{ $defaultClient === false ? 'required' : '' }} autocomplete="off">
                            {{-- <div class="datepicker date input-group">
                                        <input type="text" max="{{ $maxDate }}" placeholder="Lab Receiption Date" value="{{ $batch->receipt_date ?? '' }}" class="form-control " name="receipt_date" required autocomplete="off">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="mdi mdi-clock"></i></span>
                            </div>
                        </div> --}}
                </div>

                <div class="form-group ">

                    <label class="control-label">Client <span class="text-danger">*</span> <span class="btn-primary p-0 btn-sm" style="margin: 0px !important;" data-target="#add-customer" data-toggle="modal" data-toggle="tooltip" title="Add Client"><i class="mdi mdi-plus"></i></span></label>
                    <select class="form-control" name="crm_customer_id" id="client-select" required readonly>
                        <option value="{{ $client->id }}">$customer->name</option>
                    </select>
                    <input type="hidden" name="is_client_order" value="1" />
                </div>
                <div class="form-group">
                    <label class="control-label"><span class='client-prefered-unit-name'>Site Location</span> <span class="text-danger">*</span> <span class="btn-primary p-0 btn-sm" data-target="#add-company-unit" data-toggle="modal" data-toggle="tooltip" title="Add Site Location"><i class="mdi mdi-plus"></i></span></label>
                    <select class="form-control" name="crm_unit_name" id="client-unit-select" required>
                        <option value="">Select Client Unit...</option>
                        @foreach($customer->units as $unit)
                        <option value="{{$unit->id}}" {{ isset($batch->id) && $batch->crm_customer_unit == $unit->id ? 'selected' : '' }}>{{$unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="control-label text-sm">Sample Type <span class="text-danger">*</span></label>
                    <select class="form-control" name="sample_type_id" required id="batch-info-sample-type">
                        <option value="">Select Sample Type...</option>
                        @foreach (getSampleTypes() as $sample)
                        <option value="{{ $sample->id }}" {{ isset($batch->sample_type_id) && $batch->sample_type_id == $sample->id ? 'selected' : '' }}>{{ $sample->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group btn-group-sm">
                    <label class="control-label">RFT Form No <span class="text-danger">*(unique)</span></label>
                    <input type="text" class="form-control" data-batch="{{isset($batch->id) ? json_encode($batch->id) : 0}}" name="reference_number" value="{{ $batch->reference_number ?? '' }}" placeholder="Reference Number..." required />
                    <small id="rft-message" class="text-danger"></small>
                </div>


                <div class="form-group btn-group-sm">
                    <label class="control-label">Samples Description</label>
                    <textarea class="form-control" name="description" placeholder="Description...">{{ $batch->description ?? '' }}</textarea>
                </div>

                <div class="form-group btn-group-sm">
                    <label class="control-label">Sampled By</label>
                    <input type="text" name="sample_by" value="{{$batch->sampling_officer_name ?? '' }}" id="" placeholder="Sampled By..." class="form-control">

                </div>

                <div class="form-group btn-group-sm">
                    <label class="control-label">Submitted By</label>
                    <input type="text" class="form-control" value="{{$batch->submit_by ?? ''}}" name="submit_by" value="{{ $batch->submit_by ?? '' }}" placeholder="Submitted By..." />
                </div>
                <div class="form-group btn-group-sm">
                    <label class="control-label"><?php $active_company = getActiveCompany() ?>
                        <input type="checkbox" name="agreement" value="1" {{ isset($batch->user_agreement) && $batch->user_agreement == 1 ? 'checked' : '' }}> I agree to {{$active_company->name}} terms and conditions?
                    </label>
                </div>
                <div class="form-group btn-group-sm">
                    <label class="control-label"><?php $active_company = getActiveCompany() ?>
                        <input type="checkbox" name="sampled_by_company_personnel" value="1" {{ isset($batch->sampled_by_company_personnel) && $batch->sampled_by_company_personnel == 1 ? 'checked' : '' }}> Sampled by {{$active_company->name}} personnel?
                    </label>
                </div>
                <div class="form-group">
                    @if(isset($batch->status) && $batch->status != 'Samples En-Route')

                    @else
                    <button class="btn btn-outline-primary btn-lg btn-block" id="save-headers">
                        <i class="mdi mdi-content-save"></i> Save
                    </button>
                    @endif
                </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8 p-2">
        <div class="card tab-card">
            <div class="card-header tab-card-header">
                <ul class="nav nav-tabs card-header-tabs" id="analyte-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="attachment-tab" data-toggle="tab" href="#Attachment" role="tab" aria-controls="Custody" aria-selected="true"><i class="mdi mdi-attachment"></i> Attachments</a>
                    </li>
                </ul>
            </div>
            <div class="tab-content">
                <div class="tab-pane fade show active p-3" id="Atachment" role="tabpanel" aria-labelledby="one-tab">
                    <div class="table-responsive">
                        <table class="table table-sm table-condensed table-hover table-bordered table-stripped">
                            <thead>
                                <th></th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th></th>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

</main>
@endsection
@section('script2')

@endsection