@extends('layouts.app',['select2'=>true])

@section('module-name')
<li class="nav-item">
    <a class="nav-link module-name" href="{{ route('home') }}"><i class="mdi mdi-account"></i> Profile</a>
</li>
@endsection

@section('title')
<style type="text/css">
    /* Profile page only: remove sidebar spacing and hide sidebar */
    #main-container-body {
        margin-left: 0 !important;
        width: 100% !important;
    }

    #sidebar-container {
        display: none !important;
    }

    .profile-hero {
        background: linear-gradient(135deg, rgba(0, 123, 255, 0.08) 0%, rgba(74, 144, 226, 0.05) 100%);
        border-radius: 18px;
        border: 1px solid rgba(0, 123, 255, 0.12);
    }

    .profile-avatar {
        width: 72px;
        height: 72px;
        border-radius: 16px;
        object-fit: cover;
        border: 2px solid rgba(0, 123, 255, 0.18);
        background: #fff;
    }

    .section-title {
        font-size: 12px;
        font-weight: 700;
        color: #495057;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 14px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-title i {
        font-size: 18px;
    }

    .info-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border: 1px solid #e9ecef;
        border-radius: 14px;
        padding: 14px 16px;
        height: 100%;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
    }

    .info-label {
        font-size: 11px;
        font-weight: 700;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .info-value {
        font-size: 14px;
        font-weight: 600;
        color: #212529;
        word-break: break-word;
    }

    .badge-modern {
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .badge-modern-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: #fff;
        box-shadow: 0 2px 10px rgba(40, 167, 69, 0.25);
    }

    .badge-modern-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: #fff;
        box-shadow: 0 2px 10px rgba(108, 117, 125, 0.22);
    }

    .profile-card {
        border-radius: 18px;
        border: 0;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
    }

    .profile-card .card-header {
        background: #fff;
        border-bottom: 1px solid #f1f3f5;
        border-top-left-radius: 18px;
        border-top-right-radius: 18px;
    }
</style>
@endsection

@section('content')
<div class="row" id="body-row">
    <div class="py-3" id="main-container-body">
        <div id="message-section" style="padding: 10px 10px 0px 10px !important">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li><i class="fas fa-exclamation-triangle"></i> {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

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

            <div class="px-3 px-md-4">
            <div class="card profile-card mb-4">
                <div class="card-header p-4 profile-hero">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex align-items-center">
                            <img class="profile-avatar mr-3" src="{{ $user->photo ?? '/images/user.png' }}" alt="Avatar">
                            <div>
                                <h3 class="mb-1 font-weight-bold">{{ $user->name }}</h3>
                                <div class="text-muted">
                                    <span class="mr-2"><i class="mdi mdi-mail-outline"></i> {{ $user->email }}</span>
                                    @if(trim((string) $user->phone) !== '')
                                        <span class="mr-2"><i class="mdi mdi-phone-outline"></i> {{ $user->phone }}</span>
                                    @endif
                                </div>
                                <div class="mt-2">
                                    @if($user->two_factor_confirmed_at)
                                        <span class="badge-modern badge-modern-success">
                                            <i class="mdi mdi-shield-check"></i> Two-Factor Enabled
                                        </span>
                                    @else
                                        <span class="badge-modern badge-modern-secondary">
                                            <i class="mdi mdi-shield-outline"></i> Two-Factor Not Enabled
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 mt-md-0 d-flex align-items-center" style="gap: .5rem;">
                            <button class="btn btn-outline-primary btn-sm" type="button" id="open-2fa-modal">
                                <i class="mdi mdi-shield-key-outline"></i>
                                {{ $user->two_factor_confirmed_at ? 'Manage Two-Factor' : 'Enable Two-Factor' }}
                            </button>
                            <button class="btn btn-primary btn-sm" type="submit" form="personnel-profile-form">
                                <i class="mdi mdi-content-save"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body px-4 py-4">
                    @php $zones = \App\Zone::where('inventory_location_id', getCurrentUserLocation()->id)->orderBy('key')->get(); @endphp
                    <form id="personnel-profile-form" autocomplete="off" action="{{ route('add-personnel', ['id'=>$user->id]) }}" method="POST" enctype="multipart/form-data">
                        <input autocomplete="off" name="hidden" type="password" style="display:none;">
                        @csrf
                        <div class="mb-4">
                            <div class="section-title"><i class="mdi mdi-account-outline text-primary"></i> Personal Details</div>
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="control-label">Designation *</label>
                                        <select name="designation" class="form-control" required>
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

                            <div class="form-group d-none">
                                <label class="control-label">User License</label>
                                <select name="user_license" class="form-control" required>
                                    <option></option>
                                    @foreach (getUserLicenses() as $i=>$n)
                                        <option value="{{ $i }}" {{ $i == $user->license_type ? 'selected' :'' }} {{ intval($license_count[$i]) == intval(mamboSawa($i.'s')) ? 'disabled' : '' }}>{{ $n }} {{ $license_count[$i]."/".mamboSawa($i.'s') }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="control-label">Zone</label>
                                <select name="zone_id" class="form-control">
                                    <option value=""></option>
                                    @foreach ($zones as $zone)
                                        <option value="{{ $zone->id }}" {{ $zone->id == $user->zone_id ? 'selected' : '' }}>{{ $zone->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <input type="hidden" name="department" value="{{$user->department_id}}">
                            <input type="hidden" name="position" value="{{$user->position}}">
                            <input type="hidden" name="education_level" value="{{$user->education_level}}">
                            <div class="form-group d-none mt-sm-5">
                                <label class="control-label"><input type="checkbox" name="active" value="1" {{ $user->active == "1" ? "checked" : "" }} /> Active</label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="section-title"><i class="mdi mdi-card-account-details-outline text-primary"></i> Contact & Identity</div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label">Photo</label>
                                        <input type="file" class="form-control" name="image" />
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
                                        <input type="date" class="form-control" name="date_of_birth" value="{{ $user->date_of_birth }}" />
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label">Employment Date</label>
                                        <input type="date" class="form-control" name="employment_date" value="{{ $user->employment_date }}" />
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
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="section-title"><i class="mdi mdi-flask-outline text-primary"></i> Lab & Signature</div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label">Lab Section</label>
                                        <select name="lab_section_id[]" multiple class="form-control">
                                            @foreach($stages as $stage)
                                                <option value="{{$stage->id}}" {{ in_array($stage->id,explode(',',$user->lab_section_id)) ? 'selected' : ''}}>{{$stage->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label">Signature</label>
                                        <input type="file" name="signature" class="form-control">
                                    </div>
                                </div>
                                @if($user->electronic_sig)
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="control-label">Current signature</label>
                                            <div class="info-card">
                                                <img src="{{ $user->electronic_sig }}" style="max-width: 100%; max-height:70px">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mb-2">
                            <div class="section-title"><i class="mdi mdi-lock-outline text-primary"></i> Security</div>
                            <div class="info-card">
                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                    <div>
                                        <div class="info-label"><i class="mdi mdi-shield-key-outline text-primary"></i> Two-Factor Authentication</div>
                                        <div class="info-value">
                                            @if($user->two_factor_confirmed_at)
                                                Enabled (Authenticator app)
                                            @else
                                                Disabled (Email verification codes)
                                            @endif
                                        </div>
                                        <div class="text-muted mt-1" style="font-size: 12px;">
                                            If enabled, login will require an authenticator code instead of email OTP.
                                        </div>
                                    </div>
                                    <div class="mt-3 mt-md-0">
                                        <button class="btn btn-outline-primary btn-sm" type="button" id="open-2fa-modal-2">
                                            <i class="mdi mdi-shield-key-outline"></i>
                                            {{ $user->two_factor_confirmed_at ? 'Manage Two-Factor' : 'Enable Two-Factor' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="info-card">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                                        <div>
                                            <div class="info-label"><i class="mdi mdi-key-outline text-primary"></i> Password</div>
                                            <div class="text-muted" style="font-size: 12px;">Optionally update your password.</div>
                                        </div>
                                        <div class="mt-3 mt-md-0">
                                            <label class="mb-0">
                                                <input type="checkbox" name="has_credentials" value="1" /> Create/Update User Passwords
                                            </label>
                                        </div>
                                    </div>

                                    <div class="row d-none mt-3" id="passwords-holder">
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label>Password</label>
                                                <input autocomplete="off" type="password" value="" class="form-control pass" name="main_password" placeholder="Password..." />
                                                <div class="has-success"></div>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label>Confirm Password</label>
                                                <input type="password" value="" class="form-control pass" name="confirm_password" placeholder="Confirm Password..." />
                                                <div class="has-error"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<!-- 2FA Modal -->
<div class="modal fade" id="twoFactorModal" tabindex="-1" role="dialog" aria-labelledby="twoFactorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" style="border-radius: 18px;">
      <div class="modal-header">
        <h5 class="modal-title" id="twoFactorModalLabel"><i class="mdi mdi-shield-key-outline text-primary"></i> Two-Factor Authentication</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="twofa-alert" class="alert d-none" role="alert"></div>

        <div id="twofa-enabled-state" class="{{ $user->two_factor_confirmed_at ? '' : 'd-none' }}">
            <div class="info-card mb-3">
                <div class="info-label"><i class="mdi mdi-shield-check text-success"></i> Status</div>
                <div class="info-value">Enabled</div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-2">
                    <div class="info-card h-100">
                        <div class="info-label"><i class="mdi mdi-backup-restore text-primary"></i> Recovery codes</div>
                        <div class="text-muted" style="font-size: 12px;">Regenerate recovery codes (requires password).</div>
                        <div class="mt-3">
                            <input type="password" class="form-control mb-2" id="twofa-password-recovery" placeholder="Confirm password">
                            <button class="btn btn-outline-primary btn-sm" type="button" id="btn-recovery-regenerate">
                                <i class="mdi mdi-refresh"></i> Regenerate Codes
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="info-card h-100">
                        <div class="info-label"><i class="mdi mdi-shield-off-outline text-danger"></i> Disable 2FA</div>
                        <div class="text-muted" style="font-size: 12px;">Disable authenticator protection (requires password).</div>
                        <div class="mt-3">
                            <input type="password" class="form-control mb-2" id="twofa-password-disable" placeholder="Confirm password">
                            <button class="btn btn-outline-danger btn-sm" type="button" id="btn-twofa-disable">
                                <i class="mdi mdi-close-circle-outline"></i> Disable
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div id="twofa-disabled-state" class="{{ $user->two_factor_confirmed_at ? 'd-none' : '' }}">
            <div class="info-card mb-3">
                <div class="info-label"><i class="mdi mdi-shield-outline text-secondary"></i> Status</div>
                <div class="info-value">Not enabled</div>
                <div class="text-muted mt-1" style="font-size: 12px;">Enable authenticator-based 2FA to require an app code at login.</div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-2">
                    <div class="info-card h-100 text-center">
                        <div class="info-label justify-content-center"><i class="mdi mdi-qrcode text-primary"></i> Scan QR Code</div>
                        <div class="my-2">
                            <img id="twofa-qr" src="" alt="QR code" style="max-width: 220px; border-radius: 12px; border: 1px solid #e9ecef; background:#fff;">
                        </div>
                        <div class="text-muted" style="font-size: 12px;">Use Google Authenticator, Authy, or Microsoft Authenticator.</div>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="info-card h-100">
                        <div class="info-label"><i class="mdi mdi-key-outline text-primary"></i> Manual key</div>
                        <div class="info-value" id="twofa-manual-key" style="font-family: monospace;">-</div>
                        <div class="mt-3">
                            <label class="control-label"><strong>Verify code</strong></label>
                            <input type="text" class="form-control" id="twofa-code" placeholder="6-digit code">
                            <button class="btn btn-primary btn-sm mt-3" type="button" id="btn-twofa-confirm">
                                <i class="mdi mdi-shield-check"></i> Confirm Setup
                            </button>
                        </div>
                        <div class="text-muted mt-2" style="font-size: 12px;">Recovery codes can be regenerated after enabling.</div>
                    </div>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Recovery Codes Modal -->
<div class="modal fade" id="recoveryCodesModal" tabindex="-1" role="dialog" aria-labelledby="recoveryCodesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content" style="border-radius: 18px;">
      <div class="modal-header">
        <h5 class="modal-title" id="recoveryCodesModalLabel"><i class="mdi mdi-key-outline text-primary"></i> Recovery Codes</h5>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning" role="alert" style="border-radius: 12px;">
            Save these codes somewhere safe. Each code can be used once to access your account if you lose your authenticator.
        </div>
        <pre class="p-3 mb-2" style="background:#f8f9fa;border-radius:12px;" id="recovery-codes-pre"></pre>
        <div class="d-flex align-items-center" style="gap: .5rem;">
            <button class="btn btn-outline-secondary btn-sm" type="button" id="btn-copy-recovery"><i class="mdi mdi-content-copy"></i> Copy</button>
            <button class="btn btn-primary btn-sm" type="button" id="btn-recovery-saved"><i class="mdi mdi-check"></i> I have saved the recovery codes</button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
    $(function() {
        $('.pass').val('');
        $('[name="has_credentials"]').on('change', function() {
            if ($(this).is(':checked')) {
                $("#passwords-holder").removeClass("d-none");
                $(".pass").attr('required', true);
            } else {
                $('.pass').val('');
                $("#passwords-holder").addClass("d-none");
                $(".pass").removeAttr('required');
            }
        });

        $('[name="confirm_password"]').on('keyup', function() {
            var pass1 = $('[name="main_password"]').val();
            var pass2 = $(this).val();

            if (pass1 != pass2) {
                $(this).siblings('.has-success').html('').addClass('text-success');
                $(this).siblings('.has-error').html('<i class="mdi mdi-cancel"></i> Passwords did not match.').addClass('text-danger');
            } else {
                $(this).siblings('.has-error').html('')
                $(this).siblings('.has-success').html('<i class="mdi mdi-check-circle"></i> Passwords Match!.')
            }
        });
    });
</script>

<script>
    $(function () {
        var csrf = $('meta[name="csrf-token"]').attr('content');

        function showAlert(type, message) {
            var $a = $('#twofa-alert');
            $a.removeClass('d-none').removeClass('alert-success alert-danger alert-info')
                .addClass(type === 'success' ? 'alert-success' : (type === 'info' ? 'alert-info' : 'alert-danger'))
                .text(message);
        }

        function resetAlert() {
            $('#twofa-alert').addClass('d-none').text('');
        }

        function openTwoFaModal() {
            resetAlert();
            $('#twoFactorModal').modal('show');

            // Bootstrap modals may not be ":visible" immediately after .modal('show').
            // Use the server-rendered state (d-none) to decide.
            if (! $('#twofa-disabled-state').hasClass('d-none')) {
                $('#twofa-qr').attr('src', '');
                $('#twofa-manual-key').text('Loading…');

                $.ajax({
                    url: "{{ route('account.2fa.setup') }}",
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': csrf },
                    success: function (resp) {
                        var svg = resp.qr_svg_base64 || resp.qr_svg || resp.qr || null;
                        if (svg) {
                            $('#twofa-qr').attr('src', 'data:image/svg+xml;base64,' + svg);
                        }
                        $('#twofa-manual-key').text(resp.manual_key || '-');
                    },
                    error: function (xhr) {
                        var msg = 'Failed to start 2FA setup.';
                        if (xhr && xhr.status) {
                            msg += ' (HTTP ' + xhr.status + ')';
                        }
                        showAlert('danger', msg);
                        $('#twofa-manual-key').text('-');
                    }
                });
            }
        }

        $('#open-2fa-modal, #open-2fa-modal-2').on('click', openTwoFaModal);

        $('#btn-twofa-confirm').on('click', function () {
            resetAlert();
            $.ajax({
                url: "{{ route('account.2fa.confirm') }}",
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
                data: { code: $('#twofa-code').val() },
                success: function (resp) {
                    showAlert('success', 'Two-factor enabled. Save your recovery codes now.');
                    $('#recovery-codes-pre').text((resp.recovery_codes || []).join("\n"));
                    $('#twofa-disabled-state').addClass('d-none');
                    $('#twofa-enabled-state').removeClass('d-none');
                    $('#recoveryCodesModal').modal({ backdrop: 'static', keyboard: false });
                    $('#recoveryCodesModal').modal('show');
                },
                error: function (xhr) {
                    showAlert('danger', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Invalid code.');
                }
            });
        });

        $('#btn-twofa-disable').on('click', function () {
            resetAlert();
            $.ajax({
                url: "{{ route('account.2fa.disable') }}",
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
                data: { password: $('#twofa-password-disable').val() },
                success: function () {
                    showAlert('success', 'Two-factor disabled. Reloading...');
                    window.location.reload();
                },
                error: function (xhr) {
                    showAlert('danger', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to disable.');
                }
            });
        });

        $('#btn-recovery-regenerate').on('click', function () {
            resetAlert();
            $.ajax({
                url: "{{ route('account.2fa.recovery-codes') }}",
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
                data: { password: $('#twofa-password-recovery').val() },
                success: function (resp) {
                    showAlert('success', 'Recovery codes regenerated. Save them now.');
                    $('#recovery-codes-pre').text((resp.recovery_codes || []).join("\n"));
                    $('#recoveryCodesModal').modal({ backdrop: 'static', keyboard: false });
                    $('#recoveryCodesModal').modal('show');
                },
                error: function (xhr) {
                    showAlert('danger', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to regenerate codes.');
                }
            });
        });

        $('#btn-copy-recovery').on('click', function () {
            var text = $('#recovery-codes-pre').text();
            if (! text) {
                return;
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
                return;
            }
        });

        $('#btn-recovery-saved').on('click', function () {
            window.location.reload();
        });
    });
</script>
@endsection