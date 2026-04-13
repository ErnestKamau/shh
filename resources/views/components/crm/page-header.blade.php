@props(['breadcrumbItems' => [], 'title' => '', 'subtitle' => null, 'icon' => null, 'actions' => null])

<div class="crm-page-header" {{ $attributes }}>
    <x-bread-crumb :items="$breadcrumbItems"></x-bread-crumb>
    <h2 class="crm-page-title">
        @if($icon ?? null)
            <i class="mdi {{ $icon }}"></i>
        @endif
        {{ $title }}
        @if(isset($actions))
            <div class="float-right">
                {{ $actions }}
            </div>
        @endif
    </h2>
    @if(isset($subtitle) && $subtitle)
        <p class="crm-page-subtitle">{{ $subtitle }}</p>
    @endif
</div>
