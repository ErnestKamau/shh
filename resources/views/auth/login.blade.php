<!doctype html>
<html lang="{{ app()->getLocale() }}">
	 <head>
		<meta charset="utf-8">
		@include('partials.favicon')
		<link rel="stylesheet" href="{{ asset('css/w3.css') }}">
		<link href="{{ asset('css/icons/css/fontawesome.min.css') }}" rel="stylesheet">
		<link href="{{ asset('material-design/css/materialdesignicons.min.css') }}" rel="stylesheet">
		<link href="{{ asset('css/app.css') }}" rel="stylesheet">  
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>Login | {{ config('app.name', 'Laravel') }}</title>

		  <!-- Fonts -->

		  <!-- Styles -->
		  <style>
				html, body {
					background-color: #fff;
					color: #fff;
					font-family: 'Raleway', sans-serif;
					font-weight: 100;
					height: 100vh;
					margin: 0;      
					background-image: url('/images/bg-il.png');
					background-repeat: no-repeat;
					background-size:cover;
					background-position: 100% 100%;              
				}

				.full-height {
					 height: 100vh;
				}

				.flex-center {
					align-items: center;
					display: flex;
					justify-content: center;
				}

				.position-ref {
					position: relative;
				}

				.content {
					text-align: center;
				}

				.title {
					 font-size: 84px;
				}

				.links > a {
					 color: #fff;
					 padding: 15px 25px;
					 font-size: 12px;
					 font-weight: 600;
					 letter-spacing: .1rem;
					 text-decoration: none;
					 text-transform: uppercase;
				}

				.m-b-md {
					 margin-bottom: 30px;
				}

				.w3-whiter{
					background-color: rgba(199,199,199, 0.8);
					color: #232323;
				}

				.password-input-wrap {
					position: relative;
					display: block;
					width: 100%;
				}

				.password-input-wrap .form-control {
					padding-right: 2.75rem;
					box-sizing: border-box;
				}

				.password-toggle-btn {
					position: absolute;
					top: 70%;
					right: 0.35rem;
					transform: translateY(-50%);
					width: 2.5rem;
					height: 2.5rem;
					margin: 0;
					padding: 0;
					border: none;
					border-radius: 0.2rem;
					background: transparent;
					color: #555;
					cursor: pointer;
					display: inline-flex;
					align-items: center;
					justify-content: center;
					line-height: 1;
					-webkit-appearance: none;
					appearance: none;
					z-index: 2;
				}

				.password-toggle-btn .mdi {
					display: inline-flex;
					align-items: center;
					justify-content: center;
					width: 1.5rem;
					height: 1.5rem;
					font-size: 1.25rem;
					line-height: 1;
					text-align: center;
					pointer-events: none;
				}

				.password-toggle-btn:hover,
				.password-toggle-btn:focus {
					color: #232323;
				}

				.password-toggle-btn:focus {
					outline: none;
					box-shadow: none;
				}

				.password-toggle-btn:focus-visible {
					outline: 2px solid #795548;
					outline-offset: 2px;
				}

				/* AmSpec Maroon & White theme overrides */
				.w3-border-brown {
					border-color: #6D0A0E !important;
				}
				.btn-primary {
					background-color: #6D0A0E !important;
					border-color: #6D0A0E !important;
					color: #ffffff !important;
					font-weight: 600;
					padding: 8px 20px;
					border-radius: 4px;
					transition: all 0.2s ease;
				}
				.btn-primary:hover,
				.btn-primary:focus {
					background-color: #8B1E22 !important;
					border-color: #8B1E22 !important;
					color: #ffffff !important;
					box-shadow: 0 4px 8px rgba(109, 10, 14, 0.3);
				}
				.btn-link {
					color: #6D0A0E !important;
					font-weight: 500;
				}
				.btn-link:hover,
				.btn-link:focus {
					color: #8B1E22 !important;
					text-decoration: underline;
				}
		  </style>
	 </head>
	 <body>
		<div class="flex-center position-ref full-height">
			<div class="content">
				<div class="container">
					
					 <div class="row">
						  <div class="flex-center position-ref w3-card-2">
									<form class="w3-padding-large w3-white w3-card-8 w3-round w3-topbar w3-border-brown" method="POST" action="{{ route('login') }}" style="width: 380px; max-width: 100%; box-sizing: border-box;">
										 {{ csrf_field() }}
										<div class="w3-padding-large w3-center">
											@include('layouts.partials.auth-logo')
										</div>
										 <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
											<label for="email" class="control-label w3-left w3-text-dark-grey"><strong>E-Mail Address</strong></label>
											<input id="email" type="email" placeholder="{{ __('E-Mail Address') }}..." class="form-control" name="email" value="{{ old('email') }}" required autofocus>

											@if ($errors->has('email'))
												 <span class="help-block">
													  <strong>{{ $errors->first('email') }}</strong>
												 </span>
											@endif
											@if ($errors->has('active'))
												 <span class="help-block">
													  <strong>{{ $errors->active }}</strong>
												 </span>
											@endif
										 </div>
										 <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
											<label for="password" class="control-label w3-left w3-text-dark-grey"><strong>Password</strong></label>
											<div class="password-input-wrap">
												<input id="password" type="password" placeholder="{{ __('Password') }}..." class="form-control" name="password" required>
												<button type="button" class="password-toggle-btn" id="password-toggle" aria-label="Show password" aria-pressed="false">
													<i class="mdi mdi-eye-outline" aria-hidden="true"></i>
												</button>
											</div>
											@if ($errors->has('password'))
												 <span class="help-block">
													  <strong>{{ $errors->first('password') }}</strong>
												 </span>
											@endif
										 </div>

										@if(session('login_attempts_warning'))
											<div class="alert alert-warning" style="background:#fff3cd;border:1px solid #ffc107;color:#856404;padding:10px 14px;border-radius:4px;margin-bottom:12px;width:100%;box-sizing:border-box;overflow-wrap:break-word;">
												<strong>&#9888;</strong> {{ session('login_attempts_warning') }}
											</div>
										@endif

										@if(session('failed_login_attempts') && session('failed_login_attempts') >= 2)
											<div style="text-align: center; margin-bottom: 15px;">
												<span class="badge badge-danger" style="background-color: #dc3545; color: white; padding: 8px 12px; border-radius: 20px; font-size: 13px; display: inline-block;">
													<i class="fas fa-exclamation-circle"></i> {{ 5 - session('failed_login_attempts') }} {{ 5 - session('failed_login_attempts') == 1 ? 'attempt' : 'attempts' }} remaining
												</span>
											</div>
										@endif

										@if(session('is_account_locked'))
											<div style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 14px; border-radius: 6px; margin-bottom: 12px; width:100%; box-sizing:border-box; overflow-wrap:break-word;">
												<div style="font-size: 18px; margin-bottom: 6px;">
													<i class="fas fa-lock"></i>
												</div>
												<p style="margin: 0; font-size: 14px; line-height: 1.5;">
													Your account has been locked after 5 unsuccessful login attempts. Please contact your administrator to unlock your account.
												</p>
											</div>
										@endif

										 <div class="form-group">
											<div class="checkbox">
                      <label>
                      <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me
                      </label>
											</div>
										 </div>

										 <div class="form-group">
											<button type="submit" class="btn btn-primary">
                      Login
											</button>
											<div class="w3-padding-top w3-right">
												<a class="btn btn-link" href="{{ route('password.request') }}" class="w3-margin-top">
													 Forgot Your Password?
												</a>
											</div>
										 </div>
									</form>
						  </div>
					 </div>
				</div>
			</div>
		</div>
		<script>
			(function () {
				var input = document.getElementById('password');
				var btn = document.getElementById('password-toggle');
				var icon = btn ? btn.querySelector('i') : null;
				if (!input || !btn || !icon) {
					return;
				}
				btn.addEventListener('click', function () {
					if (input.type === 'password') {
						input.type = 'text';
						icon.classList.remove('mdi-eye-outline');
						icon.classList.add('mdi-eye-off-outline');
						btn.setAttribute('aria-label', 'Hide password');
						btn.setAttribute('aria-pressed', 'true');
					} else {
						input.type = 'password';
						icon.classList.remove('mdi-eye-off-outline');
						icon.classList.add('mdi-eye-outline');
						btn.setAttribute('aria-label', 'Show password');
						btn.setAttribute('aria-pressed', 'false');
					}
				});
			})();
		</script>
	 </body>
</html>