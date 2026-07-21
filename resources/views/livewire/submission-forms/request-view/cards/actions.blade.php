@php
    $primary = $nextStepActions['primary'] ?? null;
    $secondary = $nextStepActions['secondary'] ?? [];
    $danger = $nextStepActions['danger'] ?? null;
@endphp
<aside class="rv-card rv-card--actions rv-card--actions-light">
    <header class="rv-card-header rv-card-header--actions">
        <span class="rv-card-tag">Next step</span>
    </header>
    <div class="rv-card-body rv-actions-body">
        @if($primary)
            @include('livewire.submission-forms.request-view.cards.action-button', [
                'action' => $primary,
                'variant' => 'primary',
            ])
        @endif

        @if(count($secondary) > 0)
            <ul class="rv-actions-list">
                @foreach($secondary as $action)
                    <li>
                        @include('livewire.submission-forms.request-view.cards.action-button', [
                            'action' => $action,
                            'variant' => 'secondary',
                        ])
                    </li>
                @endforeach
            </ul>
        @endif

        @if($danger)
            <div class="rv-actions-danger">
                @include('livewire.submission-forms.request-view.cards.action-button', [
                    'action' => $danger,
                    'variant' => 'danger',
                ])
            </div>
        @endif
    </div>
</aside>
