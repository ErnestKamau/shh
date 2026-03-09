<div class="header">
    <div class="header-info">
        <div class="header-left" style="line-height: 1.5;">
            <strong>{{ $company->name ?? 'Laboratory Name' }}</strong><br>
            {{ $company->street ?? 'Laboratory Street' }}<br>
            P.O BOX {{ $company->address ?? 'Laboratory Address' }}<br>
            {{ $company->location ?? 'Laboratory Location' }}<br>
            {{ $company->country->name ?? 'Laboratory Country' }}<br>
            Tel: {{ $company->telephone ?? 'N/A' }}<br>
            Fax: {{ $company->fax ?? 'N/A' }}<br>
            Email: {{ $company->email ?? 'N/A' }}
        </div>
        <div class="header-center">
            <img src="{{ $report_logo ?? '' }}" alt="Company Logo" class="company-logo">
        </div>
        <div class="header-right" style="line-height: 1.4;">
            <span>{{ $document_code ?? 'FM/QA/100' }}</span><br>
            <span>Revision {{ $revision_number ?? '2' }}</span><br>
            <span>Issue Date: {{ $issue_date ? date('d/m/Y', strtotime($issue_date)) : '16/03/2023' }}</span><br>
            <span>Report No: </span> {{ $batch->batch_code ?? 'N/A' }}<br>
            <span>Customer: </span> {{ $customer->name ?? 'N/A' }}<br>
            <span>Address: </span> {{ $customer->address ?? 'N/A' }}<br>
            <span>P.O BOX: </span> {{ $customer->postal_address ?? 'N/A' }}<br>
            <span>Cell : </span> {{ $customer->telephone1 ?? 'N/A' }}<br>
            <span>Email: </span> {{ $customer->email ?? 'N/A' }}
        </div>
    </div>
</div>