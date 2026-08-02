{{--
    Searchable multi/single select combobox used by the sample configuration table.

    Required: $comboKey, $comboOptions, $comboSelected, $comboToggleMethod
    Optional: $comboToggleArgs, $comboRemoveMethod, $comboRemoveArgs, $comboRemoveValue,
              $comboPlaceholder, $comboDisabled, $comboDisabledPlaceholder, $comboChipsEmptyText,
              $comboOptionsEmptyText, $comboClass, $comboAriaLabel

    Option shape: ['id' => string, 'name' => string, 'title' => ?string, 'search' => ?string].
    $comboRemoveValue lets single-select callers clear the field instead of toggling the chip id.
--}}
@php
    $comboIsDisabled = (bool) ($comboDisabled ?? false);
    $comboQuote = fn ($value): string => is_int($value) || is_float($value)
        ? (string) $value
        : "'".addslashes((string) $value)."'";
    $comboToggleArgList = collect($comboToggleArgs ?? [])->map($comboQuote)->all();
    $comboRemoveName = $comboRemoveMethod ?? $comboToggleMethod;
    $comboRemoveArgList = collect($comboRemoveArgs ?? $comboToggleArgs ?? [])->map($comboQuote)->all();
    $comboRemoveFixedValue = $comboRemoveValue ?? null;

    $comboNormalize = function ($option): array {
        $id = (string) ($option['id'] ?? '');
        $label = trim((string) ($option['name'] ?? ''));
        $label = $label !== '' ? $label : $id;
        $title = trim((string) ($option['title'] ?? ''));

        return [
            'id' => $id,
            'label' => $label,
            'title' => $title !== '' ? $title : $label,
            'search' => strtolower(trim((string) ($option['search'] ?? $label))),
        ];
    };

    $comboOptionList = collect($comboOptions)
        ->map($comboNormalize)
        ->filter(fn (array $option): bool => $option['id'] !== '')
        ->values();
    $comboSelectedList = collect($comboSelected)
        ->map($comboNormalize)
        ->filter(fn (array $option): bool => $option['id'] !== '')
        ->values();
@endphp

<div
    class="acc-param-tags {{ $comboClass ?? '' }}"
    wire:key="combo-{{ $comboKey }}"
    x-data="{
        open: false,
        query: '',
        panelStyle: '',
        isDisabled() {
            /* Livewire morphs the disabled attribute without re-running x-data, so a
               cached flag would stay stale (analysis types enabling right after a
               sample type is picked). Read the live DOM instead. */
            const input = this.$refs.input;

            return input ? input.disabled : @js($comboIsDisabled);
        },
        openPanel() {
            if (this.isDisabled()) {
                this.open = false;

                return;
            }
            this.open = true;
            this.$nextTick(() => requestAnimationFrame(() => this.updatePosition()));
        },
        updatePosition() {
            const control = this.$refs.control;
            if (! control) {
                return;
            }
            const rect = control.getBoundingClientRect();
            const gap = 4;
            const edge = 8;
            const spaceBelow = window.innerHeight - rect.bottom - gap - edge;
            const spaceAbove = rect.top - gap - edge;
            const dropUp = spaceBelow < 160 && spaceAbove > spaceBelow;
            const maxHeight = Math.min(240, Math.max(120, dropUp ? spaceAbove : spaceBelow));
            const width = Math.max(220, rect.width);
            const left = Math.min(
                Math.max(edge, rect.left),
                Math.max(edge, window.innerWidth - width - edge)
            );
            // Anchor to the control edge so a short list stays flush (not floating
            // at a top computed for the full max-height).
            const placement = dropUp
                ? `bottom:${Math.max(edge, window.innerHeight - rect.top + gap)}px;top:auto`
                : `top:${rect.bottom + gap}px;bottom:auto`;
            this.panelStyle = [
                'position:fixed',
                placement,
                `left:${left}px`,
                `width:${width}px`,
                `max-height:${maxHeight}px`,
                'z-index:2050',
            ].join(';');
        },
        matchesQuery(el) {
            const q = this.query.trim().toLowerCase();
            return q === '' || (el.dataset.label || '').includes(q);
        },
        hasNoMatches() {
            const q = this.query.trim().toLowerCase();
            if (q === '') {
                return false;
            }
            const options = this.$refs.options ? this.$refs.options.querySelectorAll('[data-label]') : [];
            return options.length > 0 && ! Array.from(options).some((el) => (el.dataset.label || '').includes(q));
        },
        select(id) {
            this.query = '';
            Promise.resolve(
                this.$wire.call(@js($comboToggleMethod), {{ implode(', ', array_merge($comboToggleArgList, ['id'])) }})
            ).finally(() => this.$nextTick(() => this.updatePosition()));
        },
        closeIfOutside(event) {
            if (! this.open) {
                return;
            }
            const target = event.target;
            if (this.$refs.control?.contains(target) || this.$refs.panel?.contains(target)) {
                return;
            }
            this.open = false;
        },
        init() {
            this._reposition = () => { if (this.open) this.updatePosition(); };
            this._outside = (event) => this.closeIfOutside(event);
            window.addEventListener('resize', this._reposition);
            document.addEventListener('scroll', this._reposition, true);
            document.addEventListener('mousedown', this._outside, true);
        },
        destroy() {
            window.removeEventListener('resize', this._reposition);
            document.removeEventListener('scroll', this._reposition, true);
            document.removeEventListener('mousedown', this._outside, true);
        }
    }"
    @keydown.escape.window="open = false"
>
    <div
        class="acc-param-tags__control {{ $comboIsDisabled ? 'is-disabled' : '' }}"
        x-ref="control"
        @click="openPanel(); $refs.input?.focus()"
    >
        <input
            type="text"
            class="acc-param-tags__input"
            x-ref="input"
            aria-label="{{ $comboAriaLabel ?? 'Search options' }}"
            placeholder="{{ $comboIsDisabled ? ($comboDisabledPlaceholder ?? 'Unavailable…') : ($comboPlaceholder ?? 'Search…') }}"
            x-model="query"
            @focus="openPanel()"
            @click.stop="openPanel()"
            @input="openPanel()"
            @disabled($comboIsDisabled)
        >
        <div class="acc-param-tags__chips">
            @forelse($comboSelectedList as $comboChip)
                @php
                    $comboChipRemoveArgs = array_merge(
                        $comboRemoveArgList,
                        [$comboQuote($comboRemoveFixedValue ?? $comboChip['id'])],
                    );
                @endphp
                <span
                    class="acc-param-tags__chip"
                    wire:key="combo-chip-{{ $comboKey }}-{{ $comboChip['id'] }}"
                    title="{{ $comboChip['title'] }}"
                >
                    {{ $comboChip['label'] }}
                    <button
                        type="button"
                        class="acc-param-tags__remove"
                        title="Remove"
                        wire:click.stop="{{ $comboRemoveName }}({{ implode(', ', $comboChipRemoveArgs) }})"
                    >&times;</button>
                </span>
            @empty
                <span class="acc-param-tags__placeholder">{{ $comboChipsEmptyText ?? 'Nothing selected' }}</span>
            @endforelse
        </div>
    </div>

    <template x-teleport="body">
        <div
            class="acc-param-tags__panel acc-param-tags__panel--floating"
            x-ref="panel"
            x-show="open"
            x-cloak
            :style="panelStyle"
        >
            <div class="acc-param-tags__panel-hint">
                {{ $comboOptionList->count() }} available · type to search
            </div>
            <div class="acc-param-tags__options" x-ref="options">
                @forelse($comboOptionList as $comboOption)
                    <button
                        type="button"
                        class="acc-param-tags__option"
                        wire:key="combo-opt-{{ $comboKey }}-{{ $comboOption['id'] }}"
                        data-label="{{ $comboOption['search'] }}"
                        title="{{ $comboOption['title'] }}"
                        x-show="matchesQuery($el)"
                        @click="select(@js($comboOption['id']))"
                    >
                        {{ $comboOption['label'] }}
                    </button>
                @empty
                    <p class="acc-wizard-hint mb-0 px-2 py-2">
                        {{ $comboOptionsEmptyText ?? 'No options available.' }}
                    </p>
                @endforelse
                <p class="acc-wizard-hint mb-0 px-2 py-2" x-show="hasNoMatches()" x-cloak>
                    No matches for “<span x-text="query"></span>”.
                </p>
            </div>
        </div>
    </template>
</div>
