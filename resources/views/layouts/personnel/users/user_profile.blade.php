@extends('layouts.app',['select2'=>true])

@section('module-name')
<li class="nav-item">
    <a class="nav-link module-name" href="{{ route('home') }}"><i class="mdi mdi-account"></i> Profile</a>
</li>
@endsection

@section('title')
<style type="text/css">
    .substringed {
        cursor: pointer;
    }

    .substringed .hoverable {
        display: none;
    }

    .substringed:hover .hoverable {
        display: unset !important;
    }

    .substringed:hover .default-seen {
        display: none !important;
    }

    .substringed .default-seen {
        display: unset !important;
    }

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

    .datepicker {
        position: ;
    }

    #eventHistory:hover {
        transform: scale(1.01);
        box-shadow: 0 6px 15px rgba(0, 0, 0, .12), 0 4px 8px rgba(0, 0, 0, .06);
    }
</style>

@endsection


@section('content')
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<link href="https://pagecdn.io/lib/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker3.min.css" rel="stylesheet" crossorigin="anonymous">
<link href="https://pagecdn.io/lib/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker3.css" rel="stylesheet" crossorigin="anonymous">
<link href="https://pagecdn.io/lib/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.standalone.css" rel="stylesheet" crossorigin="anonymous">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css" />

<div class="row" id="body-row">
    <!-- Sidebar -->

    <!-- sidebar-container END -->

    <!-- MAIN -->
    <div class="py-3" id="main-container-body">
        <div id="message-section" style="padding: 10px 10px 0px 10px !important">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li><i class="fas fa-exclamation-triangle"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            @if (\Session::has('success') || \Session::has('error'))
            @if (\Session::has('success'))
            <div class="alert alert-success center text-lg alert-callout">
                <i class="fas fa-thumbs-up"></i> {{ Session::get('success') }}
            </div>
            @endif
            @if (\Session::has('error'))
            <div class="alert alert-danger center text-lg alert-callout">
                <i class="fas fa-exclamation-triangle"></i> {{ Session::get('error') }}
            </div>
            @endif
            @endif
        </div>
        <div class="">
            <?php
            $items = array(

                array(
                    'link' => '/',
                    'name' => 'Home',
                    'icon' => null
                ),
                array(
                    'link' => 'null',
                    'name' => Auth::user()->name,
                    'icon' => null
                ),


            );
            ?>
            <x-bread-crumb :items="$items"></x-bread-crumb>
            <h2 class="p-4">
                @if (isset($user->photo) && $user->photo != "")
                <img src="{{ $user->photo }}" style="width: 100px" />
                @else
                <i class="mdi mdi-account"></i>
                @endif
                {{ $user->name }} | <small class="text-muted">Profile</small>
            </h2>
            <br>
            <div class="card">
                <form autocomplete="off" action="{{ route('add-personnel', ['id'=>$user->id]) }}" method="POST" class="p-3" enctype="multipart/form-data">
                    <h5 class="card-title"><i class="mdi mdi-key"></i> User Details
                        <button class="btn btn-outline-primary btn-sm float-right"><i class="mdi mdi-content-save"></i> Save</button>
                    </h5>
                    <input autocomplete="off" name="hidden" type="password" style="display:none;">
                    @csrf
                    <div class="table-responsive">
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label class="control-label">Designation *</label>
                                    <select name="designation" class="form-control" placeholder="Designation..." required>
                                        <option></option>
                                        @foreach (getModulePreconfig("Designation", "Personnel-Management") as $item)
                                        <option value="{{ $item->id }}" {{ $user->designation == $item->id ? 'selected' : ''  }}>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label class="control-label">First Name *</label>
                                    <input type="text" class="form-control" name="first_name" value="{{ $user->first_name }}" placeholder="First Name..." required />
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label class="control-label">Middle Name</label>
                                    <input type="text" class="form-control" name="middle_name" value="{{ $user->middle_name }}" placeholder="Middle Name..." />
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label class="control-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" value="{{ $user->last_name }}" placeholder="Last Name..." />
                                </div>
                            </div>
                        </div>
                        <div class="form-group hidden">
                            <label class="control-label">User License</label>
                            <select name="user_license" class="form-control" placeholder="User License..." required>
                                <option></option>
                                @foreach (getUserLicenses() as $i=>$n)
                                <option value="{{ $i }}" {{ $i == $user->license_type ? 'selected' :'' }} {{ intval($license_count[$i]) == intval(mamboSawa($i.'s')) ? 'disabled' : '' }}>{{ $n }} {{ $license_count[$i]."/".mamboSawa($i.'s') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="department" value="{{$user->department_id}}">
                        
                        <input type="hidden" name="position" value="{{$user->position}}">
                        <input type="hidden" name="education_level" value="{{$user->education_level}}">
                        <div class="form-group hidden mt-sm-5 ">
                            <label class="control-label"><input type="checkbox" name="active" value="1" {{ $user->active == "1" ? "checked" : "" }} /> Active</label>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group row no-gutters">
                                    <div class="col-4 text-center">
                                        <img src="{{ $user->photo ?? '/images/user.png' }}" style="max-width: 100%; max-height:75px" />
                                    </div>
                                    <div class="col-8">
                                        <label class="control-label">Photo</label>
                                        <input type="file" class="form-control" name="image" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">Email *</label>
                                    <input type="email" class="form-control" name="email" value="{{ $user->email }}" placeholder="Email..." required />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">Phone</label>
                                    <input type="text" class="form-control" name="phone" value="{{ $user->phone }}" placeholder="Phone..." />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">ID Number/Passport No *</label>
                                    <input type="text" class="form-control" name="id_number" value="{{ $user->id_number }}" placeholder="ID Number..." required />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">Date of Birth</label>
                                    <input type="date" class="form-control" name="date_of_birth" value="{{ $user->date_of_birth }}" placeholder="Date of Birth..." />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">Employment Date</label>
                                    <input type="date" class="form-control" name="employment_date" value="{{ $user->employment_date }}" placeholder="Employment Dat..." />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">KRA PIN</label>
                                    <input type="text" class="form-control" name="kra_pin" value="{{ $user->kra_pin }}" placeholder="KRA PIN..." />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">NSSF</label>
                                    <input type="text" class="form-control" name="nssf" value="{{ $user->nssf }}" placeholder="NSSF..." />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">NHIF</label>
                                    <input type="text" class="form-control" name="nhif" value="{{ $user->nhif }}" placeholder="NHIF..." />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">Signature <span>*</span></label>
                                    <input type="file" name="signature" id="" class="form-control">
                                </div>
                            </div>
                            @if($user->electronic_sig)
                            <div class="col-sm-4">
                                <img src="{{ $user->electronic_sig }}" style="max-width: 100%; max-height:70px">
                            </div>
                            @endif
                        


                    </div>
                    <div class="row">

                        <div class="col-sm-8 mt-sm-5">
                            <div class="form-group hidden">
                                <label class="control-label">
                                    <input type="checkbox" name="has_credentials" value="1" /> Create/Update User Passwords
                                </label>
                            </div>
                        </div>

                    </div>
                    <div class="row hidden" id="passwords-holder">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Password</label>
                                <input autocomplete="off" type="password" value="" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" class="form-control pass" name="main_password" placeholder="Password..." />
                                <div class="has-success"></div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" value="" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" class="form-control pass" name="confirm_password" placeholder="Confirm Password..." />
                                <div class="has-error"></div>
                            </div>
                        </div>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
<!-- Main Col END -->
</div>


@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js" integrity="sha256-4iQZ6BVL4qNKlQ27TExEhBN1HFPvAvAMbFavKKosSWQ=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>
<script src="https://pagecdn.io/lib/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" crossorigin="anonymous"></script>
<script>
    $(function() {
        $('.pass').val('');
        $('[name="has_credentials"]').on('change', function() {
            if ($(this).is(':checked')) {
                $("#passwords-holder").removeClass("hidden");
                $(".pass").attr('required', true);
            } else {
                $('.pass').val('');
                $("#passwords-holder").addClass("hidden");
                $(".pass").removeAttr('required');
            }
        });

        $('[name="confirm_password"]').on('keyup', function() {
            var pass1 = $('[name="main_password"]').val();
            var pass2 = $(this).val();

            if (pass1 != pass2) {
                $(this).siblings('.has-success').html('').addClass('text-success');
                $(this).siblings('.has-error').html(`<i class="mdi mdi-cancel"></i> Passwords did not match.`).addClass('text-danger');
            } else {
                $(this).siblings('.has-error').html('')
                $(this).siblings('.has-success').html('<i class="mdi mdi-check-circle"></i> Passwords Match!.')
            }
        });
    });
</script>
<script>
    $(function() {
        $('.submit-delete-form-btn').on('click', function() {
            var form = $(this);
            if (confirm("Are you sure that you want to remove this role?")) {
                form.submit();
            }
        });
    })
</script>


@endsection