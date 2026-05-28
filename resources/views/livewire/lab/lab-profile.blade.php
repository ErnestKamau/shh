<div class="container-fluid lab-profile-page">
    @if(!empty($message))
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    {{-- Page title --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0 lab-profile-page__title-card">
                <div class="card-body py-3 px-4">
                    <h2 class="mb-1 lab-profile-page__title">
                        <i class="mdi mdi-flask-outline text-primary"></i>
                        Lab Profile
                    </h2>
                    <p class="text-muted mb-0">Manage lab sections and decontamination areas for this facility.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Lab summary (compact) --}}
    @php
        $zoneLabel = '—';
        if ($lab->zone) {
            $zoneKey = trim((string) ($lab->zone->key ?? ''));
            $zoneValue = trim((string) ($lab->zone->value ?? ''));
            if ($zoneKey !== '' && $zoneValue !== '' && $zoneKey !== $zoneValue && ! str_contains($zoneValue, $zoneKey)) {
                $zoneLabel = $zoneKey.' — '.$zoneValue;
            } else {
                $zoneLabel = $zoneValue !== '' ? $zoneValue : $zoneKey;
            }
        }
    @endphp
    <div class="row mb-3">
        <div class="col-12">
            <div class="lab-info-compact">
                <div class="lab-info-compact__primary">
                    <div class="lab-info-compact__icon">
                        <i class="mdi mdi-flask"></i>
                    </div>
                    <div class="lab-info-compact__identity">
                        <h6 class="lab-info-compact__title mb-0">{{ $lab->name }}</h6>
                        <p class="lab-info-compact__subtitle mb-0">
                            {{ $lab->code }} · {{ $lab->directorate->name ?? 'No directorate' }}
                        </p>
                    </div>
                    <div class="lab-info-compact__badges">
                        <span class="lab-badge {{ $lab->active ? 'lab-badge--active' : 'lab-badge--inactive' }}">
                            {{ $lab->active ? 'Active' : 'Inactive' }}
                        </span>
                        <span class="lab-badge {{ $lab->is_external ? 'lab-badge--external' : 'lab-badge--internal' }}">
                            {{ $lab->is_external ? 'External' : 'Internal' }}
                        </span>
                        <span class="lab-badge lab-badge--info">
                            {{ $lab->labSections->count() }} Section{{ $lab->labSections->count() === 1 ? '' : 's' }}
                        </span>
                        <span class="lab-badge lab-badge--info">
                            {{ $lab->decontaminationAreas->count() }} Decon
                        </span>
                    </div>
                </div>
                <ul class="lab-info-compact__meta">
                    <li>
                        <i class="mdi mdi-map-marker-radius-outline" title="Zone"></i>
                        <span>{{ $zoneLabel }}</span>
                    </li>
                    <li>
                        <i class="mdi mdi-email-outline" title="Email"></i>
                        <span>{{ $lab->email ?? '—' }}</span>
                    </li>
                    <li>
                        <i class="mdi mdi-phone-outline" title="Phone"></i>
                        <span>{{ $lab->phone1 ?? '—' }}</span>
                    </li>
                    <li>
                        <i class="mdi mdi-crosshairs-gps" title="Location"></i>
                        <span>{{ $lab->location ?? '—' }}</span>
                    </li>
                    <li>
                        <i class="mdi mdi-barcode" title="Start sample no"></i>
                        <span>{{ $lab->start_sample_no ?? '—' }}</span>
                    </li>
                    @if(filled($lab->address))
                        <li>
                            <i class="mdi mdi-home-map-marker" title="Address"></i>
                            <span>{{ $lab->address }}</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    {{-- Tabs: sections & decontamination --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 lab-profile-page__tabs-card">
                <div class="card-header bg-white border-bottom-0 pt-3 px-3 pb-0">
                    <ul class="nav nav-tabs lab-profile-tabs border-0" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button type="button"
                                    class="nav-link lab-profile-tabs__link {{ $activeProfileTab === 'sections' ? 'active' : '' }}"
                                    wire:click="setActiveProfileTab('sections')"
                                    role="tab"
                                    @if($activeProfileTab === 'sections') aria-selected="true" @endif>
                                <i class="mdi mdi-layers-triple me-1"></i>
                                Lab Sections for {{ $lab->name }}
                                <span class="badge bg-light text-dark ms-1">{{ $lab->labSections->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button"
                                    class="nav-link lab-profile-tabs__link {{ $activeProfileTab === 'decontamination' ? 'active' : '' }}"
                                    wire:click="setActiveProfileTab('decontamination')"
                                    role="tab"
                                    @if($activeProfileTab === 'decontamination') aria-selected="true" @endif>
                                <i class="mdi mdi-spray me-1"></i>
                                Decontamination Areas
                                <span class="badge bg-light text-dark ms-1">{{ $lab->decontaminationAreas->count() }}</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4">
                    @if($activeProfileTab === 'sections')
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                            <div class="lab-profile-section-search flex-grow-1" style="max-width: 360px;">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="mdi mdi-magnify text-muted"></i>
                                    </span>
                                    <input type="text"
                                           wire:model.live.debounce.300ms="sectionSearch"
                                           class="form-control border-start-0"
                                           placeholder="Search sections by name, code, equipment...">
                                </div>
                            </div>
                            @can('laboratory.components.labs.edit')
                                <button type="button"
                                        class="btn btn-primary btn-sm section-add-btn"
                                        wire:click="showCreateLabSectionModal('{{ $lab->id }}')">
                                    <i class="mdi mdi-plus"></i> Add Lab Section
                                </button>
                            @endcan
                        </div>

                        <p class="text-muted small mb-3">
                            Track environmental analysis setup, expected values, and reporting units.
                            @if($sectionSearch !== '')
                                Showing {{ $this->filteredLabSections->count() }} of {{ $lab->labSections->count() }} section(s).
                            @endif
                        </p>

                        @include('livewire.lab.partials.lab-section-cards', [
                            'lab' => $lab,
                            'sections' => $this->filteredLabSections,
                            'sectionSearch' => $sectionSearch,
                            'embeddedInTab' => true,
                        ])
                    @else
                        @include('livewire.lab.partials.decontamination-areas', [
                            'lab' => $lab,
                            'embeddedInTab' => true,
                        ])
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('livewire.lab.partials.lab-section-modal')

    @include('livewire.lab.partials.lab-delete-modal', ['confirmMethod' => 'confirmDelete'])

    @include('livewire.lab.partials.lab-config-styles')

    <style>
        .lab-profile-page__title-card,
        .lab-profile-page__tabs-card {
            border-radius: 15px;
        }

        .lab-profile-page__title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
        }

        .lab-info-compact {
            border: 1px solid #e8eef7;
            border-radius: 12px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            padding: 0.75rem 1rem;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        .lab-info-compact__primary {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.65rem 1rem;
        }

        .lab-info-compact__icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eff6ff;
            color: #2563eb;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .lab-info-compact__identity {
            flex: 1;
            min-width: 140px;
        }

        .lab-info-compact__title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
        }

        .lab-info-compact__subtitle {
            font-size: 0.78rem;
            color: #64748b;
        }

        .lab-info-compact__badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            align-items: center;
        }

        .lab-info-compact__badges .lab-badge {
            padding: 4px 8px;
            font-size: 10px;
        }

        .lab-info-compact__meta {
            list-style: none;
            margin: 0.65rem 0 0;
            padding: 0.65rem 0 0;
            border-top: 1px solid #eef2f7;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 1.25rem;
        }

        .lab-info-compact__meta li {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.8rem;
            color: #334155;
            max-width: 100%;
        }

        .lab-info-compact__meta li i {
            color: #64748b;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .lab-info-compact__meta li span {
            font-weight: 500;
            word-break: break-word;
        }

        .lab-profile-tabs__link {
            border: none;
            border-bottom: 3px solid transparent;
            border-radius: 0;
            color: #64748b;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.65rem 1rem;
            margin-bottom: -1px;
            background: transparent;
        }

        .lab-profile-tabs__link:hover {
            color: #2563eb;
            border-color: transparent;
            background: #f8fafc;
        }

        .lab-profile-tabs__link.active {
            color: #1d4ed8;
            border-bottom-color: #2563eb;
            background: transparent;
        }

        .lab-profile-section-search .input-group-text,
        .lab-profile-section-search .form-control {
            border-color: #dbe3ef;
            min-height: 42px;
        }

        .lab-profile-section-search .form-control:focus {
            border-color: #93c5fd;
            box-shadow: none;
        }
    </style>
</div>
