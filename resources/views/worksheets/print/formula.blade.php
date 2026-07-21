@extends('worksheets.print.layout')

@section('worksheet-print-content')
    @if(!empty($runDetails))
        <h2 class="worksheet-print-section-title">Worksheet run details</h2>
        <table class="worksheet-print-table mb-4">
            <tbody>
                <tr>
                    <th>Date</th>
                    <td>{{ $runDetails['date'] ?? '—' }}</td>
                    <th>Lab No</th>
                    <td>{{ $runDetails['lab_no'] ?? '—' }}</td>
                    <th>Time In</th>
                    <td>{{ $runDetails['time_in'] ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Done By</th>
                    <td>{{ $runDetails['done_by'] ?? '—' }}</td>
                    <th>Time Out</th>
                    <td>{{ $runDetails['time_out'] ?? '—' }}</td>
                    <th>Read Date</th>
                    <td>{{ $runDetails['read_date'] ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Read By</th>
                    <td colspan="5">{{ $runDetails['read_by'] ?? '—' }}</td>
                </tr>
            </tbody>
        </table>
    @endif

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
