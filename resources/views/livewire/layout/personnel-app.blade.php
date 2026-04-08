@extends('layouts.personnel.layout.app', ['dataTable'=>false, 'select2'=>true])

@section('title2')
<title>{{ $pageTitle ?? 'Personnel Management' }}</title>
@endsection

@section('content2')
<main>
    <?php
    $breadcrumbItems = [
        [
            'link' => route('personnel-home'),
            'name' => 'Personnel Management',
            'icon' => null,
        ],
    ];

    if (isset($componentType) && $componentType === 'personnel-dashboard') {
        $breadcrumbItems[] = [
            'link' => route('personnel-home'),
            'name' => 'Dashboard',
            'icon' => null,
        ];
    }
    ?>
    <x-bread-crumb :items="$breadcrumbItems"></x-bread-crumb>

    @if($componentType === 'personnel-dashboard')
        @livewire('personnel.personnel-dashboard')
    @endif
</main>
@endsection

@section('script2')
<div id="add-personnel" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <form autocomplete="off" class="modal-content" method="POST" action="{{ route('add-personnel', ['id' => time()]) }}" enctype="multipart/form-data">
            @csrf
            <input autocomplete="off" name="hidden" type="password" style="display:none;">
            <div class="modal-header">
                <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Personnel</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label">Designation <span class="text-danger">*</span></label>
                            <select name="designation" class="form-control" required>
                                <option value=""></option>
                                @foreach (getModulePreconfig('Designation', 'Personnel-Management') as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" placeholder="First Name..." required />
                        </div>
                        <div class="form-group">
                            <label class="control-label">Middle Name</label>
                            <input type="text" class="form-control" name="middle_name" placeholder="Middle Name..." />
                        </div>
                        <div class="form-group">
                            <label class="control-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" placeholder="Last Name..." />
                        </div>
                        <div class="form-group">
                            <label class="control-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" placeholder="Email..." required />
                        </div>
                        <div class="form-group">
                            <label class="control-label">Phone</label>
                            <input type="text" class="form-control" name="phone" placeholder="Phone..." />
                        </div>
                        <div class="form-group">
                            <label class="control-label">ID Number/Passport No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="id_number" placeholder="ID Number..." required />
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label">Employment Date</label>
                            <input type="date" class="form-control" name="employment_date" />
                        </div>
                        <div class="form-group">
                            <label class="control-label">Education Level</label>
                            <select name="educational_level" class="form-control">
                                <option value=""></option>
                                @foreach (getModulePreconfig('Educational Levels', 'Personnel-Management') as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Position <span class="text-danger">*</span></label>
                            <select name="position" class="form-control" required>
                                <option value=""></option>
                                @foreach (getModulePreconfig('Job Description', ['Personnel-Management', 'Skills-Matrix']) as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Department <span class="text-danger">*</span></label>
                            <select name="department" class="form-control" required>
                                <option value=""></option>
                                @foreach (getDepartments() as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="" class="control-label">Lab Section</label>
                            <select name="lab_section_id[]" multiple class="form-control">
                                @foreach($stages as $stage)
                                    <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">User License <span class="text-danger">*</span></label>
                            <select name="user_license" class="form-control" required>
                                <option value=""></option>
                                @foreach (getUserLicenses() as $key => $name)
                                    <option value="{{ $key }}" {{ isset($license_count[$key]) && (int) $license_count[$key] >= (int) mamboSawa($key . 's') ? 'disabled' : '' }}>
                                        {{ $name }} {{ isset($license_count[$key]) ? $license_count[$key] . '/' . mamboSawa($key . 's') : '0/' . mamboSawa($key . 's') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label"><input type="checkbox" name="active" value="1" checked /> Active</label>
                        </div>
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
