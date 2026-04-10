<div class="sync-status-indicator">
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($ticket)
        <div class="d-flex align-items-center">
            @if($syncStatus === 'synced')
                <span class="badge badge-success">
                    <i class="fas fa-check-circle"></i> Synced
                </span>
                @if($lastSyncedAt)
                    <small class="text-muted ml-2">
                        Last synced: {{ \Carbon\Carbon::parse($lastSyncedAt)->diffForHumans() }}
                    </small>
                @endif
            @elseif($syncStatus === 'pending')
                <span class="badge badge-warning">
                    <i class="fas fa-clock"></i> Sync Pending
                </span>
                <button wire:click="retrySync" class="btn btn-sm btn-link text-primary ml-2" wire:loading.attr="disabled"
                    wire:target="retrySync">
                    <i class="fas fa-sync-alt" wire:loading.class="fa-spin" wire:target="retrySync"></i>
                    Sync Now
                </button>
            @elseif($syncStatus === 'failed')
                <span class="badge badge-danger">
                    <i class="fas fa-exclamation-triangle"></i> Sync Failed
                </span>
                <button wire:click="retrySync" class="btn btn-sm btn-danger ml-2" wire:loading.attr="disabled"
                    wire:target="retrySync">
                    <i class="fas fa-redo" wire:loading.class="fa-spin" wire:target="retrySync"></i>
                    Retry
                </button>
                @if($syncError)
                    <small class="text-danger ml-2" title="{{ $syncError }}">
                        <i class="fas fa-info-circle"></i> {{ Str::limit($syncError, 50) }}
                    </small>
                @endif
            @else
                <span class="badge badge-secondary">
                    <i class="fas fa-times-circle"></i> Not Synced
                </span>
                <button wire:click="retrySync" class="btn btn-sm btn-primary ml-2" wire:loading.attr="disabled"
                    wire:target="retrySync">
                    <i class="fas fa-cloud-upload-alt" wire:loading.class="fa-spin" wire:target="retrySync"></i>
                    Sync to Developer
                </button>
            @endif

            @if($retrying)
                <span class="ml-2">
                    <i class="fas fa-spinner fa-spin"></i> Syncing...
                </span>
            @endif
        </div>
    @endif
</div>

<style>
    .sync-status-indicator {
        margin: 10px 0;
    }

    .sync-status-indicator .badge {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }

    .sync-status-indicator .btn-link {
        padding: 0;
        text-decoration: none;
    }

    .sync-status-indicator .btn-link:hover {
        text-decoration: underline;
    }
</style>