@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true, 'datePicker'=>true])

@section('title2')
  <title>Worksheets | {{ $batch->batch_code }}</title>
  <link type="text/css" rel="stylesheet" href="{{ asset('css/method-sequences.css') }}" />
  @include('worksheets.partials.method-sequences-jquery-styles')
@endsection

@section('content2')
  <main>
    <?php
      $items = [
        [
          'link' => route('dashboard-lab'),
          'name' => 'Dashboard',
          'icon' => null,
        ],
        [
          'link' => route('sample-workflow', ['status' => $batch->status ?? 'All Samples']),
          'name' => 'Sample Workflow',
          'icon' => null,
        ],
        [
          'link' => route('view-batch-details', ['batch' => $batch->id]),
          'name' => $batch->batch_code,
          'icon' => null,
        ],
        [
          'link' => '#',
          'name' => 'Worksheets',
          'icon' => null,
        ],
      ];
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="container-fluid pt-4">
      <div class="row">
        <div class="col-12">
          @livewire('worksheets.worksheet-manager', ['batch' => $batch])
        </div>
      </div>
    </div>
  </main>
@endsection

@section('script2')
<script>
(function() {
    setInterval(function() {
        const table = $('.polucon-custom-table');
        if (table.length && $.fn.DataTable && $.fn.DataTable.isDataTable(table)) {
            table.DataTable().destroy();
        }
    }, 150);

    window.getMethodSequencesApi = function() {
        return window.MethodSequences || window.methodSequences || null;
    };

    window.initMethodSequencesWidget = function() {
        const ms = window.getMethodSequencesApi();
        if (!ms) {
            return false;
        }

        const container = document.getElementById('method-sequences-container');
        if (!container) {
            return false;
        }

        const tabPanel = container.closest('.method-sequences-tab-panel');
        if (tabPanel && tabPanel.classList.contains('d-none')) {
            return false;
        }

        const tabsHaveContent = document.getElementById('sequence-tabs')?.children.length > 0;
        if (ms.initialized && tabsHaveContent) {
            return true;
        }

        if (ms.initialized && !tabsHaveContent) {
            ms.initialized = false;
            ms.eventsBound = false;
        }

        ms.init();
        return ms.initialized === true;
    };

    window.scheduleMethodSequencesInit = function(maxAttempts) {
        const attempts = maxAttempts || 10;
        let attempt = 0;

        const tryInit = function() {
            if (window.initMethodSequencesWidget()) {
                return;
            }

            attempt++;
            if (attempt < attempts) {
                setTimeout(tryInit, 200);
            }
        };

        tryInit();
    };
})();

document.addEventListener('livewire:init', function () {
    Livewire.on('init-method-sequences', function () {
        window.scheduleMethodSequencesInit(12);
    });

    Livewire.hook('morph.updated', function () {
        if (document.getElementById('method-sequences-container')) {
            window.scheduleMethodSequencesInit(5);
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var params = new URLSearchParams(window.location.search);
    if (params.get('tab') === 'method-sequences') {
        window.scheduleMethodSequencesInit(15);
    }
});
</script>
@endsection
