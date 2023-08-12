@extends('layouts.lab.layout.app', ['dataTable'=>true])

@section('title2')
  <title>Labs</title>
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
          'link' => route('labs'),
          'name' => 'Labs',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-flask"></i> Labs
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-lab"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>Code</th>
            <th>Name</th>
            <th>Start Sample</th>
            <th>Address</th>
            <th>Website</th>
            <th>Fax</th>
            <th>Email</th>
            <th nowrap>Phone 1</th>
            <th nowrap>Phone 2</th>
            <th nowrap>Phone 3</th>
            <th>Internal Lab?</th>
            <th>Active?</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @if(count($labs) > 0)
            @foreach($labs as $lab)
              <tr>
                <td valign="center">{{ $loop->iteration }}</td>
                <td>{{ $lab->code }}</td>
                <td>{{ $lab->name }}</td>
                <td>{{$lab->start_sample_no ?? '-'}}</td>
                <td>{{ $lab->address }}</td>
                <td>{{ $lab->website }}</td>
                <td>{{ $lab->fax }}</td>
                <td>{{ $lab->email }}</td>
                <td>{{ $lab->phone1 ?? '-' }}</td>
                <td>{{ $lab->phone2 ?? '-' }}</td>
                <td>{{ $lab->phone3 ?? '-' }}</td>
                <td class="text-small">{!! $lab->is_external == '0' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                <td class="text-small">{!! $lab->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                <td nowrap>
                  <button class="btn btn-primary btn-sm" data-target="#edit-lab-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                  <a class="btn btn-success btn-sm" href="{{ route('show-lab-analysis-types', ['labid'=>$lab->id]) }}"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
                  <div id="edit-lab-{{ $loop->iteration }}" class="modal fade" role="dialog">
                    <div class="modal-dialog modal-lg">
                      <!-- Modal content-->
                      <form class="modal-content" method="POST" action="{{ route('edit-lab', ['id'=>$lab->id]) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                          <h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Lab</h4>
                        </div>
                        <div class="modal-body row">
                          <div class="col-sm-6">
                            <div class="form-group">
                              <label class="control-label">Lab Name</label>
                              <input type="text" class="form-control" name="name" value="{{ $lab->name }}" placeholder="Lab Name..." required />
                            </div>
                            <div class="form-group">
                              <label class="control-label">Lab Code</label>
                              <input type="text" class="form-control" name="code" value="{{ $lab->code }}" placeholder="Lab Code..." required />
                            </div>
                            <div class="form-group">
                              <label class="control-label">Start Sample No</label>
                              <input type="text" class="form-control" name="start_sample_no" value="{{ $lab->start_sample_no }}" placeholder="Lab Start Sample No..." required />
                            </div>

                            <div class="form-group">
                              <label class="control-label">Lab Postal Address</label>
                              <textarea class="form-control" name="address" placeholder="Lab Postal Address..." required>{{ $lab->address }}</textarea>
                            </div>
                            <div class="form-group">
                              <label class="control-label">Lab Location</label>
                              <input type="text" class="form-control" name="location" value="{{ $lab->location }}" placeholder="Lab Location..." required />
                            </div>
                            <div class="form-group">
                              <label class="control-label">Lab Website</label>
                              <input type="text" class="form-control" name="website" value="{{ $lab->website }}" placeholder="Lab Website..." />
                            </div>
                          </div>
                          <div class="col-sm-6">
                            <div class="form-group">
                              <label class="control-label">Lab Fax</label>
                              <input type="fax" class="form-control" name="fax" value="{{ $lab->fax }}" placeholder="Lab Fax..." />
                            </div>
                            <div class="form-group">
                              <label class="control-label">Lab Email</label>
                              <input type="email" class="form-control" name="email" value="{{ $lab->email }}" placeholder="Lab Email..." required />
                            </div>
                            <div class="form-group">
                              <label class="control-label">Lab Phone 1</label>
                              <input type="tel" class="form-control" name="phone1" value="{{ $lab->phone1 }}" placeholder="Lab Phone 1..." required />
                            </div>
                            <div class="form-group">
                              <label class="control-label">Lab Phone 2</label>
                              <input type="tel" class="form-control" name="phone2" value="{{ $lab->phone2 }}" placeholder="Lab Phone 2..." />
                            </div>
                            <div class="form-group">
                              <label class="control-label">Lab Phone 3</label>
                              <input type="tel" class="form-control" name="phone3" value="{{ $lab->phone3 }}" placeholder="Lab Phone 3..." />
                            </div>
                            <div class="form-group">
                              <label class="control-label"><input type="checkbox" name="is_external" value="1" {{ $lab->is_external == 1 ? 'checked' : '' }} /> Is an External Lab?</label>
                            </div>
                            <div class="form-group">
                              <label class="control-label"><input type="checkbox" value="1" name="active" {{ $lab->active == 1 ? 'checked' : '' }} /> Is Active?</label>
                            </div>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
                          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
      @if(count($labs) == 0)
        <div class="alert alert-info">
          <i class="mdi mdi-alert"></i> No Labs added yet.
        </div>
      @endif
    </div>
  </main>
@endsection

@section('script2')
  <div id="add-lab" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-labs') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Lab</h4>
        </div>
        <div class="modal-body row">
          <div class="col-sm-6">
            <div class="form-group">
              <label class="control-label">Lab Name</label>
              <input type="text" class="form-control" name="name" placeholder="Lab Name..." required />
            </div>
            <div class="form-group">
              <label class="control-label">Lab Code</label>
              <input type="text" class="form-control" name="code" placeholder="Lab Code..." required />
            </div>
            <div class="form-group">
              <label class="control-label">Start Sample No</label>
              <input type="text" class="form-control" name="start_sample_no" value="" placeholder="Lab Start Sample No..." required />
            </div>
            <div class="form-group">
              <label class="control-label">Lab Postal Address</label>
              <textarea class="form-control" name="address" placeholder="Lab Postal Address..." required required ></textarea>
            </div>
            <div class="form-group">
              <label class="control-label">Lab Location</label>
              <input type="text" class="form-control" name="location" placeholder="Lab Location..." required />
            </div>
            <div class="form-group">
              <label class="control-label">Lab Website</label>
              <input type="text" class="form-control" name="website" placeholder="Lab Website..."  />
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label class="control-label">Lab Fax</label>
              <input type="text" class="form-control" name="fax" placeholder="Lab Fax..." />
            </div>
            <div class="form-group">
              <label class="control-label">Lab Email</label>
              <input type="email" class="form-control" name="email" placeholder="Lab Email..." required />
            </div>
            <div class="form-group">
              <label class="control-label">Lab Phone 1</label>
              <input type="tel" class="form-control" name="phone1" value="" placeholder="Lab Phone 1..." required />
            </div>
            <div class="form-group">
              <label class="control-label">Lab Phone 2</label>
              <input type="tel" class="form-control" name="phone2" value="" placeholder="Lab Phone 2..." />
            </div>
            <div class="form-group">
              <label class="control-label">Lab Phone 3</label>
              <input type="tel" class="form-control" name="phone3" value="" placeholder="Lab Phone 3..." />
            </div>
            <div class="form-group">
              <label class="control-label"><input type="checkbox" value="1" name="is_external" /> Is an External Lab?</label>
            </div>
            <div class="form-group">
              <label class="control-label"><input type="checkbox" value="1" name="active" checked /> Is Active?</label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
@endsection