@extends('layouts.lab.layout.app', ['dataTable' => true, 'select2' => true])

@section('title2')
<title>Sample Types</title>
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
        'link' => route('sample-types'),
        'name' => 'Sample Types',
        'icon' => null
    ),
    array(
        'link' => route('sample-types'),
        'name' => 'Zoho Items to Analysis',
        'icon' => null
    )
);
  ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h5 class="p-2 pt-4">
        <i class="mdi mdi-sync"></i> Match Analysis Types to Zoho Items

    </h5>
    <br>
    <!-- ------------------------------------ -->
    <div class="tab-card">
        <div class="card-body">
            <div class="table-responsive p-3 bg-light">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                    <thead class="bg-light p-2">
                        <tr>
                           <th>#</th>
                           <th>Analysis Type</th>
                           <th>Zoho Item</th>
                           <th>Sample Type</th>
                           
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($analysis_types as $analysis)
                            <tr>
                                <td><span class="btn btn-sm btn-default text-primary" data-toggle="modal" data-target="#edit-analysis" data-record="{{json_encode($analysis)}}" ><i class="mdi mdi-pencil" data-toggle="modal" title="Edit"></i></span></td>
                                <td>{{$analysis->name}}</td>
                                <td>{{$analysis->zohoitem ? $analysis->zohoitem->name  : 'NOT SET'}}</td>
                                <td>{{$analysis->sample_type->name}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
    <!-- ------------------------------------ -->

</main>
@endsection

@section('script2')
<div class="modal fade" id="edit-analysis" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('zoho-item-analysis-store')}}" method="post">
                @csrf
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

<script type="text/javascript">
    $(function () {
        var getAnalysisBody = (data)=>{
            var body = $(`
                <div class="alert alert-primary p-2 d-flex">
                        <i class="mdi mdi-alert-decagram-outline" style="font-size:25px"></i>
                        <h6 class="pl-2 mt-2">Tie ${data.name} Analysis to Zoho Item</h6> 
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Zoho Item</label>
                    <select name="zoho_id" id="" class="form-control zoho_id">
                        <option value="">Zoho Item</option>
                        @foreach ($zoho_items as $z_item)
                            <option value="{{$z_item->id}}">{{$z_item->name}}</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="analysis_type_id" value="${data.id}">
            `).clone();
            if(data.zoho_id > 0){
                $(body).find('.zoho_id').val(data.zoho_id);
            }
            $(body).find('.zoho_id').select2();
            return body;
        }
        $('#edit-analysis').on('show.bs.modal',(e)=>{
            var data = $(e.relatedTarget).data('record');
            var body = getAnalysisBody(data);
            $('#edit-analysis').find('.modal-body').empty();
            $('#edit-analysis').find('.modal-body').append(body);
        });
    })
</script>
@endsection