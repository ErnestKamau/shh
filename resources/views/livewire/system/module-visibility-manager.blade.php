<div class="card shadow-sm border-0" id="module-visibility">
    <div class="card-header bg-white border-bottom">
        <h5 class="mb-0 text-dark">
            <i class="mdi mdi-view-dashboard text-primary"></i>
            Module Visibility
        </h5>
        <small class="text-muted">Control which modules appear on the home dashboard.</small>
    </div>

    <div class="card-body">
        @if(session()->has('success') || session()->has('error'))
            <div class="alert {{ session()->has('success') ? 'alert-success' : 'alert-danger' }} mb-3">
                <i class="mdi {{ session()->has('success') ? 'mdi-check-circle-outline' : 'mdi-alert-circle-outline' }}"></i>
                {{ session()->get('success') ?? session()->get('error') }}
            </div>
        @endif

        <div class="row">
            @foreach($modules as $moduleKey => $module)
                <div class="col-md-6 mb-3">
                    <div class="d-flex justify-content-between align-items-center p-3 border rounded bg-light">
                        <div>
                            <div class="font-weight-bold text-dark">{{ $module['name'] }}</div>
                            <small class="text-muted">{{ $module['route'] }}</small>
                        </div>
                        <div class="custom-control custom-switch">
                            <input
                                type="checkbox"
                                class="custom-control-input"
                                id="module-{{ $moduleKey }}"
                                wire:model.live="visibility.{{ $moduleKey }}"
                                {{ $moduleKey === 'settings' ? 'disabled' : '' }}
                            >
                            <label class="custom-control-label" for="module-{{ $moduleKey }}"></label>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3">
            @can('system.module-switching.edit')
                <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">
                        <i class="mdi mdi-content-save"></i> Save Module Settings
                    </span>
                    <span wire:loading wire:target="save">
                        <i class="mdi mdi-loading mdi-spin"></i> Saving...
                    </span>
                </button>
            @else
                <div class="alert alert-warning mb-0">
                    <i class="mdi mdi-alert-circle-outline"></i>
                    You can view module visibility but cannot change it. Missing permission: <strong>system.module-switching.edit</strong>
                </div>
            @endcan
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('module-visibility-save-failed', (payload) => {
            const message = payload?.message ?? 'Module visibility save failed (no message provided).';
            console.error('[ModuleVisibility] Save failed:', message);
        });

        Livewire.on('module-visibility-saved', () => {
            setTimeout(() => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }, 100);
        });
    });
</script>
