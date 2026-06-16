<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Password | {{ config('app.name', 'LIMS') }}</title>
    @include('partials.favicon')
    <link rel="stylesheet" href="{{ asset('css/w3.css') }}">
    <link href="{{ asset('css/icons/css/fontawesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        html, body {
            background-color: #fff;
            font-family: 'Raleway', sans-serif;
            height: 100vh;
            margin: 0;
            background-image: url('/images/bg-il.png');
            background-repeat: no-repeat;
            background-size: cover;
            background-position: 100% 100%;
        }
        .full-height  { height: 100vh; }
        .flex-center  { align-items: center; display: flex; justify-content: center; }
        .position-ref { position: relative; }
        .change-card  {
            width: 420px;
            max-width: 95vw;
            background: rgba(255,255,255,0.97);
            border-radius: 10px;
            padding: 40px 36px 32px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        }
        .change-card .brand-logo { max-width: 110px; margin-bottom: 18px; }
        .expiry-badge {
            display: inline-block;
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffc107;
            border-radius: 6px;
            padding: 8px 14px;
            font-size: 13px;
            margin-bottom: 18px;
            width: 100%;
            box-sizing: border-box;
        }
        .expiry-badge.expired {
            background: #f8d7da;
            color: #842029;
            border-color: #dc3545;
        }
        .form-group label { font-weight: 600; color: #444; margin-bottom: 4px; }
        .btn-primary { background: #3d5a80; border-color: #3d5a80; width: 100%; padding: 10px; font-size: 15px; }
        .btn-primary:hover { background: #2e4566; border-color: #2e4566; }
        .policy-note { font-size: 11.5px; color: #888; margin-top: 16px; text-align: center; }
    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    <div class="change-card">

        <div class="text-center">
            <img src="/images/imara-sys.png" class="brand-logo" alt="{{ config('app.name') }}">
        </div>

        @if (session('password_expired') || auth()->user()->isPasswordExpired())
            <div class="expiry-badge expired">
                <i class="fas fa-lock"></i>
                <strong>Password Expired</strong> — You must set a new password to continue.
            </div>
        @else
            <div class="expiry-badge">
                <i class="fas fa-key"></i>
                <strong>Change Your Password</strong> — Keep your account secure.
            </div>
        @endif

        <h5 class="mb-3" style="color:#3d5a80;font-weight:700;">Set a New Password</h5>

        @if ($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li style="font-size:13px">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.force-change.update') }}">
            @csrf

            <div class="form-group mb-3">
                <label for="new_password">New Password</label>
                <input id="new_password"
                       type="password"
                       name="new_password"
                       class="form-control @error('new_password') is-invalid @enderror"
                       required
                       autocomplete="new-password"
                       minlength="8"
                       placeholder="At least 8 characters">
                @error('new_password')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group mb-4">
                <label for="new_password_confirmation">Confirm New Password</label>
                <input id="new_password_confirmation"
                       type="password"
                       name="new_password_confirmation"
                       class="form-control"
                       required
                       autocomplete="new-password"
                       placeholder="Repeat the new password">
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check-circle mr-1"></i> Update Password
            </button>
        </form>

        <p class="policy-note">
            <i class="fas fa-shield-alt"></i>
            Passwords expire every <strong>{{ \App\User::PASSWORD_EXPIRY_DAYS }} days</strong> per security policy.
        </p>
    </div>
</div>
</body>
</html>
