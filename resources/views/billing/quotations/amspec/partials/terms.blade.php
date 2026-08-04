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
        <p class="amspec-terms-link">
            Full terms and conditions: <a href="{{ $copy['terms_url'] }}">{{ $copy['terms_url'] }}</a>
        </p>
    @endif
</div>
