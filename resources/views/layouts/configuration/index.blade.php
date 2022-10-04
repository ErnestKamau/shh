@extends('layouts.configuration.layout.app')

@section('title2')
<title>System Configuration</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.css">
<!-- wow js cdn link -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
<!-- wow js initializer -->
<script>
  new WOW().init();
</script>
@endsection
@section('content2')
<div class="card p-2 " style="width: 50%;">

  <form action="{{ route('importUser') }}" enctype="multipart/form-data" method="post">
  @csrf
    <div class="form-group">
      <label class="control-label">Choose excel file</label>
      <input type="file" name="file" placeholder="Choose Excel File To Import..." class="form-control">
    </div>
    <button type="submit" class="btn-btn-outline-success">Upload</button>
  </form>
</div>
<div style="position: fixed;top: 40%;left: 40%; background-color:white;text-align:center;padding:8em" class="wow bounceInDown card" data-wow-duration="2s" data-wow-delay="0s">
  <div class="card-body"></div>
  <h1 class="card-text">Welcome to the Lab</h1>
</div>
@endsection