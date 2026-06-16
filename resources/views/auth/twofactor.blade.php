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
	<title>Verification  | {{ config('app.name', 'Laravel') }}</title>

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
	</style>
</head>
<body>
	<div class="flex-center position-ref full-height">
		<div class="content">
			<div class="container">

					<div class="row">
						<div class="flex-center position-ref w3-card-2">
							<form class="w3-padding-large w3-white w3-card-8 w3-round w3-topbar w3-border-brown" method="POST" action="{{ route('verify-store') }}">
									{{ csrf_field() }}
								<div class="w3-padding-large w3-center">
									<img src="/images/imara-sys.png" style="max-width: 120px"  />
								</div>

								@if (session('error'))
									<div class="alert alert-danger">
										{{ session('error') }}
									</div>
								@endif

								@if (session('success'))
									<div class="alert alert-success">
										{{ session('success') }}
									</div>
								@endif

								@if (!is_null($otpAttemptsRemaining ?? null))
									<div class="alert alert-info">
										OTP attempts remaining: <strong>{{ (int) $otpAttemptsRemaining }}</strong>
										@if (!empty($otpRetryAfterSeconds))
											<br>
											Retry window: <span class="otp-retry-countdown" data-seconds="{{ (int) $otpRetryAfterSeconds }}"></span>
										@endif
									</div>
								@endif

								<div class="alert alert-secondary">
									Resend attempts used: <strong>{{ (int) ($resendAttemptsUsed ?? 0) }}</strong>/5
									@if (!empty($resendWaitSeconds))
										<br>
										Next resend in: <span id="resend-countdown" data-seconds="{{ (int) $resendWaitSeconds }}"></span>
									@endif
								</div>

								<div class="form-group{{ $errors->has('verify') ? ' has-error' : '' }}">
									<label for="verify_code" class="control-label w3-left w3-text-dark-grey"><strong>Verification Code</strong></label>
									<input id="verify" type="text" placeholder="Type Your Verification Code..." class="form-control" name="verify_code" required>
									@if ($errors->has('verify'))
										<span class="help-block">
											<strong>{{ $errors->first('verify') }}</strong>
										</span>
									@endif
									</div>

									<div class="form-group">
									<button type="button" id="logout-btn" data-action="{{ route('logout') }}" class="btn btn-outline-danger">
										<i class="mdi mdi-power"></i>
									</button>
									<button type="submit" class="btn btn-primary">
										Submit
									</button>
									<div class="w3-padding-top w3-right">
										<a class="btn btn-link {{ !empty($resendWaitSeconds) ? 'disabled' : '' }}" id="resend-link" href="{{ route('verify-resend') }}" class="w3-margin-top" @if(!empty($resendWaitSeconds)) aria-disabled="true" @endif>
											Resend Verification Code
										</a>
									</div>
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

		function formatCountdown(totalSeconds) {
			totalSeconds = Math.max(0, parseInt(totalSeconds, 10) || 0);
			var mins = Math.floor(totalSeconds / 60);
			var secs = totalSeconds % 60;
			return mins + 'm ' + (secs < 10 ? '0' + secs : secs) + 's';
		}

		$('.otp-retry-countdown').each(function(){
			var $el = $(this);
			var seconds = parseInt($el.data('seconds'), 10) || 0;
			if (seconds <= 0) {
				$el.text('0m 00s');
				return;
			}

			$el.text(formatCountdown(seconds));
			var intervalId = setInterval(function(){
				seconds -= 1;
				$el.text(formatCountdown(seconds));
				if (seconds <= 0) {
					clearInterval(intervalId);
				}
			}, 1000);
		});

		var $resendCountdown = $('#resend-countdown');
		var $resendLink = $('#resend-link');
		if ($resendCountdown.length) {
			var resendSeconds = parseInt($resendCountdown.data('seconds'), 10) || 0;
			if (resendSeconds > 0) {
				$resendCountdown.text(formatCountdown(resendSeconds));
				$resendLink.addClass('disabled').attr('aria-disabled', 'true').on('click', function(e){
					e.preventDefault();
				});

				var resendInterval = setInterval(function(){
					resendSeconds -= 1;
					$resendCountdown.text(formatCountdown(resendSeconds));

					if (resendSeconds <= 0) {
						clearInterval(resendInterval);
						$resendCountdown.text('0m 00s');
						$resendLink.removeClass('disabled').removeAttr('aria-disabled').off('click');
					}
				}, 1000);
			}
		}
	});
</script>
</html>