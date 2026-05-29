@php
    $pcrRows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
    $pcrCols = range(1, 12);
    $mode = $mode ?? 'preview';
    $wells = $wells ?? [];
    $stepId = $stepId ?? null;
    $interactive = in_array($mode, ['capture', 'configure'], true);
    $shellClass = $interactive ? 'pcr-plate-shell--interactive' : 'pcr-plate-shell--preview';
@endphp
<div class="pcr-plate-shell {{ $shellClass }}">
    <div class="table-responsive">
        <table class="pcr-plate-grid table table-bordered table-sm mb-0">
            <thead>
                <tr>
                    <th class="pcr-plate-grid__corner"></th>
                    @foreach($pcrCols as $col)
                        <th class="pcr-plate-grid__col-head">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($pcrRows as $row)
                    <tr>
                        <th class="pcr-plate-grid__row-head">{{ $row }}</th>
                        @foreach($pcrCols as $col)
                            @php
                                $wellKey = $row.$col;
                                $assignment = $wells[$wellKey] ?? null;
                                $kind = is_array($assignment) ? ($assignment['kind'] ?? '') : '';
                                $value = is_array($assignment) ? ($assignment['value'] ?? '') : '';
                                $kindClass = $kind !== '' ? 'pcr-well--'.$kind : 'pcr-well--empty';
                                if ($mode === 'configure') {
                                    $isActive = isset($pcrConfigActiveWell) && $pcrConfigActiveWell === $wellKey;
                                } else {
                                    $isActive = $mode === 'capture'
                                        && isset($activePcrWell, $activePcrStepId)
                                        && $activePcrWell === $wellKey
                                        && $activePcrStepId === $stepId;
                                }
                            @endphp
                            <td class="pcr-plate-grid__cell p-0">
                                @if($interactive)
                                    <button type="button"
                                            class="pcr-well {{ $kindClass }} {{ $isActive ? 'pcr-well--active' : '' }}"
                                            @if($mode === 'configure')
                                                wire:click="openPcrConfigWellEditor(@js($wellKey))"
                                            @else
                                                wire:click="openPcrWellEditor(@js($stepId), @js($wellKey))"
                                            @endif
                                            title="{{ $wellKey }}{{ $value !== '' ? ': '.$value : '' }}">
                                        <span class="pcr-well__coord">{{ $wellKey }}</span>
                                        @if($value !== '')
                                            <span class="pcr-well__value">{{ Str::limit($value, 8) }}</span>
                                        @endif
                                    </button>
                                @else
                                    <div class="pcr-well pcr-well--preview {{ $kindClass }}">
                                        <span class="pcr-well__coord">{{ $wellKey }}</span>
                                        @if($value !== '')
                                            <span class="pcr-well__value">{{ Str::limit($value, 8) }}</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
