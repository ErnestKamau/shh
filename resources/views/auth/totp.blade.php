<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
	<meta charset="utf-8">
	@include('partials.favicon')
	<link rel="stylesheet" href="{{ asset('css/w3.css') }}">
	<link href="{{ asset('css/icons/css/fontawesome.min.css') }}" rel="stylesheet">
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="/material-design/css/materialdesignicons.min.css">
	<title>Authenticator Verification | {{ config('app.name', 'Laravel') }}</title>

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

		.w3-whiter{
			background-color: rgba(199,199,199, 0.8);
			color: #232323;
		}
	</style>
</head>
<body>
	<div class="flex-center position-ref full-height">
		<div class="content">
			<div class="container">
				<div class="row">
					<div class="flex-center position-ref w3-card-2">
						<form class="w3-padding-large w3-white w3-card-8 w3-round w3-topbar w3-border-brown" method="POST" action="{{ route('verify-totp-store') }}">
							{{ csrf_field() }}
							<div class="w3-padding-large w3-center">
								<img src="/images/imara-sys.png" style="max-width: 120px"  />
							</div>

							@if (\Session::has('error'))
								<div class="alert alert-danger">
									{{ Session::get('error') }}
								</div>
							@endif

							<div class="form-group">
								<label for="totp_code" class="control-label w3-left w3-text-dark-grey"><strong>Authenticator Code</strong></label>
								<input id="totp_code" type="text" placeholder="6-digit code" class="form-control" name="code" inputmode="numeric" autocomplete="one-time-code" required>
								<small class="text-muted">Enter the 6-digit code from your authenticator app.</small>
							</div>

							<div class="form-group mt-3">
								<label for="recovery_code" class="control-label w3-left w3-text-dark-grey"><strong>Recovery Code (optional)</strong></label>
								<input id="recovery_code" type="text" placeholder="XXXXX-XXXXX" class="form-control" name="recovery_code">
								<small class="text-muted">If you can’t access your authenticator, use a recovery code.</small>
							</div>

							<div class="form-group mt-4">
								<button type="button" id="logout-btn" data-action="{{ route('logout') }}" class="btn btn-outline-danger">
									<i class="mdi mdi-power"></i>
								</button>
								<button type="submit" class="btn btn-primary">
									Verify
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript">
	$(function(){
		$('#logout-btn').on('click', function(){
			var $action = $(this).data('action');
			var $form = $(this).parents('form');
			$form.attr('action', $action);
			$form.submit();
		});
	});
</script>
</html>

