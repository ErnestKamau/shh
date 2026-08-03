@php
    $showHideAction = $showHideAction ?? false;
    $showClearAction = $showClearAction ?? false;
    $hideLabel = ! empty($card['is_hidden_from_rft']) ? 'Show' : 'Hide';
    $hideTitle = ! empty($card['is_hidden_from_rft'])
        ? 'Show on Request For Testing'
        : 'Hide from Request For Testing';
@endphp

@if (
    auth()->user()?->can('laboratory.components.rft form.view')
    || auth()->user()?->can('laboratory.components.rft form.edit')
    || $showClearAction
)
    <div class="rft-form-type-card-menu"
         x-data="{ open: false }"
         @click.outside="open = false"
         :class="{ 'is-open': open }">
        <button type="button"
                class="rft-card-menu-toggle"
                aria-label="More actions"
                aria-haspopup="true"
                :aria-expanded="open ? 'true' : 'false'"
                @click.stop="open = ! open">
            <i class="mdi mdi-dots-vertical" aria-hidden="true"></i>
        </button>
        <div class="rft-card-menu-dropdown" role="menu" @click.stop>
            @can('laboratory.components.rft form.view')
                <a href="{{ $card['view_url'] }}"
                   class="rft-card-menu-item"
                   role="menuitem">
                    <i class="mdi mdi-eye-outline" aria-hidden="true"></i>
                    <span>View</span>
                </a>
            @endcan
            @can('laboratory.components.rft form.edit')
                <a href="{{ $card['edit_url'] }}"
                   class="rft-card-menu-item"
                   role="menuitem">
                    <i class="mdi mdi-pencil-outline" aria-hidden="true"></i>
                    <span>Edit</span>
                </a>
                @if ($showHideAction)
                    <button type="button"
                            class="rft-card-menu-item"
                            role="menuitem"
                            wire:click="toggleFormHiddenFromRft('{{ $card['submission_form_id'] }}')"
                            title="{{ $hideTitle }}">
                        <i class="mdi {{ ! empty($card['is_hidden_from_rft']) ? 'mdi-eye-outline' : 'mdi-eye-off-outline' }}" aria-hidden="true"></i>
                        <span>{{ $hideLabel }}</span>
                    </button>
                @endif
            @endcan
            @if ($showClearAction)
                <button type="button"
                        class="rft-card-menu-item"
                        role="menuitem"
                        wire:click="clearSelectedSampleType">
                    <i class="mdi mdi-close" aria-hidden="true"></i>
                    <span>Clear</span>
                </button>
            @endif
        </div>
    </div>
@endif
