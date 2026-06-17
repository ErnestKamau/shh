@php
    $state = $state ?? [];
@endphp
<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;table-layout:fixed;width:100%;margin:0;">
    <tr>
        <td width="33%" align="center" style="border:none;padding:0;font-size:5pt;line-height:1;white-space:nowrap;overflow:hidden;">
            <span class="{{ ($state['L'] ?? false) ? 'trf-check trf-check-on trf-check-mini' : 'trf-check trf-check-off trf-check-mini' }}"></span><span class="trf-state-label">L</span>
        </td>
        <td width="34%" align="center" style="border:none;padding:0;font-size:5pt;line-height:1;white-space:nowrap;overflow:hidden;">
            <span class="{{ ($state['SS'] ?? false) ? 'trf-check trf-check-on trf-check-mini' : 'trf-check trf-check-off trf-check-mini' }}"></span><span class="trf-state-label">SS</span>
        </td>
        <td width="33%" align="center" style="border:none;padding:0;font-size:5pt;line-height:1;white-space:nowrap;overflow:hidden;">
            <span class="{{ ($state['S'] ?? false) ? 'trf-check trf-check-on trf-check-mini' : 'trf-check trf-check-off trf-check-mini' }}"></span><span class="trf-state-label">S</span>
        </td>
    </tr>
</table>
