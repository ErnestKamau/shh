@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        {{ $slot }}
    </div>
@endsection
