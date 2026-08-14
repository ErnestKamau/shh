@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title> {{ $standard->code }} | Standard</title>

@endsection
@section('content2')
<main>
  <?php
  $items = array(
    array(
      'link' => route('lab-home'),
      'name' => 'Lab Management',
      'icon' => null
    ),
    array(
      'link' => route('sample-types'),
      'name' => 'Sample Types',
      'icon' => null
    ),
    array(
      'link' => '#',
      'name' => 'Standard-' . $standard->code,
      'icon' => null
    )
  );
  ?>
  <x-bread-crumb :items="$items"></x-bread-crumb>
  <h2 class="p-4">
    <i class="mdi mdi-microscope"></i> {{ $standard->code }} <small class="text-muted"> | Standard</small>
    <span class="btn btn-sm btn-outline-primary float-right" data-toggle="modal" data-target="#add-analyte"><i class="mdi mdi-plus"></i> Add</span>
  </h2>
  <div class="table-responsive bg-light p-4">
    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
      <thead class="bg-light p-2">
        <tr>
          <th>No</th>
          <th>Code</th>
          <th nowrap>Analyte</th>
          <th>Standard</th>
          <th>Specification Value</th>
          <th>Specification Value Type</th>
          <th>Low</th>
          <th>High</th>
          <th>Value</th>
          <th>Comment</th>
          <th>Recommendation</th>
          <th></th>`
        </tr>
      </thead>
      <tbody>
        @foreach($standard_analyte as $analyte)
        <tr>
          <td>{{$loop->iteration}}</td>
          <td>{{$analyte->analyte_code}}</td>
          <td>{{$analyte->analyte_name}}</td>
          <td>{{$standard->code}}</td>
          <td class="text-center">{{$analyte->standard_value_id == '' ? '-' : $analyte->standard_value_name}}</td>
          <td>{{$analyte->standard_value_type}}</td>
          <td class="text-center">{!! $analyte->low == '' ? '-':$analyte->low !!}</td>
          <td class="text-center">{!! $analyte->high == '' ? '-': $analyte->high !!}</td>
          <td class="text-center"> {{$analyte->value_type ?? ''}} {!! $analyte->standard_is_value == '' ? '-' : $analyte->standard_is_value !!}</td>
          <td>{{$analyte->comments}}</td>
          <td>{{$analyte->recommendations}}</td>
          <td>
            <span class="btn-sm btn-outline-default" data-target="#edit-guide-{{$loop->iteration}}" data-toggle="modal" data-toggle="tooltip" title="Edit Analyte Standard"><i class="mdi mdi-pencil"></i></span>
            <span class="btn-sm btn-default text-warning" data-toggle="modal" data-target="#clone-guide-{{$loop->iteration}}" data-toggle="tooltip" title="Duplicate/Clone Analyte Standard"><i class="mdi mdi-content-duplicate"></i></span>
            <span class="btn-sm btn-default text-danger" data-toggle="modal" data-target="#delete-guide-{{$loop->iteration}}" data-toggle="tooltip" title="Delete Analyte Standard"><i class="mdi mdi-delete-empty"></i></span>

            <div class="modal fade" id="delete-guide-{{$loop->iteration}}">
              <div class="modal-dialog">
                <form action="{{ route('delete_analysis_guide') }}" method="post" class="modal-content" enctype="multipart/form-data">
                  @csrf
                  <div class="modal-header">
                    <h4 class="modal-title"><i class="mdi mdi-delete-empty text-danger"></i> Delete {{ $analyte->analyte_name}} Standard.</h4>
                  </div>
                  <div class="modal-body" style="background-color: turquoise;">
                    <input type="hidden" name="guide_id" value="{{$analyte->id}}">
                    Confirm you want to delete {{ $analyte->analyte_name}} analyite standard ?
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-success btn-sm"><i class="mdi mdi-content-save"></i> Yes</button>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Cancel</button>
                  </div>
                </form>
              </div>
            </div>


            <div class="modal fade" id="clone-guide-{{$loop->iteration}}">
              <div class="modal-dialog">
                <form action="{{ route('clone_analysis_guide') }}" method="post" class="modal-content" enctype="multipart/form-data">
                  @csrf
                  <div class="modal-header">
                    <h4 class="modal-title"><i class="mdi mdi-content-duplicate text-warning"></i> Clone {{ $analyte->analyte_name}} Standard.</h4>
                  </div>
                  <div class="modal-body" style="background-color: turquoise;">
                    <input type="hidden" name="guide_id" value="{{$analyte->id}}">

                    Confirm You Want to Duplicate {{ $analyte->analyte_name}} Analyte Standard ?
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-success btn-sm"><i class="mdi mdi-content-save"></i> Yes</button>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Cancel</button>
                  </div>
                </form>
              </div>
            </div>

            <div id="edit-guide-{{$loop->iteration}}" class="modal fade" role="dialog">
              <div class="modal-dialog">
                <!-- Modal content-->
                <form class="modal-content" method="POST" action="{{ route('add-analyte-guide') }}" enctype="multipart/form-data">
                  @csrf
                  <input type="hidden" name="standard_id" value="{{ $standard->id }}" />
                  <input type="hidden" name="guide_id" value="{{$analyte->id}}">

                  <div class="modal-header">
                    <h4 class="modal-title"><i class="mdi mdi-pencil text-primary"></i> Edit {{ $analyte->analyte_name }} Standards</h4>
                  </div>
                  <div class="modal-body">
                    <div class="form-group">
                      <label class="control-label">Analyte</label>
                      <select class="form-control" name="analyte_id" required placeholder="Select Analyte...">
                        <option></option>
                        @foreach($analytes as $a)
                        <option value="{{ $a->id }}" {{$analyte->analyte_id == $a->id ? 'selected':''}} data-step="{{ $a->decimal_places }}">{{ $a->name }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="form-group">
                      <label class="control-label">Standard</label>
                      <input type="text" readonly value="{{$standard->code}}" class="form-control">
                    </div>

                    <div class="form-group">
                      <div class="row no-gutters">
                        <div class="col-lg-6 col-sm-6">
                          <div class="form-check">

                            <input class="form-check-input" type="radio" id="is-range-{{$analyte->id}}" class="form-control" name="standard_value_type" value="is_range" data-id="{{$analyte->id}}" onclick="inhoused(this)" {{$analyte->standard_value_type == 'is_range' ? 'checked="checked"' : ''}} />
                            <label class="form-check-label" for="is-range">
                              Use range
                            </label>
                          </div>
                        </div>
                        <div class="col-lg-6 col-sm-6">
                          <div class="form-check">

                            <input class="form-check-input" type="radio" id="is-standard-value-{{$analyte->id}}" class="form-control" name="standard_value_type" data-id="{{$analyte->id}}" value="is_standard_value" onclick="externaled(this)" {{$analyte->standard_value_type == 'is_standard_value' ? 'checked="checked"' :''}} />
                            <label class="form-check-label" for="is-standard-value">
                              Use Value
                            </label>



                          </div>
                        </div>
                      </div>
                    </div>
                    @if($analyte->standard_value_type == 'is_range')
                    <div class="form-group" id="range-{{$analyte->id}}">
                      <label class="control-label">Standard Range</label>
                      <div class="row">
                        <div class="col-lg-6 col-sm-6">
                          <label class="control-label">Low <span class="text-danger">*</span></label>
                          <input type="text" name="low_range" id="Low-Range-{{$analyte->id}}" value="{{$analyte->low}}" class="form-control">
                        </div>
                        <div class="col-lg-6 col-sm-6">
                          <label class="control-label">High <span class="text-danger">*</span></label>
                          <input type="text" name="high_range" id="High-Range-{{$analyte->id}}" value="{{$analyte->high}}" class="form-control">
                        </div>
                      </div>
                    </div>
                    @else
                    <div class="form-group" id="standard-values-{{$analyte->id}}">
                      <label class="control-label">Specification Values <span class="text-danger">*</span></label>
                      <select name="standard_value" class="form-control select-standard-value" id="standard-selected-{{$analyte->id}}" data-analyte='{{ json_encode($analyte) }}' placeholder="Employee...">
                        <option value="">Select Specification Value</option>
                        @foreach($standard_values as $value)
                        <option value="{{$value->code}}" {{$analyte->standard_value_id == $value->id ? 'selected' : ''}}>{{$value->name}}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="row"  id="is-value-{{$analyte->id}}">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="" class="control-label">Limit Measure</label>
                          <select name="limit_measure" id="" class="form-control limit-measure">
                            <option value="Max" {{$analyte->value_type == 'Max' ? 'selected' : ''}}>Max</option>
                            <option value="Min" {{$analyte->value_type == 'Min' ? 'selected' : ''}}>Min</option>
                            <option value="less_than" {{$analyte->value_type == 'less_than' ? 'selected' : ''}}>< (Less Than)</option>
                            <option value="greater_than" {{$analyte->value_type == 'greater_than' ? 'selected' : ''}}>> (Greater Than)</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label class="control-label">Value <span class="text-danger">*</span></label>
                          <input type="text" name="is_value" value="{{$analyte->standard_is_value}}" id="Is-Value-{{$analyte->id}}" placeholder="Enter Value..." class="form-control">
                        </div>
    
                      </div>
                      
                    </div>
                    @endif

                    

                    <div class="form-group">
                      <label class="control-label">Comments</label>
                      <textarea name="comments" class="form-control" placeholder="Guide Comments...">{{$analyte->comments}}</textarea>
                    </div>
                    <div class="form-group">
                      <label class="control-label">Recommendations</label>
                      <textarea name="recommendations" class="form-control" placeholder="Guide Recommendations...">{{$analyte->recommendations}}</textarea>
                    </div>

                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="mdi mdi-content-save"></i> Save</button>
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
                  </div>
                </form>
              </div>
            </div>

          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>






</main>
@endsection
@section('script2')
<div id="add-analyte" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <form class="modal-content" method="POST" action="{{ route('add-analyte-guide') }}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="standard_id" value="{{ $standard->id }}" />
      <input type="hidden" name="guide_id" value="0">

      <div class="modal-header">
        <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Analyte</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="control-label">Analyte</label>
          <select class="form-control" name="analyte_id" required placeholder="Select Analyte...">
            <option></option>
            @foreach($analytes as $a)
            <option value="{{ $a->id }}" data-step="{{ $a->decimal_places }}">{{ $a->name }}</option>
            @endforeach
          </select>
        </div>


        <div class="form-group">
          <label class="control-label">Standard</label>
          <input type="text" readonly value="{{$standard->code}}" class="form-control">
        </div>

        <div class="form-group">
          <div class="row no-gutters">
            <div class="col-lg-6 col-sm-6">
              <div class="form-check">
                <input class="form-check-input" type="radio" id="is-range" class="form-control" name="standard_value_type" value="is_range" onclick="inhouse()" />
                <label class="form-check-label" for="is-range">
                  Use range
                </label>
              </div>
            </div>
            <div class="col-lg-6 col-sm-6">
              <div class="form-check">
                <input class="form-check-input" type="radio" id="is-standard-value" class="form-control" name="standard_value_type" value="is_standard_value" onclick="external()" />
                <label class="form-check-label" for="is-standard-value">
                  Use Value
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group" id="range" style="display:none">
          <label class="control-label">Standard Range</label>
          <div class="row">
            <div class="col-lg-6 col-sm-6">
              <label class="control-label">Low <span class="text-danger">*</span></label>
              <input type="text" name="low_range" id="Low-Range" value="" class="form-control">
            </div>
            <div class="col-lg-6 col-sm-6">
              <label class="control-label">High <span class="text-danger">*</span></label>
              <input type="text" name="high_range" id="High-Range" value="" class="form-control">
            </div>
          </div>
        </div>
        <div class="form-group" id="standard-values" style="display:none">
          <label class="control-label">Standard Values <span class="text-danger">*</span></label>
          <select name="standard_value" class="form-control" id="Standard-Value-primary" placeholder="Employee...">
            @foreach($standard_values as $value)
            <option value="{{$value->code}}">{{$value->name}}</option>
            @endforeach
          </select>
        </div>
        <div class="row hidden" id="is-value-primary">
          <div class="col-md-6">
            <div class="form-group">
              <label for="" class="control-label">Limit Measure</label>
              <select name="limit_measure" id="" class="form-control limit-measure">
                <option value="Max" >Max</option>
                <option value="Min" >Min</option>
                <option value="less_than">< (Less Than)</option>
                <option value="greater_than">> (Greater Than)</option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group ">
              <label class="control-label">Value <span class="text-danger">*</span></label>
              <input type="text" name="is_value" id="Is-Value" placeholder="Enter Value..." class="form-control">
            </div>
          </div>
        </div>
        
        <div class="form-group">
          <label class="control-label">Comments</label>
          <textarea name="comments" class="form-control" placeholder="Guide Comments..."></textarea>
        </div>
        <div class="form-group">
          <label class="control-label">Recommendations</label>
          <textarea name="recommendations" class="form-control" placeholder="Guide Recommendations..."></textarea>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" class="form-control" name="main_standard" value="1" />
          <label class="form-check-label">
            Main Specification
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>
<script>
  console.log('test2')
   function inhouse() {
    var checkbox = document.getElementById("is-range");
    var text = document.getElementById("range");
    var text2 = document.getElementById('is-value');
    if (checkbox.checked == true) {
      text.style.display = "block";
      text2.style.display = "none";
      $('#Low-Range').attr('required', true);
      $('#High-Range').attr('required', true);
      $('#Standard-Value').removeAttr('required', false);

      external();
    } else {
      text.style.display = "none";
      text2.style.display = "none";
      $('#Low-Range').removeAttr('required', false);
      $('#High-Range').removeAttr('required', false);
    }

  }

  function external() {
    var checkbox = document.getElementById("is-standard-value");
    var text = document.getElementById("standard-values");
    if (checkbox.checked == true) {

      text.style.display = "block";
      $('#Standard-Value').attr('required', true);

      inhouse();
    } else {
      text.style.display = "none";
      $('#Standard-Value').removeAttr('required', false);
      $('#Is-Value').removeAttr('required', false);
    }
  }

  function checkvalue(item) {
    var checkexist = item.value;
    var text = document.getElementById('is-value');
    var checkbox = document.getElementById("is-standard-value");

    if ((checkexist === 'IsValue') && (checkbox.checked === true)) {
      $('#Is-Value').attr('required', true);

      text.style.display = "block";
    } else {
      text.style.display = "none";
      $('#Is-Value').removeAttr('required', false);
    }
  }

  function checkvalued(item) {
    console.log('test');
    var index = item.getAttribute('data-id');
    var count = item.getAttribute('data-count');
    var i = count - 1;
    var is_value = 'is-value-' + index;
    var standard = 'standard_value-' + index;
    var is_standard = 'is-standard-value-' + index;
    var checkexist = item.value;

    console.log(item.value);
    var text = document.getElementById(is_value);
    var checkbox = document.getElementById(is_standard);
    if (checkexist == 'IsValue' && checkbox.checked == true) {

      $('#Is-Value').attr('required', true);

      text.style.display = "block";
    } else {
      text.style.display = "none";
      $('#Is-Value').removeAttr('required', false);
    }
  }

  function inhoused(item) {

    var index = item.getAttribute('data-id');
    var is_range = 'is-range-' + index;
    var range = 'range-' + index;
    var is_value = 'is-value-' + index;
    var low = '#Low-Range-' + index;
    var high = '#High-Range' + index;
    var standard = '#Standard-Value-' + index;

    var checkbox = document.getElementById(is_range);
    var text = document.getElementById(range);
    var text2 = document.getElementById(is_value);
    if (checkbox.checked == true) {
      text.style.display = "block";
      text2.style.display = "none";
      $(low).attr('required', true);
      $(high).attr('required', true);
      $(standard).removeAttr('required', false);

      externaled(item);
    } else {
      text.style.display = "none";
      text2.style.display = "block";
      $(low).removeAttr('required', false);
      $(high).removeAttr('required', false);
    }
    

  }

  function externaled(item) {

    var index = item.getAttribute('data-id');
    var is_standard = 'is-standard-value-' + index;
    var standard_value = 'standard-values-' + index;
    var is_value = '#is-value-'+index;

    var standard = '#Standard-Value-' + index;

    var checkbox = document.getElementById(is_standard);
    var text = document.getElementById(standard_value);

    if (checkbox.checked == true) {
      text.style.display = "block";
      $('#Standard-Value').attr('required', true);
      var value_id = '#standard-selected-'+index;
      $(value_id).trigger('change');
      

      inhoused(item);
    } else {
      text.style.display = "none";
      $(standard).removeAttr('required', false);
      $(is_value).removeAttr('required', false);
    }
   
   
  }
   
  $(function() {
    console.log('test')
    $('.select-standard-value').on('change', function() {
      console.log('test1');
      var analyte_id = $(this).data('analyte');
      var selected = $(this).val();
      
      var is_value = '#is-value-'+analyte_id.id;
      var t = 'is-value-'+analyte_id.id;
      if(selected === 'IsValue'){
        $(is_value).removeClass('hidden');
       
      }else{
        
        $(is_value).addClass('hidden');
      }
      

    });
    $('#Standard-Value-primary').on('change',function(){
      var selectedValue = $(this).val();
      if(selectedValue == 'IsValue'){
        console.log('tete')
        $('#is-value-primary').removeClass('hidden');
      }else{
        console.log('tete...')
        $('#is-value-primary').addClass('hidden');
      }
      
    })
  });
</script>

@endsection
