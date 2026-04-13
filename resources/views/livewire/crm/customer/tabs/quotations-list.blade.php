<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:28px;height:28px;background:#eef2ff;">
                <i class="mdi mdi-file-settings text-primary" style="font-size:1rem;"></i>
            </span>
            <div>
                <small class="font-weight-bold text-dark" style="font-size:0.82rem;">Quotations</small>
                <small class="text-muted d-block" style="font-size:0.67rem;">Quote history and status</small>
            </div>
        </div>
    </div>

    <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
        <x-slot:header>
            <tr>
                <th>No</th>
                <th>Quote no</th>
                <th>Quote type</th>
                <th>Status</th>
                <th>Quote date</th>
                <th>Expiry date</th>
                <th>Prepared by</th>
                <th>Total</th>
                <th style="min-width: 80px;">Actions</th>
            </tr>
        </x-slot:header>
                    @forelse($this->quotes as $quote)
                        <tr wire:key="quote-{{ $quote->id }}">
                            <td>{{ ($this->quotes->currentPage() - 1) * $this->quotes->perPage() + $loop->iteration }}</td>
                            <td>{{ $quote->quote_number }}</td>
                            <td>{{ $quote->quotation_type }}</td>
                            <td>{{ $quote->status }}</td>
                            <td>{{ $quote->quote_date }}</td>
                            <td>{{ $quote->expiring_date }}</td>
                            <td>{{ $quote->creator }}</td>
                            <td style="text-align: right;">{{ number_format($quote->total_amount, 2) }}</td>
                            <td nowrap>
                                <x-crm.action-buttons>
                                    <a href="{{ route('add-qoute-details-view', ['id' => $quote->id]) }}"
                                        target="_blank" rel="noopener"
                                        class="btn crm-btn crm-btn-view btn-sm" title="View">
                                        <i class="mdi mdi-eye-outline"></i>
                                    </a>
                                </x-crm.action-buttons>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <x-crm.empty-state
                                    icon="mdi-file-settings"
                                    message="No quotations found for this client."
                                />
                            </td>
                        </tr>
                    @endforelse
    </x-crm.data-table>

    <x-crm.pagination :summary="'Showing ' . ($this->quotes->firstItem() ?? 0) . ' to ' . ($this->quotes->lastItem() ?? 0) . ' of ' . $this->quotes->total() . ' results'">
        {{ $this->quotes->links() }}
    </x-crm.pagination>
</div>
