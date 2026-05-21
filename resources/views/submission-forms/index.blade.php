@extends('layouts.lab.layout.app', ['dataTable'=>false])

@section('title2')
  <title>Submission Forms</title>
@endsection

@section('content2')
  <main>
    <?php
      $items = array(
        array(
          'link' => route('lab-home'),
          'name' => 'Lab Management',
          'icon' => null
        ),
        array(
          'link' => route('submission-forms.index'),
          'name' => 'Submission Forms',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @include('submission-forms.partials.horizontal-gutter-styles')
    <div class="submission-forms-horizontal-gutter">

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    @endif

    @livewire('submission-forms.submission-form-registry')

    </div>
  </main>
@endsection

@section('script2')
<script>
  setTimeout(function() {
    $('.alert').fadeOut('slow');
  }, 5000);
</script>
@endsection
