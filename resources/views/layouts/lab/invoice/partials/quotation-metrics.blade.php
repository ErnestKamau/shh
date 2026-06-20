@php
    $metrics = $metrics ?? [];
    $cards = [
        [
            'key' => 'total',
            'label' => 'Total Quotations',
            'sublabel' => 'Active quotes (non-draft)',
            'icon' => 'mdi-file-document-multiple',
            'color' => '#3498db',
            'link' => route('quotation-index'),
        ],
        [
            'key' => 'in_preparation',
            'label' => 'In Preparation',
            'sublabel' => 'Being configured',
            'icon' => 'mdi-file-edit',
            'color' => '#17a2b8',
            'link' => route('quotation-index', ['stage' => 'Quote In Preparation']),
        ],
        [
            'key' => 'finalised',
            'label' => 'Finalised',
            'sublabel' => 'Quote complete',
            'icon' => 'mdi-check-decagram',
            'color' => '#28a745',
            'link' => route('quotation-index', ['stage' => 'Quote Complete']),
        ],
        [
            'key' => 'drafts',
            'label' => 'Drafts',
            'sublabel' => 'Saved as draft',
            'icon' => 'mdi-file-document-edit-outline',
            'color' => '#6c757d',
            'link' => route('quotation-index', ['stage' => 'Quote In Preparation']),
        ],
        [
            'key' => 'from_enquiry',
            'label' => 'From Enquiry',
            'sublabel' => 'Sample workflow quotes',
            'icon' => 'mdi-clipboard-flow',
            'color' => '#6f42c1',
            'link' => null,
        ],
        [
            'key' => 'sent_to_customer',
            'label' => 'Sent to Customer',
            'sublabel' => 'Delivered quotes',
            'icon' => 'mdi-email-check',
            'color' => '#20c997',
            'link' => route('quotation-index', ['stage' => 'Quote Complete']),
        ],
        [
            'key' => 'pdf_generated',
            'label' => 'PDF Generated',
            'sublabel' => 'Processed documents',
            'icon' => 'mdi-printer-check',
            'color' => '#fd7e14',
            'link' => route('quotation-index', ['stage' => 'Quote Complete']),
        ],
        [
            'key' => 'total_value_formatted',
            'label' => 'Finalised Value',
            'sublabel' => 'Sum of complete quotes',
            'icon' => 'mdi-cash-multiple',
            'color' => '#e83e8c',
            'link' => route('quotation-index', ['stage' => 'Quote Complete']),
            'is_formatted' => true,
        ],
    ];
@endphp

<div class="quotation-metrics-dashboard px-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-1"><i class="mdi mdi-chart-box-outline"></i> Quotation Overview</h5>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Key statistics across all quotation activity</p>
        </div>
        <p class="mb-0 text-primary" style="font-size: 0.95rem; font-weight: 500;">
            <i class="mdi mdi-calendar"></i> {{ $metrics['as_of'] ?? now()->format('l, F j, Y') }}
        </p>
    </div>

    <div class="row mb-3">
        @foreach(array_slice($cards, 0, 4) as $card)
            <div class="col-md-3 col-sm-6 mb-3">
                @include('layouts.lab.invoice.partials.quotation-metric-card', ['card' => $card, 'metrics' => $metrics])
            </div>
        @endforeach
    </div>

    <div class="row mb-2">
        @foreach(array_slice($cards, 4, 4) as $card)
            <div class="col-md-3 col-sm-6 mb-3">
                @include('layouts.lab.invoice.partials.quotation-metric-card', ['card' => $card, 'metrics' => $metrics])
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="quotation-metric-mini">
                <span class="text-muted">This month</span>
                <strong>{{ $metrics['this_month'] ?? 0 }}</strong>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="quotation-metric-mini">
                <span class="text-muted">Expiring soon (7d)</span>
                <strong class="{{ ($metrics['expiring_soon'] ?? 0) > 0 ? 'text-warning' : '' }}">{{ $metrics['expiring_soon'] ?? 0 }}</strong>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="quotation-metric-mini">
                <span class="text-muted">Analysis quotes</span>
                <strong>{{ $metrics['analysis'] ?? 0 }}</strong>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="quotation-metric-mini">
                <span class="text-muted">General quotes</span>
                <strong>{{ $metrics['general'] ?? 0 }}</strong>
            </div>
        </div>
        @if(($metrics['in_approval'] ?? 0) > 0)
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="quotation-metric-mini">
                <span class="text-muted">Legacy in approval</span>
                <strong>{{ $metrics['in_approval'] ?? 0 }}</strong>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    .quotation-metrics-dashboard .quotation-kpi-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-left: 4px solid var(--metric-color, #3498db);
        border-radius: 8px;
        padding: 1rem 1.15rem;
        height: 100%;
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .quotation-metrics-dashboard .quotation-kpi-card:hover {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        transform: translateY(-1px);
    }

    .quotation-metrics-dashboard .quotation-kpi-card a {
        color: inherit;
        text-decoration: none;
    }

    .quotation-metrics-dashboard .quotation-kpi-value {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0;
        line-height: 1.2;
    }

    .quotation-metrics-dashboard .quotation-kpi-label {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.15rem;
        color: #343a40;
    }

    .quotation-metrics-dashboard .quotation-kpi-sublabel {
        font-size: 0.78rem;
        color: #6c757d;
        margin-bottom: 0;
    }

    .quotation-metrics-dashboard .quotation-kpi-icon {
        font-size: 1.75rem;
        opacity: 0.85;
    }

    .quotation-metrics-dashboard .quotation-metric-mini {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9rem;
    }
</style>
