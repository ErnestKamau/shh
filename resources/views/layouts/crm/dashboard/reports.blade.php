@extends('layouts.crm.dashboard.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title> {{ $customer->name }} - Customer | CRM</title>
<style type="text/css">
    .card{
        border-radius: 0.5rem;
    }
</style>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => '/dasboard/crm/client-home',
            'name' => $customer->name,
            'icon' => null
        ),
        array(
            'link' => '/dasboard/crm/client-home',
            'name' => 'CRM',
            'icon' => null
        ),
        array(
            'link' => '#',
            'name' => 'Reports',
            'icon' => null
        ),
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <div class="mb-3">
        <h4>
            <i class="mdi mdi-notebook-edit-outline mr-2"></i> Reports
        </h4>
        <div class="card mt-3">
            <div class="card-body">
                <form action="{{ route('crm-lab-reports') }}" method="get">
                    <div class="row">
                        <div class="col-md-12">
                            <p><b>Filter Reports : </b></p>
                        </div>
                        <div class="col-md-3">
                            <label for="start_date">Start Lab Receipt Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date">End Lab Receipt Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="sample_type">Sample Type</label>
                            <select name="sample_type" id="sample_type" class="form-control">
                                <option value="">--Select Sample Type--</option>
                                @foreach ($sample_types as $sampletype)
                                    <option value="{{ $sampletype->id }}">{{ $sampletype->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="customer">Company Unit</label>
                            <select name="crm_unit_id" id="crm_unit_id" class="form-control">
                                <option value="">--Select Unit--</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="filter" value="1">
                        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                        <div class="col-md-12 p-2">
                            <button type="submit" class="btn btn-primary btn-sm" style="float:right"> <i class="mdi mdi-magnify"></i> Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card mb-4" style="clear:both">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Report (PDF)</th>
                            <th>Report (Excel)</th>
                            <th>Receipt Date</th>
                            <th>Sample Type</th>
                            <th>Company Unit</th>
                            <th>Report No</th>
                            <th>Report Date</th>
                            <th>No of Samples</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batches as $batch)
                            <tr>
                                <td nowrap class="text-center"><a href="/storage{{$batch->batch_report_url}}" target="_blank"><i class="mdi mdi-download"></i> <br> Download PDF</a></td>
                                <td nowrap class="text-center">{!! $batch->excel_result_url ? '<a href="/storage'.$batch->excel_result_url.'" target="_blank"><i class="mdi mdi-download"></i> <br> Download Excel</a>' : '' !!}</td>
                                <td>{{ $batch->receipt_date }}</td>
                                <td>{{ $batch->sample_type->name }}</td>
                                <td>{{ $batch->crmunit->name }}</td>
                                <td>{{ $batch->samples->pluck('report_number')->implode(', ') }}</td>
                                <td>{{ $batch->updated_at->format('Y-m-d') }}</td>
                                <td>{{ $batch->samples->count() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="mdi mdi-flask-outline" style="font-size:2rem;"></i>
                                    <div class="mt-2">No Reports found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection

@section('script2')
<script>
</script>

@endsection