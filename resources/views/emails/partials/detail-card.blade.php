@php
    $rows = $rows ?? [];
@endphp
@if(!empty($rows))
<div style="margin:18px 0;padding:16px 18px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;">
    @foreach($rows as $index => $row)
        <div style="{{ $index < count($rows) - 1 ? 'margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid #e5e7eb;' : '' }}">
            <p style="margin:0 0 4px 0;font-size:11px;letter-spacing:0.8px;text-transform:uppercase;color:#64748b;font-weight:700;">
                {{ $row['label'] ?? '' }}
            </p>
            <p style="margin:0;font-size:15px;line-height:1.5;color:#111827;font-weight:600;">
                {!! $row['value'] ?? '' !!}
            </p>
        </div>
    @endforeach
</div>
@endif
