@extends('worksheets.print.layout')

@section('worksheet-print-content')
    <h2 class="worksheet-print-section-title">Results capture</h2>
    <table class="worksheet-print-table">
        <thead>
            <tr>
                <th>Parameter</th>
                @foreach($matrix['samples'] as $sample)
                    <th>{{ $sample['sample_detail_code'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($matrix['parameters'] as $parameter)
                <tr>
                    <td>{{ $parameter['label'] }}</td>
                    @foreach($matrix['samples'] as $sample)
                        @php
                            $cell = $matrix['cells'][$parameter['row_key']][$sample['sample_detail_code']] ?? null;
                        @endphp
                        <td>
                            @if($cell)
                                {{ trim(($cell['reporting_symbol'] ?? '').' '.($cell['result'] ?? '')) ?: '—' }}
                                @if(!empty($cell['remark']))
                                    <br><small>Remark: {{ $cell['remark'] }}</small>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
