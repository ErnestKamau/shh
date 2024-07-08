@extends('layouts.lab.layout.app', ['dataTable' => true, 'datePicker' => true, 'select2' => true])

@section('title2')
<title> TAT | Lab Reports</title>

<style>
    .form-part-toggler {
        margin: 0px 0px 5px 0px !important;
        padding: 6px 6px 6px 6px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.09);
        cursor: pointer;
    }

    .form-part-toggler:hover {
        background-color: rgba(0, 0, 0, 0.08);
    }

    #sample-detail-rows .form-group {
        display: none;
    }

    #sample-detail-rows tr.selected-row {
        background-color: rgb(253, 220, 220);
    }

    #sample-detail-rows .text {
        display: unset;
    }

    #sample-detail-rows tr.editable .form-group {
        display: unset;
    }

    #sample-detail-rows tr.editable .text {
        display: none;
    }

    #sample-detail-rows tr {
        cursor: pointer;
    }

    .hidden {
        display: none;
    }

    .overdue-bg-color {
        background-color: rgba(240, 185, 83, 0.972) !important;
    }

    .upfront-bg-color {
        background-color: skyblue !important;
    }

    .ammend-bg-color {
        background-color: #fef764 !important;
    }

    .btn-white {
        background-color: white !important;
    }
</style>
@endsection
@section('content2')
<main>
    <?php
$items = array(
    array(
        'link' => route('dashboard-lab'),
        'name' => 'Dashboard',
        'icon' => null
    ),
    array(
        'link' => route('sample-workflow'),
        'name' => 'TAT Reports',
        'icon' => null
    ),

);
	?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h4 class="p-4">
        <span class="float-left"><i class="mdi mdi-file-document-edit"></i> TAT Lab Reports</span>
    </h4>
    <div class="table-responsive bg-light p-4">

        <b><u>Apply Filters?</u></b>
        <form action="/sample-workflow/Finished Sample" class="mb-4" method="get">
            <div class="row mt-2 p-2 bg-white">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="" class="control-label">Reciept Date From</label>
                        <input type="date" name="date_from" id="" value="{{$filter['date_from'] ?? ''}}"
                            class="form-control">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="" class="control-label">Reciept Date To</label>
                        <input type="date" name="date_to" id="" value="{{$filter['date_to'] ?? ''}}"
                            class="form-control">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="" class="control-label">Users</label>
                        <select name="user_id" id="" class="form-control">
                            <option value="">Select Analyst</option>
                            <option value="All">All</option>
                            @foreach($analysts as $analyst)
                                <option value="{{$analyst->id}}" {{isset($filter['user_id']) && $analyst->id == $filter['user_id'] ? 'selected' : ''}}>{{$analyst->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="" class="control-label">Sample Types</label>
                        <select name="sample_type_id" id="sample_type_id" class="form-control">
                            <option value="">Select Sample Type</option>
                            <option value="All">All</option>
                            @foreach ($sampletypes as $s_type)
                                <option value="{{$s_type->id}}" {{isset($filter['sample_type_id']) && $filter['sample_type_id'] == $s_type->id ? 'selected' : '' }}>{{$s_type->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="" class="control-label">Analysis Type</label>
                        <select name="analysis_type_id" id="analysis_type_id"
                            data-selected="{{isset($filter['analysis_type_id']) ? $filter['analysis_type_id'] : 0 }}"
                            class="form-control">
                            <option value="">Select Analysis Type</option>

                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="" class="control-label">Analyte</label>
                        <select name="analyte_id" id="analyte_id"
                            data-selected="{{isset($filter['analyte_id']) ? $filter['analyte_id'] : 0 }}"
                            class="form-control">
                            <option value="">Select Analyte</option>
                        </select>
                    </div>
                </div>

                <input type="hidden" name="has_filter" value="1">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-sm btn-outline-primary float-right"><i
                            class="mdi mdi-filter"></i> Apply</button>
                </div>
            </div>

        </form>

        <table class="table table-condensed my-small-text table-bordered table-sm"
            data-fixedcls="{{json_encode(["left" => 3])}}">
            <thead>
                <th>Analyte</th>
                <th>Sample Code</th>
                <th>Sample Type</th>
                <th>Analysis</th>
                <th>Receipt Date</th>
                <th>Start Date of Analysis</th>
                <th>Expected Date</th>
                <th>Actual Date</th>
                <th>Tat Days</th>
                <th>Analyst</th>
                <th>Tat Remark</th>
            </thead>
            <tbody>
                @foreach ($data as $tat)
                    <tr class="">
                        <td>{{$tat->analyte_name}}</td>
                        <td>{{$tat->sample_code}}</td>
                        <td>{{$tat->sample_type_name}}</td>
                        <td>{{$tat->analysis_type_name}}</td>
                        <td>{{$tat->receipt_date}}</td>
                        <td>{{$tat->start_date_analysis}}</td>
                        <td>{{$tat->tat_date}}</td>
                        <td>{{$tat->finished_date}}</td>
                        <td class="{{$tat->tat_date > $tat->finished_date ? 'text-success' : 'text-danger'}}">
                            {{$tat->tat_date > $tat->finished_date ? '-' . $tat->tat_overdue_days : '+' . $tat->tat_overdue_days }}
                        </td>
                        <td>{{$tat->analyst->name}}</td>
                        <td
                            class="{{$tat->tat_remark == 4 ? 'bg-warning' : ''}} {{$tat->tat_remark == 5 ? 'bg-danger' : ''}}">
                            {{getTatRemark($tat->tat_remark)}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</main>
@endsection

@section('script2')
<script>
    $(() => {

        var getAnalysisType = (sampletype, callback) => {
            $.ajax({
                url: `/get-analysis-type/${sampletype}/Ajax`,
                method: 'GET',
                success: (data) => {
                    callback(data);
                },
                error: (data) => {
                    console.log(data);
                }
            })
        }
        var getAnalytes = (analysisType, callback) => {
            $.ajax({
                url: `/get-Analyte/${analysisType}/Ajax`,
                method: 'GET',
                success: (data) => {
                    callback(data);
                },
                error: (data) => {
                    console.log(data);
                }
            })
        }
        $('#sample_type_id').on('change', (e) => {
            var s_type = $('#sample_type_id').val();
            var selected_a = $('#analysis_type_id').data('selected');
            if (s_type) {
                getAnalysisType(s_type, (data) => {
                    $('#analysis_type_id').empty();
                    $('#analyte_id').empty();
                    var _option = `<option value="">Select Analysis Type</option><option value="All">All</option>`;
                    var _option1 = `<option value="">Select Analyte</option><option value="All">All</option>`;
                    $('#analysis_type_id').append(_option);
                    $('#analyte_id').append(_option1);

                    $.each(data, (i, obj) => {
                        var option = `<option value="${obj.id}">${obj.name}</option>`;
                        $('#analysis_type_id').append(option);
                    });
                    if (selected_a > 0) {
                        $('#analysis_type_id').val(selected_a);
                    }
                    $('#analysis_type_id').select2();
                    $('#analyte_id').select2();
                });
            }
        });
        $('#analysis_type_id').on('change', (e) => {
            var a_type = $('#analysis_type_id').val();
            var selected = $('#analyte_id').data('selected');
            getAnalytes(a_type, (data) => {
                $('#analyte_id').empty();
                var _option1 = `<option value="">Select Analyte</option><option value="All">All</option>`;
                $('#analyte_id').append(_option1);
                $.each(data, (i, obj) => {
                    var option = `<option value="${obj.id}">${obj.name} - ${obj.code}</option>`;
                    $('#analyte_id').append(option);
                });
                if (selected > 0) {
                    $('#analyte_id').val(selected);
                }
                $('#analyte_id').select2();
            })

        });
        $('#sample_type_id').trigger('change');
    })

</script>

@endsection