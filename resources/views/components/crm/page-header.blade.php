@props(['breadcrumbItems' => [], 'title' => '', 'subtitle' => null, 'icon' => null])

<div {{ $attributes->class(['crm-page-header']) }}>
    <x-bread-crumb :items="$breadcrumbItems"></x-bread-crumb>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 crm-page-header-card" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                        <div class="flex-grow-1" style="min-width: 220px;">
                            <h2 class="crm-page-title mb-0">
                                @if($icon ?? null)
                                    <i class="mdi {{ $icon }}"></i>
                                @endif
                                {{ $title }}
                            </h2>
                            @if(isset($subtitle) && $subtitle)
                                <p class="crm-page-subtitle mb-0 mt-2">{{ $subtitle }}</p>
                            @endif
                        </div>
                        @isset($actions)
                            @if(trim((string) $actions) !== '')
                                <div class="d-flex flex-wrap align-items-center justify-content-end flex-shrink-0" style="gap: 8px;">
                                    {{ $actions }}
                                </div>
                            @endif
                        @endisset
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
