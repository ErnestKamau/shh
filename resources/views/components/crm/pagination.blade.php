<div class="crm-pagination-wrap" {{ $attributes }}>
    @if(isset($summary))
        <div class="crm-pagination-summary text-muted small">{{ $summary }}</div>
    @endif
    <div class="crm-pagination">{{ $slot }}</div>
</div>
