@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Qc Standards</title>
<style>
    .text-bold {
        font-weight: 600;
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
            'link' => route('qc_configuration_index'),
            'name' => 'Qc - Configuration',
            'icon' => null
        ),
        array(
            'link' => route('qc_StandardShow',['id'=>$standard->id]),
            'name' => 'Standard - ' . $standard->name,
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h5 class="p-4">
        <i class="mdi mdi-file-certificate-outline"></i> QC Standards | {{$standard->name}}
        <span class="btn btn-sm btn-default text-primary float-right" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;" data-mode="add" data-toggle="modal" data-target="#add-analyte"><i class="mdi mdi-plus"></i> Add Analyte</span>
    </h5>
    <div class="card" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-condensed table-bordered table-hover table-stripped table-sm" style="width: 110%;">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Analyte Code</th>
                            <th>Standard</th>
                            <th>Expected Value</th>
                            <th>Tolerance 1</th>
                            <th>Tolerance 2</th>
                            <th>Mean value</th>
                            <th>Rel Std dev</th>
                            <th>Status</th>
                            <th>Comment</th>
                            <th>Recomendations</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach($standardAnalytes as $s_analyte)
                        <tr>
                            <td>
                                <span class="btn btn-sm btn-default text-primary" data-mode="edit" data-record="{{json_encode($s_analyte)}}" data-toggle="modal" data-target="#add-analyte"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span>
                                <span class="btn btn-sm btn-default text-danger" data-toggle="modal" data-target="#delete-analyte" data-record="{{json_encode($s_analyte)}}"><i class="mdi mdi-delete-empty" data-toggle="tooltip" title="Edit"></i></span>

                            </td>
                            <td>{{$s_analyte->getAnalyte()->code}}</td>
                            <td>{{$standard->name}}</td>
                            <td>{{$s_analyte->expected_value}}</td>
                            <td>{{$s_analyte->low ?? '-'}}</td>
                            <td>{{$s_analyte->high ?? '-'}}</td>
                            <td>{{$s_analyte->mean_value ?? '0'}}</td>
                            <td>{{$s_analyte->rel_std_dev ?? '0'}}</td>
                            <td class="text-center">{!! $s_analyte->is_active == 1 ? '<span class="text-success"><i class="mdi mdi-checkbox-marked-circle-outline mdi-24px"></i></span>' : '-'  !!}</td>
                            <td class="text-center">
                                @if($s_analyte->comments != '')
                                <span class="btn btn-sm btn-info" data-toggle="modal" data-target="#analyte-comment"  data-mode="0"  data-record="{{json_encode($s_analyte)}}"><i class="mdi mdi-arrow-expand"></i></span>
                                @else
                                -
                                @endif
                            
                            </td>
                            <td class="text-center">
                                @if($s_analyte->recommendations != '')
                                    <span class="btn btn-sm btn-info" data-toggle="modal" data-mode="1" data-target="#analyte-comment" data-record="{{json_encode($s_analyte)}}" ><i class="mdi mdi-arrow-expand"></i></span>
                                @else
                                    -
                                @endif
                            
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</main>
@endsection

@section('script2')
<div class="modal fade" id="add-analyte" data-analytes="{{json_encode($analytes)}}" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('qc_addQcStandardAnalyte')}}" method="POST">
                @csrf 
                <div class="modal-header card-header">
                    <h5><i class="mdi mdi-plus"></i> Add Analyte to Standard</h5>
                </div>
                <div class="modal-body">
                    

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="delete-analyte" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('qc_deleteQcStandardAnalyte')}}" method="POST">
                @csrf  
                <div class="modal-body">
                   
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="mdi mdi-delete-empty"></i> Yes, Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="analyte-comment" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            
            <div class="modal-body">
                
            </div>
            <div class="modal-footer">
                <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
            </div>
        </div>
    </div>
</div>
<script>
    $(()=>{
        let addAnalyteBody = (data)=>{
            var analytes =$('#add-analyte').data('analytes');
            var body = $(`
                    <div class="form-group">
                        <label for="" class="control-label">Analyte</label>
                        <select name="analyte_id" id="analyte_id" class="form-control"></select>
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Standard</label>
                        <input type="text" class="form-control" disabled value="{{$standard->name}}">
                        <input type="hidden" name="standard_id" value="{{$standard->id}}">
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Expected Value</label>
                        <input type="text" name="expected_value" placeholder="Expected Value ..." value="${data ? data.expected_value : ''}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label"><input type="checkbox" name="use_absolute" ${data && data.absolute_tolerance == 1 ? `checked` : ``} id="use_absolute"> Use Absolute Tolerance</label>
                    </div>
                    <div class="row form-group">
                        <div class="col-md-6">
                            <label for="" class="control-label">tolerance 1</label>
                            <input type="text" name="tolerance_1" value="${data && data.low != null ? data.low : ``}" placeholder="Tolerance 1 ..." class="form-control">
                        </div>
                        <div class="col-md-6 hidden tolerance-2">
                            <label for="" class="control-label">tolerance 2</label>
                            <input type="text" name="tolerance_2" value="${data && data.high != null ? data.high : ''}" placeholder="Tolerance 2 ..." class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Mean Value</label>
                        <input type="text" name="mean_value" value="${data && data.mean_value != null ? data.mean_value : ''}" placeholder="Mean..." class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Relative Standard Deviation</label>
                        <input type="text" name="rel_std_dev" value="${data && data.rel_std_dev != null ? data.rel_std_dev : ''}" placeholder="Relative Standard Deviation..." class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Comments</label>
                        <textarea class="form-control" name="comment" placeholder="Comment..." col="30" row="5">${data ? data.comments : ``}</textarea>

                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Recomendations</label>
                        <textarea class="form-control" name="recomendation" placeholder="Recomendations..." col="30" row="5">${data ? data.recommendations : ``}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label"><input type="checkbox" name="is_active" id="is_active"> Is Active</label>
                        <input type="hidden" name="standard_analyte_id" value="${data ? data.id : 0}">
                    </div>
            `).clone();
            $.each(analytes,(i,obj)=>{
                var option = `<option value="${obj.id}" ${data && data.analyte_id == obj.id ? `selected` : `` }>${obj.name}</option>`
                $(body).find('#analyte_id').append(option);
                
            }); 
            $(body).find('#analyte_id').select2();
            if(data){
                if(data.is_active == 1){
                    $(body).find('#is_active').prop('checked',true);
                }
            }else{
                $(body).find('#is_active').prop('checked',true);
            }

            $(body).find('#use_absolute').on('change',(e)=>{
                if($(e.currentTarget).is(':checked')){
                    $(body).find('.tolerance-2').removeClass('hidden');
                }else{
                    $(body).find('.tolerance-2').addClass('hidden');
                }
            });
            
            return body;
        }
        var deleteAnalyteBody = (data)=>{
            var body = $(`
                <div class="alert alert-danger p-2 d-flex">
                    <i class="mdi mdi-delete-empty mdi-36px"></i>
                    <span class="p-2">
                        Confirm you want to delete ${data.analytename} analyte from {{$standard->name}} Standard
                    </span>
                </div>
                <input type="hidden" name="standard_analyte_id" value="${data.id}">
            `).clone();
            return body;
        }
        $('#delete-analyte').on('show.bs.modal',(e)=>{
            var data = $(e.relatedTarget).data('record');
            var body = deleteAnalyteBody(data);
            $('#delete-analyte').find('.modal-body').empty();
            $('#delete-analyte').find('.modal-body').append(body);
        })
        $('#add-analyte').on('show.bs.modal',(e)=>{
            var mode = $(e.relatedTarget).data('mode');
            var data = mode == 'add' ? false : $(e.relatedTarget).data('record');
            var body = addAnalyteBody(data);
            $('#add-analyte').find('.modal-body').empty();
            $('#add-analyte').find('.modal-body').append(body);
        });
        let viewAnalyteComment = (data,mode)=>{
            var body = $(`
                <div class="alert alert-primary p-2 d-flex">
                    <i class="mdi mdi-information-outline" style="font-size: 24px;"></i>
                    <span class="p-2">${mode== "0" ? data.comments : data.recommendations}</span>
                </div>
            `).clone();
            return body;
        }
        $('#analyte-comment').on('show.bs.modal',(e)=>{
            var data = $(e.relatedTarget).data('record');
            var mode = $(e.relatedTarget).data('mode');
            var body = viewAnalyteComment(data,mode);
            $('#analyte-comment').find('.modal-body').empty();
            $('#analyte-comment').find('.modal-body').append(body);
           
        })
    })
</script>
@endsection