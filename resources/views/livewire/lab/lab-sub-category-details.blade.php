<div class="lab-sub-category-details-page container-fluid py-3 lab-surface-theme ls-admin-page" data-ls-type="plex">
    {{-- Page header --}}
    <div class="scd-hero card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div class="scd-hero__lead">
                    <a href="{{ route('stock_management_index') }}"
                       class="scd-back-btn"
                       title="Back to stock management">
                        <i class="mdi mdi-arrow-left"></i>
                    </a>
                    <div>
                        <p class="scd-eyebrow mb-1">Stock monitoring · Solution configuration</p>
                        <h2 class="scd-title mb-1">{{ $subCategory->name }}</h2>
                        <p class="scd-subtitle mb-0">
                            Manage preparation details and linked reagents for this item.
                        </p>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @if($subCategory->active)
                        <span class="scd-status scd-status--active">
                            <i class="mdi mdi-check-circle"></i> Active
                        </span>
                    @else
                        <span class="scd-status scd-status--inactive">
                            <i class="mdi mdi-close-circle"></i> Inactive
                        </span>
                    @endif
                    @if($subCategory->category)
                        <span class="scd-meta-pill">
                            <i class="mdi mdi-shape-outline"></i> {{ $subCategory->category->name }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show scd-alert" role="alert">
            <i class="mdi mdi-{{ $messageType === 'success' ? 'check-circle' : 'alert-circle' }} me-1"></i>
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    {{-- Configuration (always visible) --}}
    <section class="scd-config card border-0 shadow-sm mb-4">
        <div class="scd-config__head">
            <div class="scd-config__title-wrap">
                <span class="scd-config__icon"><i class="mdi mdi-tune-variant"></i></span>
                <div>
                    <h6 class="scd-config__title mb-0">Configuration</h6>
                    <p class="scd-config__subtitle mb-0">Core attributes for this solution item</p>
                </div>
            </div>
            @if(!$editingConfiguration)
                <button type="button" class="btn btn-outline-primary btn-sm scd-config__edit-btn" wire:click="startEditingConfiguration">
                    <i class="mdi mdi-pencil-outline"></i> Edit
                </button>
            @else
                <button type="button" class="btn btn-light btn-sm" wire:click="cancelEditingConfiguration">
                    Cancel
                </button>
            @endif
        </div>

        @if(!$editingConfiguration)
            <div class="scd-config__body">
                <div class="scd-info-grid">
                    <div class="scd-info-item">
                        <span class="scd-info-label">Name</span>
                        <span class="scd-info-value">{{ $subCategory->name }}</span>
                    </div>
                    <div class="scd-info-item">
                        <span class="scd-info-label">Category</span>
                        <span class="scd-info-value">{{ $subCategory->category->name ?? '—' }}</span>
                    </div>
                    <div class="scd-info-item">
                        <span class="scd-info-label">Unit of measure</span>
                        <span class="scd-info-value">{{ $subCategory->reportingUnit->name ?? '—' }}</span>
                    </div>
                    <div class="scd-info-item">
                        <span class="scd-info-label">Rate</span>
                        <span class="scd-info-value">{{ $subCategory->rate ?: '—' }}</span>
                    </div>
                    <div class="scd-info-item scd-info-item--wide">
                        <span class="scd-info-label">Description</span>
                        <span class="scd-info-value">{{ $subCategory->description ?: 'No description provided.' }}</span>
                    </div>
                </div>
                <div class="scd-config__media">
                    @if($subCategory->image)
                        <img src="{{ $subCategory->image }}" alt="{{ $subCategory->name }}" class="scd-config__thumb">
                    @else
                        <div class="scd-config__thumb-placeholder">
                            <i class="mdi mdi-image-outline"></i>
                            <span>No image</span>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="scd-config__body scd-config__body--form">
                    <form wire:submit.prevent="updateSubCategory" class="scd-form scd-config-form">
                        <div class="row g-3 mb-1">
                            <div class="col-md-4">
                                <label class="scd-label">Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       wire:model="subCategoryForm.name"
                                       class="form-control scd-input @error('subCategoryForm.name') is-invalid @enderror"
                                       placeholder="Item name">
                                @error('subCategoryForm.name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="scd-label">
                                    <i class="mdi mdi-shape text-primary"></i> Category <span class="text-danger">*</span>
                                </label>
                                <div class="tag-select-container @error('subCategoryForm.category_id') is-invalid @enderror"
                                     wire:click="openCategoryDropdown"
                                     wire:click.outside="closeCategoryDropdown">
                                    <div class="tag-select-input">
                                        @if($this->selectedCategory)
                                            <span class="tag-badge tag-badge--primary">
                                                {{ $this->selectedCategory->name }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="clearCategory"></i>
                                            </span>
                                        @endif
                                        <input type="text"
                                               wire:model.live.debounce.200ms="categorySearch"
                                               class="tag-input"
                                               placeholder="{{ $this->selectedCategory ? '' : 'Search categories...' }}"
                                               autocomplete="off">
                                    </div>
                                    @if($showCategoryDropdown)
                                        <div class="tag-dropdown">
                                            @forelse($this->filteredCategories as $category)
                                                <div class="tag-dropdown-item" wire:click.stop="selectCategory('{{ $category->id }}')">
                                                    {{ $category->name }}
                                                </div>
                                            @empty
                                                <div class="tag-dropdown-item text-muted">No categories found</div>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                                @error('subCategoryForm.category_id')
                                    <span class="scd-field-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="scd-label">
                                    <i class="mdi mdi-ruler text-primary"></i> Unit of measure <span class="text-danger">*</span>
                                </label>
                                <div class="tag-select-container @error('subCategoryForm.reporting_unit') is-invalid @enderror"
                                     wire:click="openReportingUnitDropdown"
                                     wire:click.outside="closeReportingUnitDropdown">
                                    <div class="tag-select-input">
                                        @if($this->selectedReportingUnit)
                                            <span class="tag-badge tag-badge--neutral">
                                                {{ $this->selectedReportingUnit->name }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="clearReportingUnit"></i>
                                            </span>
                                        @endif
                                        <input type="text"
                                               wire:model.live.debounce.200ms="reportingUnitSearch"
                                               class="tag-input"
                                               placeholder="{{ $this->selectedReportingUnit ? '' : 'Search units...' }}"
                                               autocomplete="off">
                                    </div>
                                    @if($showReportingUnitDropdown)
                                        <div class="tag-dropdown">
                                            @forelse($this->filteredReportingUnits as $unit)
                                                <div class="tag-dropdown-item" wire:click.stop="selectReportingUnit('{{ $unit->id }}')">
                                                    {{ $unit->name }}
                                                </div>
                                            @empty
                                                <div class="tag-dropdown-item text-muted">No units found</div>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                                @error('subCategoryForm.reporting_unit')
                                    <span class="scd-field-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-1">
                            <div class="col-md-4">
                                <label class="scd-label">Rate</label>
                                <input type="text"
                                       wire:model="subCategoryForm.rate"
                                       class="form-control scd-input @error('subCategoryForm.rate') is-invalid @enderror"
                                       placeholder="Rate of production">
                                @error('subCategoryForm.rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-8">
                                <label class="scd-label">Description</label>
                                <textarea wire:model="subCategoryForm.description"
                                          class="form-control scd-input @error('subCategoryForm.description') is-invalid @enderror"
                                          rows="2"
                                          placeholder="Optional description"></textarea>
                                @error('subCategoryForm.description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3 scd-config-form__image-row">
                            <div class="col-12">
                                <label class="scd-label">Image</label>
                                <div class="scd-config-form__image-wrap">
                                    <input type="file"
                                           wire:model="imageUpload"
                                           class="form-control scd-input @error('imageUpload') is-invalid @enderror"
                                           accept="image/*">
                                    @error('imageUpload')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror

                                    <div class="scd-image-preview mt-3">
                                        @if ($imageUpload)
                                            <img src="{{ $imageUpload->temporaryUrl() }}" alt="Preview" class="scd-preview-img">
                                        @elseif($subCategoryForm['image'])
                                            <img src="{{ $subCategoryForm['image'] }}" alt="{{ $subCategory->name }}" class="scd-preview-img">
                                        @else
                                            <div class="scd-preview-placeholder">
                                                <i class="mdi mdi-image-outline"></i>
                                                <span>No image</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-2 scd-config-form__actions">
                            <button type="button" class="btn btn-light" wire:click="cancelEditingConfiguration">Cancel</button>
                            <button type="submit" class="btn btn-primary scd-save-btn" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="updateSubCategory,imageUpload">
                                    <i class="mdi mdi-content-save"></i> Save changes
                                </span>
                                <span wire:loading wire:target="updateSubCategory,imageUpload">
                                    <span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...
                                </span>
                            </button>
                        </div>
                    </form>
            </div>
        @endif
    </section>

    {{-- Tabs: Reagents & Preparation templates --}}
    <nav class="scd-tab-nav mb-4" role="tablist" aria-label="Solution sections">
        <button type="button"
                class="scd-tab-nav__btn {{ $activeTab === 'reagents' ? 'scd-tab-nav__btn--active' : '' }}"
                wire:click="setActiveTab('reagents')"
                role="tab"
                aria-selected="{{ $activeTab === 'reagents' ? 'true' : 'false' }}">
            <i class="mdi mdi-flask-outline"></i>
            <span>Reagents</span>
            <span class="scd-tab-nav__badge">{{ $categoryItems->count() }}</span>
        </button>
        <button type="button"
                class="scd-tab-nav__btn {{ $activeTab === 'templates' ? 'scd-tab-nav__btn--active' : '' }}"
                wire:click="setActiveTab('templates')"
                role="tab"
                aria-selected="{{ $activeTab === 'templates' ? 'true' : 'false' }}">
            <i class="mdi mdi-format-list-numbered"></i>
            <span>Preparation templates</span>
        </button>
    </nav>

    <div class="scd-tab-panel">
        @if($activeTab === 'templates')
            @livewire('lab.solution-preparation-templates', ['subCategoryId' => $subCategoryId], key('templates-'.$subCategoryId))
        @elseif($activeTab === 'reagents')
            <div class="scd-panel card border-0 shadow-sm h-100">
                <div class="scd-panel__head scd-panel__head--split">
                    <div class="d-flex align-items-center gap-2">
                        <span class="scd-panel__icon scd-panel__icon--accent"><i class="mdi mdi-flask-outline"></i></span>
                        <div>
                            <h6 class="mb-0">Reagent items</h6>
                            <small class="text-muted">{{ $categoryItems->count() }} linked reagent{{ $categoryItems->count() === 1 ? '' : 's' }}</small>
                        </div>
                    </div>
                    <button wire:click="showAddItemModal" class="btn btn-primary btn-sm scd-add-btn">
                        <i class="mdi mdi-plus"></i> Add reagent
                    </button>
                </div>
                <div class="card-body p-0">
                    @if($categoryItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover scd-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 48px;">#</th>
                                        <th>Reagent</th>
                                        <th>Code</th>
                                        <th>Amount</th>
                                        <th>Unit</th>
                                        <th style="width: 110px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categoryItems as $item)
                                        <tr>
                                            <td class="text-muted">{{ $loop->iteration }}</td>
                                            <td class="fw-semibold">{{ $item->reagent->name ?? 'N/A' }}</td>
                                            <td><code class="scd-code">{{ $item->reagent->code ?? '—' }}</code></td>
                                            <td>{{ $item->amount_used }}</td>
                                            <td>{{ $item->unitMeasure->name ?? 'N/A' }}</td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button wire:click="showEditItemModal('{{ $item->id }}')"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--edit"
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteItem('{{ $item->id }}')"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                                            title="Delete"
                                                            onclick="return confirm('Delete this reagent item?')">
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
                        <div class="scd-empty text-center py-5 px-4">
                            <div class="scd-empty__icon">
                                <i class="mdi mdi-flask-empty-outline"></i>
                            </div>
                            <h6 class="mt-3 mb-1">No reagents linked yet</h6>
                            <p class="text-muted mb-3">Add reagents used in the preparation of this solution.</p>
                            <button wire:click="showAddItemModal" class="btn btn-outline-primary btn-sm">
                                <i class="mdi mdi-plus"></i> Add first reagent
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Reagent modal --}}
    @if($showItemModal)
        <div class="modal fade show d-block scd-modal-backdrop" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content scd-modal-content border-0 shadow">
                    <div class="modal-header scd-modal-header border-0">
                        <div>
                            <h5 class="modal-title mb-0">
                                <i class="mdi mdi-{{ $editingItem ? 'pencil' : 'plus-circle' }} text-primary"></i>
                                {{ $editingItem ? 'Edit' : 'Add' }} reagent item
                            </h5>
                            <small class="text-muted">Link an inventory reagent to this configuration</small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeItemModal"></button>
                    </div>
                    <div class="modal-body pt-0">
                        <form wire:submit.prevent="saveItem">
                            <div class="mb-3">
                                <label class="scd-label">Reagent <span class="text-danger">*</span></label>

                                @if($showCreateReagentForm)
                                    <div class="scd-create-reagent card border-0 bg-light">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0 small fw-semibold">
                                                    <i class="mdi mdi-plus-circle-outline text-primary"></i> New reagent
                                                </h6>
                                                <button type="button" class="btn btn-link btn-sm text-muted p-0" wire:click="cancelCreateReagentForm">Back to search</button>
                                            </div>
                                            <div class="mb-2">
                                                <label class="scd-label">Name <span class="text-danger">*</span></label>
                                                <input type="text"
                                                       wire:model="newReagentForm.name"
                                                       class="form-control form-control-sm scd-input @error('newReagentForm.name') is-invalid @enderror"
                                                       placeholder="Reagent name">
                                                @error('newReagentForm.name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="row g-2 mb-2">
                                                <div class="col-sm-6">
                                                    <label class="scd-label">Code</label>
                                                    <input type="text"
                                                           wire:model="newReagentForm.code"
                                                           class="form-control form-control-sm scd-input"
                                                           placeholder="Auto-generated if empty">
                                                </div>
                                                <div class="col-sm-6">
                                                    <label class="scd-label">Unit type</label>
                                                    <input type="text"
                                                           wire:model="newReagentForm.unit_type"
                                                           class="form-control form-control-sm scd-input"
                                                           placeholder="e.g. mL, g">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="scd-label">Description</label>
                                                <input type="text"
                                                       wire:model="newReagentForm.description"
                                                       class="form-control form-control-sm scd-input"
                                                       placeholder="Optional">
                                            </div>
                                            <button type="button"
                                                    class="btn btn-primary btn-sm w-100"
                                                    wire:click="createReagent"
                                                    wire:loading.attr="disabled">
                                                <span wire:loading.remove wire:target="createReagent">
                                                    <i class="mdi mdi-check"></i> Create &amp; select
                                                </span>
                                                <span wire:loading wire:target="createReagent">
                                                    <span class="spinner-border spinner-border-sm me-1"></span> Creating...
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <div class="tag-select-container @error('itemForm.reagent_id') is-invalid @enderror"
                                         wire:click="openReagentDropdown"
                                         wire:click.outside="closeReagentDropdown">
                                        <div class="tag-select-input">
                                            @if($this->selectedReagent)
                                                <span class="tag-badge tag-badge--success">
                                                    {{ $this->selectedReagent->name }}
                                                    <i class="mdi mdi-close-circle" wire:click.stop="clearReagent"></i>
                                                </span>
                                            @endif
                                            <input type="text"
                                                   wire:model.live.debounce.200ms="reagentSearch"
                                                   class="tag-input"
                                                   placeholder="{{ $this->selectedReagent ? '' : 'Search reagents...' }}"
                                                   autocomplete="off">
                                        </div>
                                        @if($showReagentDropdown)
                                            <div class="tag-dropdown">
                                                @forelse($this->filteredReagents as $reagent)
                                                    <div class="tag-dropdown-item" wire:click.stop="selectReagent('{{ $reagent->id }}')">
                                                        <span>{{ $reagent->name }}</span>
                                                        @if($reagent->code)
                                                            <small class="text-muted ms-1">({{ $reagent->code }})</small>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <div class="tag-dropdown-item text-muted py-2">No matching reagents</div>
                                                @endforelse
                                                @if($this->shouldOfferCreateReagent || trim($reagentSearch) !== '')
                                                    <div class="tag-dropdown-item tag-dropdown-item--action border-top"
                                                         wire:click.stop="openCreateReagentForm">
                                                        <i class="mdi mdi-plus-circle-outline text-primary"></i>
                                                        Create new reagent
                                                        @if(trim($reagentSearch) !== '')
                                                            <span class="text-muted">“{{ \Illuminate\Support\Str::limit($reagentSearch, 40) }}”</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mt-1">
                                        <button type="button" class="btn btn-link btn-sm text-primary px-0" wire:click="openCreateReagentForm">
                                            <i class="mdi mdi-plus"></i> Add new reagent to inventory
                                        </button>
                                    </div>
                                @endif
                                @error('itemForm.reagent_id')
                                    <span class="scd-field-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="scd-label">Amount used <span class="text-danger">*</span></label>
                                <input type="number"
                                       wire:model="itemForm.amount_used"
                                       class="form-control scd-input @error('itemForm.amount_used') is-invalid @enderror"
                                       step="0.01"
                                       min="0"
                                       placeholder="0.00">
                                @error('itemForm.amount_used')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label class="scd-label">Unit of measure <span class="text-danger">*</span></label>
                                <div class="tag-select-container @error('itemForm.unit_measure_id') is-invalid @enderror"
                                     wire:click="openItemUnitDropdown"
                                     wire:click.outside="closeItemUnitDropdown">
                                    <div class="tag-select-input">
                                        @if($this->selectedItemUnit)
                                            <span class="tag-badge tag-badge--neutral">
                                                {{ $this->selectedItemUnit->name }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="clearItemUnit"></i>
                                            </span>
                                        @endif
                                        <input type="text"
                                               wire:model.live.debounce.200ms="itemUnitSearch"
                                               class="tag-input"
                                               placeholder="{{ $this->selectedItemUnit ? '' : 'Search units...' }}"
                                               autocomplete="off">
                                    </div>
                                    @if($showItemUnitDropdown)
                                        <div class="tag-dropdown">
                                            @forelse($this->filteredItemUnits as $unit)
                                                <div class="tag-dropdown-item" wire:click.stop="selectItemUnit('{{ $unit->id }}')">
                                                    {{ $unit->name }}
                                                </div>
                                            @empty
                                                <div class="tag-dropdown-item text-muted">No units found</div>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                                @error('itemForm.unit_measure_id')
                                    <span class="scd-field-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer scd-modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="closeItemModal">Cancel</button>
                        <button type="button" class="btn btn-primary scd-save-btn" wire:click="saveItem" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveItem">
                                <i class="mdi mdi-content-save"></i> Save
                            </span>
                            <span wire:loading wire:target="saveItem">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
    .lab-sub-category-details-page {
        --scd-primary: #2563eb;
        --scd-primary-soft: #eff6ff;
        --scd-slate-50: #f8fafc;
        --scd-slate-100: #f1f5f9;
        --scd-slate-200: #e2e8f0;
        --scd-slate-500: #64748b;
        --scd-slate-800: #1e293b;
        --scd-radius: 14px;
        --scd-radius-sm: 10px;
    }

    .scd-hero {
        border-radius: var(--scd-radius);
        background: linear-gradient(135deg, #ffffff 0%, var(--scd-slate-50) 100%);
    }

    .scd-hero__lead {
        display: flex;
        align-items: flex-start;
        gap: 1.25rem;
    }

    .scd-hero__lead .scd-back-btn {
        flex-shrink: 0;
        margin-top: 0.15rem;
    }

    .scd-eyebrow {
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--scd-slate-500);
    }

    .scd-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--scd-slate-800);
        letter-spacing: -0.02em;
    }

    .scd-subtitle {
        color: var(--scd-slate-500);
        font-size: 0.95rem;
    }

    .scd-back-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: var(--scd-primary-soft);
        color: var(--scd-primary);
        text-decoration: none;
        transition: background 0.2s, transform 0.15s;
    }

    .scd-back-btn:hover {
        background: #dbeafe;
        color: #1d4ed8;
        transform: translateX(-2px);
    }

    /* Configuration section */
    .scd-config {
        border-radius: var(--scd-radius);
        overflow: hidden;
    }

    .scd-config__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        background: linear-gradient(180deg, #fff 0%, var(--scd-slate-50) 100%);
        border-bottom: 1px solid var(--scd-slate-200);
    }

    .scd-config__title-wrap {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .scd-config__icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: var(--scd-primary-soft);
        color: var(--scd-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .scd-config__title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--scd-slate-800);
    }

    .scd-config__subtitle {
        font-size: 0.82rem;
        color: var(--scd-slate-500);
    }

    .scd-config__edit-btn {
        border-radius: 10px;
        font-weight: 600;
    }

    .scd-config__body {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 1.5rem;
        padding: 1.5rem;
        align-items: start;
    }

    .scd-config__body--form {
        display: block;
        width: 100%;
    }

    .scd-config-form .scd-label {
        margin-bottom: 0.35rem;
    }

    .scd-config-form__image-row {
        padding-top: 0.25rem;
        border-top: 1px solid var(--scd-slate-200);
        margin-top: 0.25rem !important;
    }

    .scd-config-form__image-wrap {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        gap: 1.25rem;
    }

    .scd-config-form__image-wrap > input[type="file"] {
        flex: 1 1 280px;
        max-width: 420px;
    }

    .scd-config-form__image-wrap .scd-image-preview {
        margin-top: 0 !important;
        flex-shrink: 0;
    }

    .scd-config-form__actions {
        padding-top: 0.5rem;
        border-top: 1px solid var(--scd-slate-200);
    }

    @media (max-width: 768px) {
        .scd-config__body {
            grid-template-columns: 1fr;
        }
    }

    .scd-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem 1.5rem;
    }

    .scd-info-item--wide {
        grid-column: 1 / -1;
    }

    .scd-info-label {
        display: block;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--scd-slate-500);
        margin-bottom: 0.25rem;
    }

    .scd-info-value {
        font-size: 0.95rem;
        font-weight: 500;
        color: var(--scd-slate-800);
        line-height: 1.45;
    }

    .scd-config__thumb {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: var(--scd-radius-sm);
        border: 1px solid var(--scd-slate-200);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }

    .scd-config__thumb-placeholder {
        width: 120px;
        height: 120px;
        border-radius: var(--scd-radius-sm);
        border: 1px dashed var(--scd-slate-200);
        background: var(--scd-slate-50);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        color: var(--scd-slate-500);
        font-size: 0.8rem;
    }

    .scd-config__thumb-placeholder i {
        font-size: 1.75rem;
        opacity: 0.6;
    }

    /* Modern tab navigation */
    .scd-tab-nav {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 0.35rem;
        background: var(--scd-slate-100);
        border-radius: 12px;
        border: 1px solid var(--scd-slate-200);
    }

    .scd-tab-nav__btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 1.15rem;
        border: none;
        border-radius: 10px;
        background: transparent;
        color: var(--scd-slate-500);
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    }

    .scd-tab-nav__btn i {
        font-size: 1.1rem;
    }

    .scd-tab-nav__btn:hover {
        color: var(--scd-slate-800);
        background: rgba(255, 255, 255, 0.7);
    }

    .scd-tab-nav__btn--active {
        background: #fff;
        color: var(--scd-primary);
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }

    .scd-tab-nav__badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.35rem;
        height: 1.35rem;
        padding: 0 0.4rem;
        border-radius: 999px;
        background: var(--scd-primary-soft);
        color: #1d4ed8;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .scd-tab-nav__btn--active .scd-tab-nav__badge {
        background: var(--scd-primary);
        color: #fff;
    }

    .scd-tab-panel {
        min-height: 200px;
    }

    .scd-create-reagent {
        border-radius: var(--scd-radius-sm);
    }

    .lab-sub-category-details-page .tag-dropdown-item--action {
        color: var(--scd-primary);
        font-weight: 600;
        background: var(--scd-primary-soft);
    }

    .lab-sub-category-details-page .tag-dropdown-item--action:hover {
        background: #dbeafe;
    }

    .scd-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .scd-status--active {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .scd-status--inactive {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .scd-meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 0.8rem;
        background: var(--scd-slate-100);
        color: var(--scd-slate-800);
        border: 1px solid var(--scd-slate-200);
    }

    .scd-panel {
        border-radius: var(--scd-radius);
        overflow: hidden;
    }

    .scd-panel__head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 1rem 1.25rem;
        background: var(--scd-slate-50);
        border-bottom: 1px solid var(--scd-slate-200);
    }

    .scd-panel__head--split {
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .scd-panel__icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--scd-primary-soft);
        color: var(--scd-primary);
        font-size: 1.25rem;
    }

    .scd-panel__icon--accent {
        background: #f0fdf4;
        color: #15803d;
    }

    .scd-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--scd-slate-800);
        margin-bottom: 6px;
        letter-spacing: 0.01em;
    }

    .scd-input {
        border-radius: var(--scd-radius-sm);
        border: 1px solid var(--scd-slate-200);
        padding: 0.55rem 0.85rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .scd-input:focus {
        border-color: var(--scd-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .scd-field-error {
        display: block;
        font-size: 0.8rem;
        color: #dc3545;
        margin-top: 4px;
    }

    .scd-image-preview {
        border-radius: var(--scd-radius-sm);
        overflow: hidden;
        border: 1px dashed var(--scd-slate-200);
        background: var(--scd-slate-50);
    }

    .scd-preview-img {
        width: 100%;
        max-height: 160px;
        object-fit: cover;
        display: block;
    }

    .scd-preview-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 2rem;
        color: var(--scd-slate-500);
        font-size: 0.85rem;
    }

    .scd-preview-placeholder i {
        font-size: 2rem;
        opacity: 0.5;
    }

    .scd-save-btn {
        border-radius: var(--scd-radius-sm);
        font-weight: 600;
        padding: 0.55rem 1rem;
    }

    .scd-add-btn {
        border-radius: var(--scd-radius-sm);
        font-weight: 600;
    }

    .scd-table thead th {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--scd-slate-500);
        background: var(--scd-slate-50);
        border-bottom: 1px solid var(--scd-slate-200);
        padding: 0.75rem 1rem;
    }

    .scd-table tbody td {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-color: var(--scd-slate-100);
    }

    .scd-table tbody tr:hover {
        background: rgba(37, 99, 235, 0.04);
    }

    .scd-code {
        font-size: 0.8rem;
        background: var(--scd-slate-100);
        color: var(--scd-slate-800);
        padding: 2px 8px;
        border-radius: 6px;
    }

    .scd-empty__icon {
        width: 64px;
        height: 64px;
        margin: 0 auto;
        border-radius: 50%;
        background: var(--scd-slate-100);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: var(--scd-slate-500);
    }

    .rm-act-btn {
        border-radius: 8px;
        padding: 4px 8px;
        font-size: 12px;
    }

    .rm-act-btn--edit {
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        background: #eff6ff;
    }

    .rm-act-btn--edit:hover {
        background: #dbeafe;
    }

    .rm-act-btn--delete {
        border: 1px solid #fecaca;
        color: #b91c1c;
        background: #fef2f2;
    }

    .rm-act-btn--delete:hover {
        background: #fee2e2;
    }

    .scd-modal-backdrop {
        background-color: rgba(15, 23, 42, 0.45);
        overflow-y: auto;
    }

    .scd-modal-content {
        border-radius: var(--scd-radius);
    }

    .scd-modal-header,
    .scd-modal-footer {
        padding: 1.25rem 1.5rem;
    }

    .scd-alert {
        border-radius: var(--scd-radius-sm);
        border: none;
    }

    /* Tag select */
    .lab-sub-category-details-page .tag-select-container {
        position: relative;
        width: 100%;
        cursor: text;
    }

    .lab-sub-category-details-page .tag-select-input {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        min-height: 42px;
        padding: 6px 12px;
        background: #fff;
        border: 1px solid var(--scd-slate-200);
        border-radius: var(--scd-radius-sm);
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .lab-sub-category-details-page .tag-select-input:hover {
        border-color: #93c5fd;
    }

    .lab-sub-category-details-page .tag-select-input:focus-within {
        border-color: var(--scd-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .lab-sub-category-details-page .tag-input {
        flex: 1;
        min-width: 100px;
        border: none;
        outline: none;
        padding: 4px 0;
        font-size: 0.9rem;
        background: transparent;
    }

    .lab-sub-category-details-page .tag-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 500;
        white-space: nowrap;
    }

    .lab-sub-category-details-page .tag-badge--primary {
        background: var(--scd-primary-soft);
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .lab-sub-category-details-page .tag-badge--neutral {
        background: var(--scd-slate-100);
        color: var(--scd-slate-800);
        border: 1px solid var(--scd-slate-200);
    }

    .lab-sub-category-details-page .tag-badge--success {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .lab-sub-category-details-page .tag-badge i {
        cursor: pointer;
        font-size: 1rem;
        opacity: 0.75;
    }

    .lab-sub-category-details-page .tag-badge i:hover {
        opacity: 1;
    }

    .lab-sub-category-details-page .tag-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        z-index: 1100;
        background: #fff;
        border: 1px solid var(--scd-slate-200);
        border-radius: var(--scd-radius-sm);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.1);
        max-height: 220px;
        overflow-y: auto;
    }

    .lab-sub-category-details-page .tag-dropdown-item {
        padding: 10px 14px;
        cursor: pointer;
        font-size: 0.9rem;
        border-bottom: 1px solid var(--scd-slate-100);
        transition: background 0.15s;
    }

    .lab-sub-category-details-page .tag-dropdown-item:last-child {
        border-bottom: none;
    }

    .lab-sub-category-details-page .tag-dropdown-item:hover {
        background: var(--scd-slate-50);
    }

    .lab-sub-category-details-page .tag-select-container.is-invalid .tag-select-input {
        border-color: #dc3545;
    }

    .modal.show {
        display: block !important;
    }

    body.modal-open {
        overflow: hidden;
    }
    </style>

    <script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('item-modal-opened', () => {
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        });

        Livewire.on('item-modal-closed', () => {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
        });
    });
    </script>
</div>
