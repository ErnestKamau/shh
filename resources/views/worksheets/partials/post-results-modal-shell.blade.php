{{--
  Shared Post Results modal chrome (green theme).
  Required: $modalTitle
  Optional: $postingInProgress, $closeAction, $confirmAction, $confirmLabel, $hideFooter, $hideHeader
--}}
@if(empty($hideHeader))
<div class="modal-header text-white" style="background-color: rgba(40, 167, 69, 0.85);">
    <h5 class="modal-title">
        <i class="mdi mdi-upload"></i> {{ $modalTitle }}
    </h5>
    @if(empty($postingInProgress))
        <button type="button" class="close text-white" wire:click="{{ $closeAction ?? 'closePostResultsModal' }}">
            <span aria-hidden="true">&times;</span>
        </button>
    @endif
</div>
@endif

@if(empty($hideFooter))
    @if(empty($postingInProgress))
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" wire:click="{{ $closeAction ?? 'closePostResultsModal' }}">
                <i class="mdi mdi-close"></i> Cancel
            </button>
            <button type="button"
                    class="btn btn-success btn-sm"
                    wire:click="{{ $confirmAction ?? 'postResults' }}"
                    wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="{{ $confirmAction ?? 'postResults' }}">
                    <i class="mdi mdi-check"></i> {{ $confirmLabel ?? 'Yes, Post Results' }}
                </span>
                <span wire:loading wire:target="{{ $confirmAction ?? 'postResults' }}">
                    <i class="mdi mdi-loading mdi-spin"></i> Posting...
                </span>
            </button>
        </div>
    @endif
@endif
