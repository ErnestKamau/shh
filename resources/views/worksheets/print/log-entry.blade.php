@extends('worksheets.print.layout')

@section('worksheet-print-content')
    <h2 class="worksheet-print-section-title">Log entry rows</h2>
    <table class="worksheet-print-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Sample code</th>
                <th>Test name</th>
                @foreach($columns as $column)
                    <th>{{ $column->label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($tableRows as $row)
                <tr>
                    <td>{{ $row['row_index'] + 1 }}</td>
                    <td>{{ $row['meta']['sample_code'] ?? '—' }}</td>
                    <td>{{ $row['meta']['test_name'] ?? '—' }}</td>
                    @foreach($columns as $column)
                        <td>{{ $row['cells'][$column->key] ?? '—' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 3 + $columns->count() }}" class="text-muted">No rows captured.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
