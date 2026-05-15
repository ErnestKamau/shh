@php
    $sections = $this->equipmentDetailSections;
    $equipment = $equipment ?? $this->equipment;
@endphp

<div class="eq-details">
    <div class="eq-details-hero d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <p class="eq-details-kicker mb-1">{{ __('equipment.details') }}</p>
            <h5 class="eq-details-title mb-1">{{ $equipment->name }}</h5>
            <p class="eq-details-subtitle mb-0">
                <span class="eq-details-pill eq-details-pill--muted">{{ $equipment->equipment_number }}</span>
                @if($equipment->status)
                    <span class="eq-details-pill eq-details-pill--outline">{{ $equipment->status }}</span>
                @endif
            </p>
        </div>
        <button type="button" wire:click="showEditEquipmentModal" class="btn btn-primary btn-sm eq-details-edit-btn">
            <i class="mdi mdi-pencil"></i> {{ __('equipment.edit') }} {{ __('equipment.equipment') }}
        </button>
    </div>

    <div class="row g-4">
        @foreach($sections as $section)
            <div class="col-12 col-xl-6">
                <section class="eq-details-card h-100">
                    <header class="eq-details-card__header">
                        <span class="eq-details-card__icon" aria-hidden="true">
                            <i class="mdi {{ $section['icon'] }}"></i>
                        </span>
                        <h6 class="eq-details-card__title mb-0">{{ $section['title'] }}</h6>
                    </header>
                    <div class="eq-details-card__body">
                        <div class="eq-details-grid">
                            @foreach($section['fields'] as $field)
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
            </div>
        @endforeach
    </div>
</div>
