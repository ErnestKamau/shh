<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h2 class="mb-0">
                            <i class="mdi {{ $icon ?? 'mdi-file-document' }} text-primary"></i>
                            {{ $title }}
                        </h2>
                        @if(!empty($description))
                            <p class="text-muted mb-0">{{ $description }}</p>
                        @endif
                    </div>
                    @isset($actions)
                        <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
                            {!! $actions !!}
                        </div>
                    @endisset
                </div>
            </div>
        </div>
    </div>
</div>
