@php
    $filterFields = $filterFields ?? [];
    $startField = $startField ?? 'start_date';
    $endField = $endField ?? 'end_date';
@endphp

<div class="d-flex flex-wrap align-items-center lab-kpi-export-group">
    @foreach(['xlsx' => ['label' => 'Excel', 'icon' => 'mdi-file-excel', 'class' => 'btn-success'], 'csv' => ['label' => 'CSV', 'icon' => 'mdi-file-delimited', 'class' => 'btn-outline-primary']] as $format => $meta)
        <form method="GET" action="{{ $summaryRoute }}" class="d-inline">
            <input type="hidden" name="{{ $startField }}" value="{{ $startDate }}">
            <input type="hidden" name="{{ $endField }}" value="{{ $endDate }}">
            <input type="hidden" name="format" value="{{ $format }}">
            @foreach($filterFields as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
            <button type="submit" class="btn btn-sm {{ $meta['class'] }}">
                <i class="mdi {{ $meta['icon'] }}"></i> Summary {{ $meta['label'] }}
            </button>
        </form>

        <form method="GET" action="{{ $detailRoute }}" class="d-inline">
            <input type="hidden" name="{{ $startField }}" value="{{ $startDate }}">
            <input type="hidden" name="{{ $endField }}" value="{{ $endDate }}">
            <input type="hidden" name="format" value="{{ $format }}">
            @foreach($filterFields as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
            <button type="submit" class="btn btn-sm {{ $format === 'xlsx' ? 'btn-outline-success' : 'btn-outline-secondary' }}">
                <i class="mdi {{ $meta['icon'] }}"></i> Detail {{ $meta['label'] }}
            </button>
        </form>
    @endforeach

    @if(!empty($helper))
        <small class="text-muted ml-2 mb-2">{{ $helper }}</small>
    @endif
</div>
