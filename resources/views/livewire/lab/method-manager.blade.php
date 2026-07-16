<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-cogs text-primary"></i>
                                Analysis Methods Management
                            </h2>
                            <p class="text-muted mb-0">Manage analysis methods for laboratory testing</p>
                        </div>
                        <button wire:click="showCreateMethodModal" class="btn btn-outline-primary method-add-btn">
                            <i class="mdi mdi-plus"></i> Add Method
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Alert -->
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0 text-muted">
                        <i class="mdi mdi-filter-variant"></i> Filter Options
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name, code, or description...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <div class="tag-select-container status-filter-container">
                                    <div class="tag-select-input status-filter-input">
                                        <select wire:model.live="statusFilter" class="tag-select-native no-select2">
                                            <option value="">All Status</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label for="perPage" class="form-label fw-bold">Show</label>
                                <div class="tag-select-container show-filter-container">
                                    <div class="tag-select-input status-filter-input">
                                        <select wire:model.live="perPage" id="perPage" class="tag-select-native no-select2">
                                            @foreach($perPageOptions as $option)
                                                <option value="{{ $option }}">{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">&nbsp;</label>
                                <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                                    <i class="mdi mdi-refresh"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Methods Table -->
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 15px;">
                <div class="card-body">
                    @if($this->methods->count() > 0)
                        <!-- Show Entries -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted">
                                    Showing {{ $this->methods->firstItem() ?? 0 }} to {{ $this->methods->lastItem() ?? 0 }} of {{ $this->methods->total() }} entries
                                </span>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>No</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Reference</th>
                                        <th>Based On Standard</th>
                                        <th>Elements</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->methods as $method)
                                        <tr>
                                            <td>{{ ($this->methods->currentPage() - 1) * $this->methods->perPage() + $loop->iteration }}</td>
                                            <td>{{ $method->code }}</td>
                                            <td>{{ $method->name }}</td>
                                            <td>{{ \Str::limit($method->description, 50) }}</td>
                                            <td>{{ $method->referencemethod->name ?? '-' }}</td>
                                            <td>{{ $method->basedOnStandard->code ?? '-' }}</td>
                                            <td>
                                                <span class="badge badge-info p-2">{{ number_format($method->analytes()->count()) }}</span>
                                            </td>
                                            <td>{{ $method->methodtype->value ?? 'Not Set' }}</td>
                                            <td>
                                                @if($method->active)
                                                    <span class="badge badge-success p-2">Active</span>
                                                @else
                                                    <span class="badge badge-secondary p-2">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('analysis-method', ['id' => $method->id]) }}" 
                                                       class="btn btn-sm rm-act-btn rm-act-btn--view" 
                                                       title="View Details">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <button wire:click="showEditMethodModal(@js($method->id))" 
                                                            class="btn btn-sm rm-act-btn rm-act-btn--edit" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteMethod(@js($method->id))" 
                                                            class="btn btn-sm rm-act-btn rm-act-btn--delete" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this method?')">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $this->methods->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-cogs text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No methods found</h5>
                            <p class="text-muted">Start by adding your first analysis method.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Method Modal -->
    @if($showMethodModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingMethod ? 'pencil' : 'plus' }}"></i>
                            {{ $editingMethod ? 'Edit' : 'Create' }} Analysis Method
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeMethodModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveMethod">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               wire:model="methodForm.name" 
                                               class="form-control @error('methodForm.name') is-invalid @enderror" 
                                               placeholder="Analysis Method Name...">
                                        @error('methodForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            Code <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               wire:model="methodForm.code" 
                                               class="form-control @error('methodForm.code') is-invalid @enderror" 
                                               placeholder="Analysis Method Code...">
                                        @error('methodForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">
                                    Description <span class="text-danger">*</span>
                                </label>
                                <textarea wire:model="methodForm.description" 
                                          class="form-control @error('methodForm.description') is-invalid @enderror" 
                                          rows="3"
                                          placeholder="Description..."></textarea>
                                @error('methodForm.description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-file-certificate-outline text-primary"></i> Based On Standard
                                        </label>
                                        <select wire:model.live="methodForm.based_on_standard_id" class="form-control livewire-select2 @error('methodForm.based_on_standard_id') is-invalid @enderror">
                                            <option value="">None (optional)</option>
                                            @foreach($standards as $standard)
                                                <option value="{{ $standard->id }}">
                                                    {{ $standard->code }} - {{ $standard->name }}{{ $standard->is_qc_standard ? ' (QC)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Catalogue/QC standard this method is based on. Separate from Reference Method.</small>
                                        @error('methodForm.based_on_standard_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">QC Schemes (method binding)</label>
                                        <select wire:model.live="methodForm.qc_scheme_ids" multiple class="form-control livewire-select2 @error('methodForm.qc_scheme_ids') is-invalid @enderror" style="min-height: 90px;">
                                            @foreach($qcSchemes as $scheme)
                                                <option value="{{ $scheme->id }}">{{ $scheme->name }} ({{ $scheme->code }})</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Resolved with the mode below against the parent standard’s schemes at Mark Complete.</small>
                                        @error('methodForm.qc_scheme_ids') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Binding Mode</label>
                                        <select wire:model="methodForm.qc_scheme_mode" class="form-control livewire-select2">
                                            <option value="override">Override</option>
                                            <option value="merge">Merge</option>
                                            <option value="additive">Additive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Priority</label>
                                        <input type="number" min="1" max="1000" wire:model="methodForm.qc_scheme_priority" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Condition: Equipment</label>
                                        <select wire:model="methodForm.qc_condition_equipment_id" class="form-control livewire-select2 @error('methodForm.qc_condition_equipment_id') is-invalid @enderror">
                                            <option value="">Any (no filter)</option>
                                            @foreach($equipmentItems as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }}@if($item->equipment_number) ({{ $item->equipment_number }})@endif</option>
                                            @endforeach
                                        </select>
                                        @error('methodForm.qc_condition_equipment_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Condition: Client</label>
                                        <select wire:model="methodForm.qc_condition_crm_customer_id" class="form-control livewire-select2 @error('methodForm.qc_condition_crm_customer_id') is-invalid @enderror">
                                            <option value="">Any (no filter)</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('methodForm.qc_condition_crm_customer_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Condition: Sample type</label>
                                        <select wire:model="methodForm.qc_condition_sample_type_id" class="form-control livewire-select2 @error('methodForm.qc_condition_sample_type_id') is-invalid @enderror">
                                            <option value="">Any (no filter)</option>
                                            @foreach($sampleTypes as $sampleType)
                                                <option value="{{ $sampleType->id }}">{{ $sampleType->name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Bindings apply only when all set conditions match the captured result / batch.</small>
                                        @error('methodForm.qc_condition_sample_type_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                @if($methodForm['method_type_id'] == $ltmMethodTypeId)
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold"><i class="mdi mdi-book-open-variant text-info"></i> Reference Method</label>
                                            <div x-data="{
                                                open: false,
                                                search: '',
                                                selected: @entangle('methodForm.reference_type_id').live,
                                                references: {{ json_encode(collect($referenceMethods)->map(fn($r) => ['id' => data_get($r, 'id'), 'name' => data_get($r, 'name', '')])->values()) }},
                                                get filteredReferences() {
                                                    if (!this.search) return this.references.slice(0, 50);
                                                    return this.references.filter(ref =>
                                                        ref.name.toLowerCase().includes(this.search.toLowerCase())
                                                    );
                                                },
                                                openDropdown() {
                                                    this.open = true;
                                                    this.$nextTick(() => this.$refs.searchInput?.focus());
                                                },
                                                closeDropdown() {
                                                    this.open = false;
                                                    this.search = '';
                                                },
                                                toggleDropdown() {
                                                    if (this.open) {
                                                        this.closeDropdown();
                                                        return;
                                                    }

                                                    this.openDropdown();
                                                },
                                                selectReference(refId) {
                                                    this.selected = refId;
                                                    this.closeDropdown();
                                                },
                                                getSelectedName() {
                                                    const ref = this.references.find(r => r.id == this.selected);
                                                    return ref ? ref.name : '';
                                                }
                                            }" class="searchable-dropdown-wrapper" wire:ignore>
                                                <div class="single-select-container" @click.stop="toggleDropdown()">
                                                    <input
                                                        x-ref="searchInput"
                                                        type="text"
                                                        x-model="search"
                                                        :placeholder="selected ? getSelectedName() : 'Search reference methods...'"
                                                        @focus="openDropdown()"
                                                        @click.stop="openDropdown()"
                                                        @keydown.escape.window="closeDropdown()"
                                                        class="form-control searchable-input-single"
                                                        autocomplete="off"
                                                    >
                                                    <i class="mdi mdi-chevron-down dropdown-arrow" :class="{ 'rotated': open }"></i>
                                                </div>

                                                <div x-show="open"
                                                     @click.away="closeDropdown()"
                                                     @click.stop
                                                     x-transition
                                                     class="dropdown-list">
                                                    <template x-if="filteredReferences.length > 0">
                                                        <div class="options-list">
                                                            <template x-for="ref in filteredReferences" :key="ref.id">
                                                                <div @click="selectReference(ref.id)" 
                                                                     class="option-item"
                                                                     :class="{ 'selected': selected == ref.id }">
                                                                    <i class="mdi mdi-check-circle text-primary" x-show="selected == ref.id"></i>
                                                                    <span x-text="ref.name"></span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>
                                                    <template x-if="filteredReferences.length === 0">
                                                        <div class="no-results">
                                                            <i class="mdi mdi-alert-circle-outline"></i>
                                                            <span>No references found</span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            @error('methodForm.reference_type_id') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="form-group mb-3">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           wire:model="methodForm.active" 
                                           class="form-check-input" 
                                           id="method_active">
                                    <label class="form-check-label" for="method_active">Active</label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeMethodModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveMethod">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <style>
        .method-add-btn {
            border-radius: 8px;
            padding: 0.48rem 1rem;
        }

        .modal.show {
            display: block !important;
        }
    
        body.modal-open {
            overflow: hidden;
        }
    
        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            max-height: calc(100vh - 200px);
        }
    
        .modal-body {
            scroll-behavior: smooth;
        }
    
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0,0,0,0.5);
        }
        </style>
    
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('method-modal-opened', () => {
                document.body.classList.add('modal-open');
                document.body.style.overflow = 'hidden';
            });
            
            Livewire.on('method-modal-closed', () => {
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
            });
        });
    </script>
    
    <style>
        .tag-select-container {
            position: relative;
            cursor: text;
        }

        .tag-select-input {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            min-height: 42px;
            padding: 6px 12px;
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .tag-select-input:hover {
            border-color: #007bff;
        }

        .tag-select-input:focus-within {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }

        .status-filter-input {
            padding: 0 12px;
        }

        .tag-select-native {
            width: 100%;
            display: block;
            border: none;
            box-shadow: none;
            background-color: transparent;
            padding: 10px 32px 10px 0;
            min-height: 42px;
            line-height: 1.5;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 6px center;
            background-size: 16px 16px;
            cursor: pointer;
        }

        .tag-select-native:focus {
            border: none;
            box-shadow: none;
            background-color: transparent;
            outline: none;
        }

        /* Single-Select Searchable Dropdown Styling */
        .searchable-dropdown-wrapper {
            position: relative;
        }

        .searchable-input-single {
            border: none;
            outline: none;
            box-shadow: none !important;
            padding: 4px 0;
            width: 100%;
        }
    
        .searchable-input-single:focus {
            border: none !important;
            box-shadow: none !important;
        }
    
        .single-select-container {
            position: relative;
            min-height: 45px;
            border: 1px solid #ced4da;
            border-radius: 12px;
            padding: 8px 40px 8px 12px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
    
        .single-select-container:hover {
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
        }
    
        .single-select-container:has(.searchable-input-single:focus) {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
    
        .dropdown-list {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            z-index: 1060;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }

        .options-list {
            padding: 8px;
            max-height: 300px;
            overflow-y: auto;
        }
    
        .option-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
        }
    
        .option-item:hover {
            background: #f8f9fa;
        }
    
        .option-item.selected {
            background: rgba(0, 123, 255, 0.08);
            font-weight: 500;
        }
    
        .option-item i {
            font-size: 18px;
        }
    </style>
</div>


