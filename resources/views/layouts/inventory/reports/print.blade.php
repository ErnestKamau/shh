<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- CSRF Token -->
	<title>Report - {{ $title }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
	<link rel="stylesheet" href="/material-design/css/materialdesignicons.min.css">
  	<link rel="stylesheet" href="/css/bootstrap.min.css">
</head>
<body>
	<div class="text-center pb-3">
		<img src="{{ imageTobase64('/storage/companies/9RUZQlhlqNlYp1icQFROCRLgLTtvqRrTtcXyms2g.png') }}" style="max-height: 70px" />
	</div>
	<hr>
	<div class="pv-2 text-center mb-2">
		<h4><span style="border-bottom:2px solid #000">{{ strtoupper($title) }}</span></h4>
	</div>
	<small>Date : {{ date('d/m/Y') }}</small>
	<br>
	<div class="main-table mt-2">
		<table class="table table-bordered table-condensed table-banded">
			<thead>
				<tr>
					<th>#</th>
					@foreach ($columns as $col)
						<th nowrap>{{ clear_underscore($col) }}</th>
					@endforeach
				</tr>
			</thead>
			<tbody>
				@foreach ($data as $dt)
					<tr>
						<td>{{ $loop->iteration }}</td>
						@foreach ($columns as $col)
							<td>{{ $dt->$col }}</td>
						@endforeach
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed:400,300,600,700&display=swap" rel="stylesheet" type="text/css">
<script>
	window.print();
</script>
</html>