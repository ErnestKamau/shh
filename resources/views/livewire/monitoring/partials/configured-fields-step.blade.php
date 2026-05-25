<div class="step-content cmt-configured-fields">
    <h5 class="mb-3">
        <i class="mdi mdi-text-box-check text-primary"></i>
        Configured Fields
    </h5>
    <p class="text-muted mb-4">
        Add worksheet fields that appear at the <strong>top</strong> or <strong>bottom</strong> of each monitoring log capture (same idea as mandatory worksheet fields on formula steps).
    </p>

    <div class="row mb-4">
        <div class="col-md-10">
            <input type="text" wire:model.live="configuredFieldSearch" class="form-control form-control--modern" placeholder="Search by label, value name, or help text…">
        </div>
        <div class="col-md-2">
            <button type="button" wire:click="clearConfiguredFieldSearch" class="btn btn-outline-secondary w-100">
                <i class="mdi mdi-refresh"></i> Clear
            </button>
        </div>
    </div>

    @php
        $placementSections = [
            'top' => ['title' => 'Top of worksheet', 'icon' => 'mdi-arrow-up-bold-box-outline', 'fields' => $this->topConfiguredFields],
            'bottom' => ['title' => 'Bottom of worksheet', 'icon' => 'mdi-arrow-down-bold-box-outline', 'fields' => $this->bottomConfiguredFields],
        ];
    @endphp

    @foreach($placementSections as $placement => $section)
        <div class="card shadow-sm border-0 mb-4 cmt-configured-placement">
            <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h6 class="mb-0">
                        <i class="mdi {{ $section['icon'] }} text-primary"></i>
                        {{ $section['title'] }}
                    </h6>
                    <p class="text-muted small mb-0">Fields render {{ $placement === 'top' ? 'above' : 'below' }} the reading structure during capture.</p>
                </div>
                <button type="button" wire:click="showCreateConfiguredFieldModalInit('{{ $placement }}')" class="btn btn-primary btn-sm">
                    <i class="mdi mdi-plus"></i> Add Field
                </button>
            </div>
            <div class="card-body p-0">
                @if(count($section['fields']) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover log-entry-data-table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">Order</th>
                                    <th>Label</th>
                                    <th>Field type</th>
                                    <th>Value name</th>
                                    <th>Configuration</th>
                                    <th>Required</th>
                                    <th class="text-end" style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($section['fields'] as $field)
                                    <tr wire:key="cf-{{ $field['id'] }}">
                                        <td><span class="badge bg-secondary">{{ $field['order'] }}</span></td>
                                        <td>
                                            <strong>{{ $field['label'] }}</strong>
                                            @if(!empty($field['help_text']))
                                                <br><small class="text-muted">{{ Str::limit($field['help_text'], 60) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info">
                                                {{ $this->configuredFieldTypeOptions[$field['field_type']] ?? $field['field_type'] }}
                                            </span>
                                        </td>
                                        <td><code>{{ $field['field_value_name'] }}</code></td>
                                        <td><small class="text-muted">{{ $this->configuredFieldTypeSummary($field) }}</small></td>
                                        <td>
                                            @if($field['is_required'])
                                                <span class="badge bg-danger">Required</span>
                                            @else
                                                <span class="badge bg-secondary">Optional</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--edit" wire:click="showEditConfiguredFieldModalInit('{{ $field['id'] }}')" title="Edit">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--delete" wire:click="showDeleteConfiguredFieldModalInit('{{ $field['id'] }}')" title="Delete">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 px-3">
                        <i class="mdi mdi-text-box-check-outline fa-2x text-muted mb-2 d-block"></i>
                        <p class="text-muted mb-2">No fields at the {{ $placement }} yet.</p>
                        <button type="button" wire:click="showCreateConfiguredFieldModalInit('{{ $placement }}')" class="btn btn-outline-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Add First Field
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>

@include('livewire.monitoring.partials.configured-fields-modals')

@include('layouts.registry.partials.rm-act-btn-styles')

<style>
    .cmt-configured-fields .log-entry-data-table .rm-act-btn {
        padding: 0.2rem 0.45rem;
        line-height: 1.2;
        border-radius: 6px;
    }
    .cmt-configured-placement .card-header {
        border-radius: 12px 12px 0 0;
    }
</style>
