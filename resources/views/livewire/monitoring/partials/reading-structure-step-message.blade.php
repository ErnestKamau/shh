@if($readingStepMessage)
    <div class="alert alert-{{ $this->readingStepMessageAlertClass() }} alert-dismissible fade show mb-3" role="alert">
        {{ $readingStepMessage }}
        <button type="button" class="btn-close" wire:click="$set('readingStepMessage', '')"></button>
    </div>
@endif
