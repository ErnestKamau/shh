<!doctype html>
<html lang="<?php echo e(app()->getLocale()); ?>">
	 <head>
		<meta charset="utf-8">
		<link rel="stylesheet" href="<?php echo e(asset('css/w3.css')); ?>">
		<link href="<?php echo e(asset('css/icons/css/fontawesome.min.css')); ?>" rel="stylesheet">
		<link href="<?php echo e(asset('css/app.css')); ?>" rel="stylesheet">  
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>Reset Password | <?php echo e(config('app.name', 'Laravel')); ?></title>

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
								<form method="POST" action="<?php echo e(route('password.update')); ?>" class="w3-padding-large w3-white w3-card-8 w3-round w3-topbar w3-border-brown">
									<?php echo e(csrf_field()); ?>

									<input type="hidden" name="token" value="<?php echo e($token); ?>">
									<div class="w3-padding-large w3-center">
										<img src="/images/imara-sys.png" style="max-width: 120px"  />
									</div>
									<div class="form-group<?php echo e($errors->has('email') ? ' has-error' : ''); ?>">
										<label for="email" class="control-label w3-left w3-text-dark-grey"><strong><?php echo e(__('E-Mail Address')); ?></strong></label>
										<input id="email" type="email" placeholder="<?php echo e(__('E-Mail Address')); ?>..." class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="email" value="<?php echo e($email ?? old('email')); ?>" required autocomplete="email" autofocus>
										<?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
											<span class="invalid-feedback" role="alert">
												<strong><?php echo e($message); ?></strong>
											</span>
										<?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
									</div>
									<div class="form-group<?php echo e($errors->has('password') ? ' has-error' : ''); ?>">
										<label for="password" class="control-label w3-left w3-text-dark-grey"><?php echo e(__('Password')); ?></label>
										<input id="password" type="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="password" required autocomplete="new-password">
										<?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
											<span class="invalid-feedback" role="alert">
												<strong><?php echo e($message); ?></strong>
											</span>
										<?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
									</div>
									<div class="form-group<?php echo e($errors->has('password') ? ' has-error' : ''); ?>">
										<label for="password-confirm" class="control-label w3-left w3-text-dark-grey"><?php echo e(__('Confirm Password')); ?></label>
										<input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
									</div>
									<div class="form-group">
										<button type="submit" class="btn btn-primary">
											<?php echo e(__('Reset Password')); ?>

										</button>
									</div>
								</form>
						  </div>
					 </div>
				</div>
			</div>
		</div>
	 </body>
</html><?php /**PATH C:\wamp64\www\polucon\resources\views/auth/passwords/reset.blade.php ENDPATH**/ ?>