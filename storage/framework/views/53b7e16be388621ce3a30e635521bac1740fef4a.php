<!doctype html>
<html lang="<?php echo e(app()->getLocale()); ?>">
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" href="<?php echo e(asset('css/w3.css')); ?>">
	<link href="<?php echo e(asset('css/icons/css/fontawesome.min.css')); ?>" rel="stylesheet">
	<link href="<?php echo e(asset('css/app.css')); ?>" rel="stylesheet">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="/material-design/css/materialdesignicons.min.css">
	<title>Verification  | <?php echo e(config('app.name', 'Laravel')); ?></title>

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
							<form class="w3-padding-large w3-white w3-card-8 w3-round w3-topbar w3-border-brown" method="POST" action="<?php echo e(route('verify-store')); ?>">
									<?php echo e(csrf_field()); ?>

								<div class="w3-padding-large w3-center">
									<img src="/images/imara-sys.png" style="max-width: 120px"  />
								</div>

								<div class="form-group<?php echo e($errors->has('verify') ? ' has-error' : ''); ?>">
									<label for="verify_code" class="control-label w3-left w3-text-dark-grey"><strong>Verification Code</strong></label>
									<input id="verify" type="text" placeholder="Type Your Verification Code..." class="form-control" name="verify_code" required>
									<?php if($errors->has('verify')): ?>
										<span class="help-block">
											<strong><?php echo e($errors->first('verify')); ?></strong>
										</span>
									<?php endif; ?>
									</div>

									<div class="form-group">
									<button type="button" id="logout-btn" data-action="<?php echo e(route('logout')); ?>" class="btn btn-outline-danger">
										<i class="mdi mdi-power"></i>
									</button>
									<button type="submit" class="btn btn-primary">
										Submit
									</button>
									<div class="w3-padding-top w3-right">
										<a class="btn btn-link" href="<?php echo e(route('verify-resend')); ?>" class="w3-margin-top">
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
	});
</script>
</html><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/auth/twofactor.blade.php ENDPATH**/ ?>