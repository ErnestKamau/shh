@php
    $variant = $variant ?? 'secondary';
    $type = $action['type'] ?? 'href';
    $isDropdown = in_array($variant, ['dropdown', 'dropdown-danger'], true);
    // Use rv-action-item (not Bootstrap .dropdown-item) so theme :active burgundy cannot apply.
    $classes = match ($variant) {
        'primary' => 'rv-action-btn rv-action-btn--accent',
        'header-primary' => 'btn btn-sm btn-light rv-header-primary-btn',
        'dropdown', 'dropdown-danger' => 'rv-action-item',
        'danger' => 'rv-action-btn rv-action-btn--danger',
        default => 'rv-action-btn rv-action-btn--secondary',
    };
    $icon = (string) ($action['icon'] ?? 'mdi-circle-small');
@endphp

@if($type === 'wire')
    <button type="button"
        class="{{ $classes }}"
        wire:click="{{ $action['wire'] }}"
        wire:loading.attr="disabled"
        wire:target="{{ $action['wire'] }}">
        <i class="mdi {{ $icon }}{{ $isDropdown ? ' mr-2' : '' }}" aria-hidden="true"></i>{{ $action['label'] }}
    </button>
@elseif($type === 'create_samples')
    <a href="#"
        class="{{ $classes }} create-samples-btn"
        data-instance-id="{{ $instance->id }}">
        <i class="mdi {{ $icon }}{{ $isDropdown ? ' mr-2' : '' }}" aria-hidden="true"></i>{{ $action['label'] }}
    </a>
@elseif($type === 'form_post')
    @if($isDropdown)
        <form method="POST" action="{{ $action['href'] }}" class="request-view-actions-form"
            @if(!empty($action['confirm'])) onsubmit="return confirm(@json($action['confirm']));" @endif>
            @csrf
            {{-- type=button avoids global button[type=submit] burgundy theme hammer --}}
            <button type="button" class="{{ $classes }}"
                onclick="this.closest('form').requestSubmit()">
                <i class="mdi {{ $icon }} mr-2" aria-hidden="true"></i>{{ $action['label'] }}
            </button>
        </form>
    @else
        <form method="POST" action="{{ $action['href'] }}" class="rv-action-form"
            @if(!empty($action['confirm'])) onsubmit="return confirm(@json($action['confirm']));" @endif>
            @csrf
            <button type="submit" class="{{ $classes }}">
                <i class="mdi {{ $icon }}" aria-hidden="true"></i>
                <span>{{ $action['label'] }}</span>
            </button>
        </form>
    @endif
@elseif($type === 'form_delete')
    @if($isDropdown)
        <form method="POST" action="{{ $action['href'] }}" class="request-view-actions-form"
            @if(!empty($action['confirm'])) onsubmit="return confirm(@json($action['confirm']));" @endif>
            @csrf
            @method('DELETE')
            <button type="button" class="{{ $classes }}"
                onclick="this.closest('form').requestSubmit()">
                <i class="mdi {{ $icon }} mr-2" aria-hidden="true"></i>{{ $action['label'] }}
            </button>
        </form>
    @else
        <form method="POST" action="{{ $action['href'] }}" class="rv-action-form"
            @if(!empty($action['confirm'])) onsubmit="return confirm(@json($action['confirm']));" @endif>
            @csrf
            @method('DELETE')
            <button type="submit" class="{{ $classes }}">
                <i class="mdi {{ $icon }}" aria-hidden="true"></i>
                <span>{{ $action['label'] }}</span>
            </button>
        </form>
    @endif
@else
    <a href="{{ $action['href'] }}"
        class="{{ $classes }}"
        @if(!empty($action['target_blank'])) target="_blank" rel="noopener" @endif>
        <i class="mdi {{ $icon }}{{ $isDropdown ? ' mr-2' : '' }}" aria-hidden="true"></i>{{ $action['label'] }}
    </a>
@endif
