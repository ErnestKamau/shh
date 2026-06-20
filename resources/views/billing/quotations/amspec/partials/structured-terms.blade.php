@php
    $items = collect($structuredTerms['items'] ?? [])->filter(fn (array $item): bool => filled($item['value'] ?? ''));
@endphp
@if($items->isNotEmpty())
    <div class="amspec-structured-terms" style="margin-top: 14px;">
        <p class="amspec-terms-title" style="margin-bottom: 6px;">Commercial Terms:</p>
        <table class="amspec-meta-table" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
            @foreach($items as $item)
                <tr>
                    <td class="amspec-meta-label" style="width: 32%; vertical-align: top; padding: 3px 0;">{{ $item['label'] }}</td>
                    <td class="amspec-meta-value" style="vertical-align: top; padding: 3px 0;">: {{ $item['value'] }}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endif
