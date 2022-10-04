<!doctype html>
<html lang="{{ app()->getLocale() }}">
	 <head>
		<meta charset="utf-8">
		<link rel="stylesheet" href="{{ asset('css/w3.css') }}">
		<link href="{{ asset('css/icons/css/fontawesome.min.css') }}" rel="stylesheet">
		<link href="{{ asset('css/app.css') }}" rel="stylesheet">  
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>Reset Password | {{ config('app.name', 'Laravel') }}</title>

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
								<form method="POST" action="{{ route('password.email') }}" class="w3-padding-large w3-white w3-card-8 w3-round w3-topbar w3-border-brown">
									{{ csrf_field() }}
									<div class="w3-padding-large w3-center">
										<img src="/images/imara-sys.png" style="max-width: 120px"  />
                  </div>
                  @if (session('status'))
                    <div class="alert alert-success" role="alert">
                      {{ session('status') }}
                    </div><br>
                  @endif
									<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
										<label for="email" class="control-label w3-left w3-text-dark-grey"><strong>{{ __('E-Mail Address') }}</strong></label>
										<input id="email" type="email" placeholder="{{ __('E-Mail Address') }}..." class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                    @error('email')
                      <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                      </span>
                    @enderror
									</div>
									<div class="form-group">
										<button type="submit" class="btn btn-primary">
											{{ __('Send Password Reset Link') }}
										</button>
									</div>
								</form>
						  </div>
					 </div>
				</div>
			</div>
		</div>
	 </body>
</html>