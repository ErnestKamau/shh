<div class="amspec-terms" style="margin-top: 18px;">
    <p class="amspec-terms-title">Terms and Conditions:</p>
    @if(! empty($terms['items']))
        <ol>
            @foreach($terms['items'] as $term)
                <li>{{ $term['text'] }}</li>
            @endforeach
        </ol>
    @endif
    @if(! empty($copy['terms_url'] ?? ''))
        <p style="margin-top: 8px; font-size: 10px;">
            Full terms and conditions: <a href="{{ $copy['terms_url'] }}">{{ $copy['terms_url'] }}</a>
        </p>
    @endif
</div>
