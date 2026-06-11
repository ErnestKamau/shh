@include('billing.quotations.amspec.partials.brand-mark')

<table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 10px; width: 100%; table-layout: fixed; border-collapse: collapse;">
    <tr>
        <td style="width: 55%; vertical-align: top; word-wrap: break-word;">
            <div class="amspec-company-name">{{ $company->name ?? '' }}</div>
            <div class="amspec-contact-line amspec-company-address">
                {{ $company->address ?? '' }}<br>
                {{ $company->location ?? '' }}<br>
                {{ $company->street ?? '' }}
            </div>
        </td>
        <td style="width: 45%; vertical-align: top; word-wrap: break-word;" class="text-right amspec-contact-line">
            <div>Tel: {{ $company->telephone ?? '' }}</div>
            <div>Fax: {{ $company->fax ?? '' }}</div>
            <div>Mobile: {{ $company->cell_phone ?? '' }}</div>
            <div>Email: {{ $company->email ?? '' }}</div>
        </td>
    </tr>
</table>
