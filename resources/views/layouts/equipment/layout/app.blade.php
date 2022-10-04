@extends('layouts.app')

@section('module-name')
<li class="nav-item">
  <a class="nav-link module-name" href="{{ route('equipment-home') }}"><i class="mdi mdi-tools"></i> Equipment Management</a>
</li>
@endsection

@section('title')
  @yield('title2')
@endsection


@section('content')
<br>
<div class="d-flex" id="main-wrapper">
  <div class="flex-fill" id="main-body-content">
		<div id="message-section" class="container-fluid">
			@if ($errors->any())
				<div class="alert alert-danger">
					<ul>
						@foreach ($errors->all() as $error)
							<li><i class="fas fa-exclamation-triangle"></i> {{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif
			@if (\Session::has('success') || \Session::has('error'))
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
			@endif
		</div>
    @yield('content2')
  </div>
</div>
@endsection

@section('script')
  @yield('script2')
@endsection
