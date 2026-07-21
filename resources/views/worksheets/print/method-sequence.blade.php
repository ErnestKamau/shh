@extends('worksheets.print.layout')

@section('worksheet-print-content')
    @if(!empty($sampleResults))
        <h2 class="worksheet-print-section-title">Sample results</h2>
        <table class="worksheet-print-table">
            <thead>
                <tr>
                    <th>Sample code</th>
                    <th>Test name</th>
                    <th>Analyst</th>
                    <th>Method</th>
                    <th>Unit</th>
                    <th>Standard</th>
                    <th>Standard limit</th>
                    <th>Result</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sampleResults as $row)
                    <tr>
                        <td>{{ $row['sample_code'] ?? '—' }}</td>
                        <td>{{ $row['test_name'] ?? '—' }}</td>
                        <td>{{ $row['analyst'] ?? '—' }}</td>
                        <td>{{ $row['method'] ?? '—' }}</td>
                        <td>{{ $row['unit'] ?? '—' }}</td>
                        <td>{{ $row['standard'] ?? '—' }}</td>
                        <td>{{ $row['standard_limit'] ?? '—' }}</td>
                        <td>{{ $row['result'] ?? '—' }}</td>
                        <td>{{ $row['remark'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-muted">Select a method sequence run with captured results to include detailed sample results in print output.</p>
    @endif
@endsection
