@php
    $payload = $payload ?? [];
    $isVerification = ($kind ?? '') === 'verification';
@endphp

<div class="modal fade show d-block eq-notes-view-overlay" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog {{ $isVerification ? 'modal-lg' : 'modal-md' }} modal-dialog-centered eq-notes-view-dialog">
        <div class="modal-content eq-notes-view-shell border-0">
            <div class="modal-body eq-notes-view-body">
                <div class="eq-notes-view-frame {{ $isVerification ? 'eq-notes-view-frame--scroll' : '' }}">
                    <div class="eq-notes-view-frame__glow" aria-hidden="true"></div>

                    @if($isVerification)
                        <div class="eq-notes-view-section">
                            <p class="eq-notes-view-section__label">{{ __('equipment.procedure') }}</p>
                            <div class="eq-notes-view-content">{{ ($payload['procedure'] ?? '') !== '' ? $payload['procedure'] : '—' }}</div>
                        </div>
                        <div class="eq-notes-view-section">
                            <p class="eq-notes-view-section__label">{{ __('equipment.response') }}</p>
                            <div class="eq-notes-view-content">{{ ($payload['response'] ?? '') !== '' ? $payload['response'] : '—' }}</div>
                        </div>
                        <div class="eq-notes-view-section">
                            <p class="eq-notes-view-section__label">{{ __('equipment.remarks') }}</p>
                            <div class="eq-notes-view-content">{{ ($payload['remarks'] ?? '') !== '' ? $payload['remarks'] : '—' }}</div>
                        </div>
                    @else
                        <div class="eq-notes-view-content eq-notes-view-content--single">
                            {{ ($payload['text'] ?? '') !== '' ? $payload['text'] : '—' }}
                        </div>
                    @endif

                    <div class="eq-notes-view-footer">
                        <button type="button" class="btn eq-notes-view-btn" wire:click="closeLogNotesModal">
                            {{ __('equipment.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
