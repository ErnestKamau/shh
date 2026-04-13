<div class="crm-filter-bar" {{ $attributes }}>
    @if(isset($title))
        <h5 class="crm-filter-title">
            <i class="mdi mdi-filter-variant mr-2"></i>{{ $title }}
        </h5>
    @endif
    <div class="row align-items-center">
        {{ $slot }}
    </div>
</div>
