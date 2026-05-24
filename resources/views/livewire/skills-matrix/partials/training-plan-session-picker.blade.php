@php
    /** @var \App\Models\SkillsMatrix\TrainingPlannerDetails $session */
    $isActive = (string) $selectedSessionId === (string) $session->id;
    $status = $this->trainingStatusMeta($session->status);
@endphp

<button
    type="button"
    class="sm-tp-session-card {{ $isActive ? 'is-active' : '' }}"
    wire:click="selectSession('{{ $session->id }}')"
    wire:key="tp-session-{{ $session->id }}"
>
    <div class="sm-tp-session-card-week">Wk {{ $session->week_no ?? '—' }}</div>
    <div class="sm-tp-session-card-body">
        <div class="sm-tp-session-card-title">{{ $this->sessionTitle($session) }}</div>
        <div class="sm-tp-session-card-meta">
            {{ $this->sessionArea($session) }}
            @if(isset($staffGapCount) && $staffGapCount > 0)
                · {{ $staffGapCount }} staff
            @endif
        </div>
        @if(filled($session->organizer_trainer))
            <div class="sm-tp-session-card-trainer">
                <i class="mdi mdi-account-tie-outline"></i> {{ $session->organizer_trainer }}
            </div>
        @endif
    </div>
    <span class="sm-training-status {{ $status['class'] }}">{{ $status['label'] }}</span>
</button>
