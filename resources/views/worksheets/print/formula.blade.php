@extends('worksheets.print.layout')

@section('worksheet-print-content')
    @if($steps->isNotEmpty())
        <h2 class="worksheet-print-section-title">Formula steps</h2>
        <table class="worksheet-print-table">
            <thead>
                <tr>
                    <th>Sample code</th>
                    @foreach($steps as $step)
                        <th>{{ $step->label }}</th>
                    @endforeach
                    <th>Final result</th>
                </tr>
            </thead>
            <tbody>
                @foreach($captureRows as $row)
                    <tr>
                        <td>{{ $row['sample_code'] }}</td>
                        @foreach($steps as $step)
                            <td>{{ $row['step_values'][(string) $step->id] ?? '—' }}</td>
                        @endforeach
                        <td>{{ $row['final_result'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-muted">No formula steps configured.</p>
    @endif
@endsection
