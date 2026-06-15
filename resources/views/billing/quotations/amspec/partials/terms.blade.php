<div class="amspec-terms">
    <p class="amspec-terms-title">Terms and Conditions:</p>
    <ol>
        @foreach($terms['items'] as $term)
            <li>{{ $term['text'] }}</li>
        @endforeach
    </ol>
</div>
