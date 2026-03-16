@php
    /** @var \App\Models\Procedures\ProcedureWorksheet $worksheet */
    /** @var \App\SampleHeader $batch */
    /** @var \Illuminate\Support\Collection|\App\CapturedResult[] $capturedResults */
    /** @var \Illuminate\Support\Collection|\App\Models\Procedures\ProcedureWorksheetStep[] $steps */
    /** @var \Illuminate\Support\Collection|\App\Models\Procedures\ProcedureConfigField[] $configFields */
    /** @var array<int, array<int, mixed>> $inputValues */
    /** @var array<int, array<int, mixed>> $configFieldValues */
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Procedure Worksheet - {{ $batch->batch_code }} - {{ $worksheet->name }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 12px;
            color: #4b5563;
        }
        .section-title {
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 4px 6px;
            vertical-align: top;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .small {
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Procedure Worksheet</div>
        <div class="subtitle">
            Batch: {{ $batch->batch_code }} |
            Worksheet: {{ $worksheet->name }} |
            Generated: {{ now()->format('Y-m-d H:i') }}
        </div>
    </div>

    @if($steps->count() > 0)
        <div class="section-title">Steps &amp; Recorded Values</div>
        <table>
            <thead>
                <tr>
                    <th>Step</th>
                    <th>Measurand</th>
                    <th>Equipment</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $firstCaptured = $capturedResults->first();
                    $firstId = $firstCaptured ? $firstCaptured->id : null;
                @endphp
                @foreach($steps as $step)
                    <tr>
                        <td>{{ $step->step }}</td>
                        <td class="small">{{ $step->measurands->pluck('name')->implode(', ') ?: '—' }}</td>
                        <td class="small">{{ $step->equipment?->name ?? '—' }}</td>
                        @php
                            $val = $firstId ? ($inputValues[$firstId][$step->id] ?? '') : '';
                        @endphp
                        <td class="small">{{ $val }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($configFields->count() > 0)
        <div class="section-title">Configurable Fields</div>
        <table>
            <thead>
                <tr>
                    <th>Field</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $firstCaptured = $capturedResults->first();
                    $firstId = $firstCaptured ? $firstCaptured->id : null;
                @endphp
                @foreach($configFields as $field)
                    <tr>
                        <td>{{ $field->label }}</td>
                        @php
                            $raw = $firstId ? ($configFieldValues[$firstId][$field->id] ?? '') : '';

                            // Normalise to array of scalar IDs when dataset-based
                            $ids = [];
                            if (is_array($raw)) {
                                $ids = $raw;
                            } elseif ($raw !== null && $raw !== '') {
                                $ids = explode(',', (string) $raw);
                            }
                            $ids = array_values(array_filter(array_map('trim', $ids), fn ($v) => $v !== ''));

                            if ($field->model_tied_to === 'methods' && ! empty($ids)) {
                                $methodIds = array_map('intval', $ids);
                                $labels = \App\AnalysisMethod::whereIn('id', $methodIds)->pluck('name')->toArray();
                                $display = implode(', ', $labels);
                            } elseif ($field->model_tied_to === 'users' && ! empty($ids)) {
                                $userIds = array_map('intval', $ids);
                                $labels = \App\User::whereIn('id', $userIds)->pluck('name')->toArray();
                                $display = implode(', ', $labels);
                            } else {
                                $display = is_array($raw) ? implode(', ', $raw) : (string) $raw;
                            }
                        @endphp
                        <td class="small">{{ $display }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>

