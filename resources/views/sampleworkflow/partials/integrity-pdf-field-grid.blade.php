@php
    $fields = is_array($fields ?? null) ? $fields : [];
    $empty = $empty ?? 'No information available.';
    $rows = array_chunk($fields, 2);
@endphp
@if($fields === [])
    <div class="empty-state muted">{{ $empty }}</div>
@else
    <table class="info-grid">
        @foreach($rows as $fieldRow)
            <tr>
                @foreach($fieldRow as $field)
                    <td>
                        <span class="field-label">{{ $field['label'] ?? '' }}</span>
                        <span class="field-value">{{ $field['value'] ?? '—' }}</span>
                    </td>
                @endforeach
                @if(count($fieldRow) === 1)
                    <td></td>
                @endif
            </tr>
        @endforeach
    </table>
@endif
