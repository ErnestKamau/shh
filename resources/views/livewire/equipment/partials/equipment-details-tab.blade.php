@php
    $sections = $this->equipmentDetailSections;
    $activeSection = $this->activeEquipmentDetailSection;
    $equipment = $equipment ?? $this->equipment;
@endphp

<div class="eq-details d-flex flex-column">
    <div class="eq-details-hero">
        <p class="eq-details-kicker mb-2">{{ __('equipment.details') }}</p>
        <div class="eq-details-hero__row">
            <div class="eq-details-heading">
                <h5 class="eq-details-title mb-0">{{ $equipment->name }}</h5>
                <div class="eq-details-subtitle">
                    <span class="eq-details-pill eq-details-pill--muted">{{ $equipment->equipment_number }}</span>
                    @if($equipment->status)
                        <span class="eq-details-pill eq-details-pill--outline">{{ $equipment->status }}</span>
                    @endif
                </div>
            </div>
            <button type="button" wire:click="showEditEquipmentModal" class="btn btn-primary btn-sm eq-details-edit-btn">
                <i class="mdi mdi-pencil"></i> {{ __('equipment.edit') }} {{ __('equipment.equipment') }}
            </button>
        </div>
    </div>

    <div class="eq-details-layout row g-3 g-lg-4 align-items-stretch flex-grow-1">
        <div class="col-12 col-lg-3">
            <nav class="eq-details-section-nav" aria-label="{{ __('equipment.details') }}">
                <p class="eq-details-section-nav__label d-none d-lg-block">{{ __('equipment.details') }}</p>
                <div class="eq-details-section-nav__list" role="tablist">
                    @foreach($sections as $section)
                        @php
                            $sectionKey = $section['key'] ?? '';
                            $isActive = $activeSection && ($activeSection['key'] ?? '') === $sectionKey;
                            $fieldCount = count($section['fields'] ?? []);
                        @endphp
                        <button
                            type="button"
                            role="tab"
                            wire:click="setActiveDetailsSection('{{ $sectionKey }}')"
                            class="eq-details-section-tab {{ $isActive ? 'eq-details-section-tab--active' : '' }}"
                            aria-selected="{{ $isActive ? 'true' : 'false' }}"
                        >
                            <span class="eq-details-section-tab__icon" aria-hidden="true">
                                <i class="mdi {{ $section['icon'] }}"></i>
                            </span>
                            <span class="eq-details-section-tab__text">
                                <span class="eq-details-section-tab__title">{{ $section['title'] }}</span>
                                <span class="eq-details-section-tab__meta">{{ $fieldCount }} {{ __('equipment.fields') }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>
            </nav>
        </div>

        <div class="col-12 col-lg-9 eq-details-panel-col">
            @if($activeSection)
                <section
                    class="eq-details-panel"
                    role="tabpanel"
                    aria-labelledby="eq-details-section-{{ $activeSection['key'] ?? 'panel' }}"
                >
                    <header class="eq-details-panel__header">
                        <span class="eq-details-panel__icon" aria-hidden="true">
                            <i class="mdi {{ $activeSection['icon'] }}"></i>
                        </span>
                        <div>
                            <h6 class="eq-details-panel__title mb-0" id="eq-details-section-{{ $activeSection['key'] ?? 'panel' }}">
                                {{ $activeSection['title'] }}
                            </h6>
                            <p class="eq-details-panel__lead mb-0">
                                {{ count($activeSection['fields'] ?? []) }} {{ __('equipment.fields') }}
                            </p>
                        </div>
                    </header>
                    <div class="eq-details-panel__body">
                        <div class="eq-details-grid">
                            @foreach($activeSection['fields'] as $field)
                                @php
                                    $value = $field['value'] ?? null;
                                    $display = ($value === null || $value === '') ? '—' : $value;
                                    $isWide = ! empty($field['wide']);
                                    $isMultiline = ! empty($field['multiline']);
                                @endphp
                                <div class="eq-details-item {{ $isWide ? 'eq-details-item--wide' : '' }} {{ $isMultiline ? 'eq-details-item--multiline' : '' }}">
                                    <div class="eq-details-item__label">{{ $field['label'] }}</div>
                                    <div class="eq-details-item__value {{ ! empty($field['highlight']) ? 'eq-details-item__value--highlight' : '' }} {{ ! empty($field['mono']) ? 'eq-details-item__value--mono' : '' }}">
                                        @if(! empty($field['badge']))
                                            <span class="eq-details-badge {{ $field['badge_class'] ?? 'eq-details-badge--muted' }}">{{ $display }}</span>
                                        @elseif($isMultiline)
                                            <div class="eq-details-notes">{{ $display }}</div>
                                        @else
                                            {{ $display }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </div>
</div>
