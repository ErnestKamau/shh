@props([
    'icon',
    'title',
    'description',
])

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                    <div>
                        <h2 class="mb-0">
                            <i class="mdi {{ $icon }} text-primary"></i>
                            {{ $title }}
                        </h2>
                        <p class="text-muted mb-0">{{ $description }}</p>
                    </div>
                    @isset($actions)
                        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                            {!! $actions !!}
                        </div>
                    @endisset
                </div>
            </div>
        </div>
    </div>
</div>
