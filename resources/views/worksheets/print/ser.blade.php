@extends('worksheets.print.layout')

@section('worksheet-print-content')
    <h2 class="worksheet-print-section-title">Run details</h2>
    <p>
        Sample: <strong>{{ $header->sample?->sample_code ?? '—' }}</strong><br>
        Date tested: <strong>{{ $header->date_tested?->format('Y-m-d') ?? '—' }}</strong>
    </p>

    @if($steps !== [])
        <h2 class="worksheet-print-section-title">Steps</h2>
        <table class="worksheet-print-table">
            <thead>
                <tr>
                    <th>Step</th>
                    <th>Measurand</th>
                    <th>Equipment</th>
                    <th>Analyst</th>
                </tr>
            </thead>
            <tbody>
                @foreach($steps as $step)
                    <tr>
                        <td>{{ $step['step_name'] }}</td>
                        <td>{{ $step['measurand_id'] ?? '—' }}</td>
                        <td>{{ $step['equipment_id'] ?? '—' }}</td>
                        <td>{{ $step['analyst'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($testKits !== [])
        <h2 class="worksheet-print-section-title">Test kits</h2>
        <table class="worksheet-print-table">
            <thead>
                <tr>
                    <th>Test name</th>
                    <th>Kit lot number</th>
                    <th>Wells used</th>
                </tr>
            </thead>
            <tbody>
                @foreach($testKits as $kit)
                    <tr>
                        <td>{{ $kit['test_name'] ?: '—' }}</td>
                        <td>{{ $kit['kit_lot_number'] ?: '—' }}</td>
                        <td>{{ $kit['wells_used'] ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
