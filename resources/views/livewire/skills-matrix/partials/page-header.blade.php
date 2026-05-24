@php
    $icon = $icon ?? 'mdi-view-dashboard';
@endphp

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 sm-page-header-card" style="border-radius: 15px;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                    <div>
                        <h2 class="mb-0">
                            <i class="mdi {{ $icon }} text-primary"></i>
                            {{ $title }}
                        </h2>
                        @if(! empty($subtitle))
                            <p class="text-muted mb-0">{{ $subtitle }}</p>
                        @endif
                    </div>
                    @isset($actions)
                        <div class="sm-page-header-actions d-flex align-items-center flex-wrap" style="gap: 8px 12px;">
                            {{ $actions }}
                        </div>
                    @endisset
                </div>
            </div>
        </div>
    </div>
</div>
