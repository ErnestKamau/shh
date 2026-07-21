@php
    $meta = is_array($metaSummary ?? null) ? $metaSummary : [];
    $fields = [
        'sample_code' => 'Sample code',
        'test_name' => 'Test name',
        'analyst' => 'Analyst',
        'method' => 'Method',
        'unit' => 'Unit',
        'standard' => 'Standard',
        'standard_limit' => 'Standard limit',
    ];
@endphp

<div class="worksheet-meta-bar alert alert-light border mb-3">
    <div class="row">
        @foreach($fields as $key => $label)
            <div class="col-md-6 col-lg-3 mb-2">
                <small class="text-muted text-uppercase d-block worksheet-meta-bar__label">{{ $label }}</small>
                <strong class="worksheet-meta-bar__value">{{ $meta[$key] ?? '—' }}</strong>
            </div>
        @endforeach
    </div>
</div>
