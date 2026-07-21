@php
    $rows = is_array($metaRows ?? null) ? $metaRows : [];
    $compact = (bool) ($compact ?? false);
@endphp

@if($rows !== [])
    <table class="table table-sm table-bordered worksheet-meta-table mb-3 {{ $compact ? 'worksheet-meta-table--compact' : '' }}">
        <thead>
            <tr>
                <th>Sample code</th>
                <th>Test name</th>
                <th>Analyst</th>
                <th>Method</th>
                <th>Unit</th>
                <th>Standard</th>
                <th>Standard limit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td>{{ $row['sample_code'] ?? '—' }}</td>
                    <td>{{ $row['test_name'] ?? '—' }}</td>
                    <td>{{ $row['analyst'] ?? '—' }}</td>
                    <td>{{ $row['method'] ?? '—' }}</td>
                    <td>{{ $row['unit'] ?? '—' }}</td>
                    <td>{{ $row['standard'] ?? '—' }}</td>
                    <td>{{ $row['standard_limit'] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
