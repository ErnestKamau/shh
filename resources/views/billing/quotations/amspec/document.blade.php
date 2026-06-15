@if(!($stylesLoaded ?? false))
    @include('billing.quotations.amspec.partials.fonts')
    @include('billing.quotations.amspec.partials.styles')
@endif

@include('billing.quotations.amspec.content')
