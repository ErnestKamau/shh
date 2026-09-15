<table class="logo-row" style="margin-bottom: 4px;">
    <tr>
        <td style="width: 45%;">
            @if(!empty($logo_path) && file_exists($logo_path))
                <img src="{{ $logo_path }}" style="height: 48px;" alt="AmSpec logo">
            @else
                <div class="logo-placeholder">Logo 1</div>
            @endif
        </td>
        <td style="width: 55%; text-align: right;">
            @if(!empty($logo_secondary_path) && file_exists($logo_secondary_path))
                <img src="{{ $logo_secondary_path }}" style="height: 48px;" alt="AmSpec graphic">
            @else
                <div class="logo-placeholder" style="display: inline-block; min-width: 80px;">Logo 2</div>
            @endif
        </td>
    </tr>
    <tr>
        <td class="company-block">
            <div class="company-name">{{ $company->name ?? 'AmSpec Middle East' }}</div>
            @if(!empty($company->address)){{ $company->address }}<br>@endif
            @if(!empty($company->po_box))PO Box: {{ $company->po_box }}@endif
        </td>
        <td class="contact-block">
            Tel: {{ $company->telephone ?? '' }}<br>
            Fax: {{ $company->fax ?? '' }}<br>
            Mobile: {{ $company->cell_phone ?? '' }}<br>
            Email: {{ $company->email ?? '' }}<br>
            @if(!empty($company->website))Website: {{ $company->website }}@endif
        </td>
    </tr>
</table>
