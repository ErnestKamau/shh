<!doctype html>
<html lang="{{ app()->getLocale() }}">
	 <head>
		<meta charset="utf-8">
		<link rel="stylesheet" href="{{ asset('css/w3.css') }}">
		<link href="{{ asset('css/icons/css/fontawesome.min.css') }}" rel="stylesheet">
		<link href="{{ asset('css/app.css') }}" rel="stylesheet">  
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

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
			<title>{{ $title }}</title>
	 </head>
	 <body>
		<div class="flex-center position-ref full-height">
			<div class="content">
				<div class="container">
					<div class="alert alert-danger">
						<h5 class="pt-3">{{ $title }}</h5>
						<div class="py-3">
							<b><i class="fas fa-warning"></i> ACTION FAILED.</b> An action has already been performed on this approval.
						</div>
						<div class="py-2">
							<a href="{{ route('view-request-details', [$req->request_type, $req->id]) }}" class="btn btn-primary">
								<i class="md md-eye"></i> View {{ $req->request_type }} {{ $req->request_code }} 
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	 </body>
</html>