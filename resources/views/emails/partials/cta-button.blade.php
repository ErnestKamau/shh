@php
    $label = $label ?? 'Open';
    $url = $url ?? '#';
@endphp
<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:22px 0;">
    <tr>
        <td align="center" style="border-radius:10px;background:#1d4ed8;">
            <a href="{{ $url }}" target="_blank"
               style="display:inline-block;padding:12px 28px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:10px;">
                {{ $label }}
            </a>
        </td>
    </tr>
</table>
