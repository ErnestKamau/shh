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
                        <button wire:click="showCreateMethodModal" class="btn btn-primary">
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
                                <select wire:model.live="statusFilter" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Method Type</label>
                                <select wire:model.live="methodTypeFilter" class="form-select">
                                    <option value="">All Types</option>
                                    @foreach($methodTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->value }}</option>
                                    @endforeach
                                </select>
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
                            <div class="d-flex align-items-center">
                                <label for="perPage" class="form-label mb-0 me-2 text-muted">Show:</label>
                                <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm" style="width: auto;">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
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
                                                       class="btn btn-sm btn-outline-info mr-2" 
                                                       title="View Details">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <button wire:click="showEditMethodModal({{ $method->id }})" 
                                                            class="btn btn-sm btn-outline-warning mr-2" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteMethod({{ $method->id }})" 
                                                            class="btn btn-sm btn-outline-danger" 
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
                                        <label class="form-label fw-bold"><i class="mdi mdi-test-tube text-primary"></i> Method Type</label>
                                        <div x-data="{
                                            open: false,
                                            search: '',
                                            selected: @entangle('methodForm.method_type_id').live,
                                            types: {{ json_encode($methodTypes->map(fn($t) => ['id' => $t->id, 'value' => $t->value])->values()) }},
                                            get filteredTypes() {
                                                if (!this.search) return this.types;
                                                return this.types.filter(type => 
                                                    type.value.toLowerCase().includes(this.search.toLowerCase())
                                                );
                                            },
                                            selectType(typeId) {
                                                this.selected = typeId;
                                                this.open = false;
                                                this.search = '';
                                            },
                                            getSelectedName() {
                                                const type = this.types.find(t => t.id == this.selected);
                                                return type ? type.value : '';
                                            }
                                        }" class="searchable-dropdown-wrapper">
                                            <div class="single-select-container" @click="open = !open">
                                                <input 
                                                    type="text" 
                                                    x-model="search"
                                                    :placeholder="selected ? getSelectedName() : 'Search method types...'"
                                                    @focus="open = true"
                                                    class="form-control searchable-input-single"
                                                    autocomplete="off"
                                                >
                                                <i class="mdi mdi-chevron-down dropdown-arrow" :class="{ 'rotated': open }"></i>
                                            </div>

                                            <div x-show="open" 
                                                 @click.away="open = false"
                                                 x-transition
                                                 class="dropdown-list">
                                                <template x-if="filteredTypes.length > 0">
                                                    <div class="options-list">
                                                        <template x-for="type in filteredTypes" :key="type.id">
                                                            <div @click="selectType(type.id)" 
                                                                 class="option-item"
                                                                 :class="{ 'selected': selected == type.id }">
                                                                <i class="mdi mdi-check-circle text-primary" x-show="selected == type.id"></i>
                                                                <span x-text="type.value"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="filteredTypes.length === 0">
                                                    <div class="no-results">
                                                        <i class="mdi mdi-alert-circle-outline"></i>
                                                        <span>No types found</span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                        @error('methodForm.method_type_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                
                                @if($methodForm['method_type_id'] == $ltmMethodTypeId)
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold"><i class="mdi mdi-book-open-variant text-info"></i> Reference Method</label>
                                            <div x-data="{
                                                open: false,
                                                search: '',
                                                selected: @entangle('methodForm.reference_type_id').live,
                                                references: {{ json_encode($referenceMethods->map(fn($r) => ['id' => $r->id, 'name' => $r->name])->values()) }},
                                                get filteredReferences() {
                                                    if (!this.search) return this.references.slice(0, 50);
                                                    return this.references.filter(ref => 
                                                        ref.name.toLowerCase().includes(this.search.toLowerCase())
                                                    );
                                                },
                                                selectReference(refId) {
                                                    this.selected = refId;
                                                    this.open = false;
                                                    this.search = '';
                                                },
                                                getSelectedName() {
                                                    const ref = this.references.find(r => r.id == this.selected);
                                                    return ref ? ref.name : '';
                                                }
                                            }" class="searchable-dropdown-wrapper">
                                                <div class="single-select-container" @click="open = !open">
                                                    <input 
                                                        type="text" 
                                                        x-model="search"
                                                        :placeholder="selected ? getSelectedName() : 'Search reference methods...'"
                                                        @focus="open = true"
                                                        class="form-control searchable-input-single"
                                                        autocomplete="off"
                                                    >
                                                    <i class="mdi mdi-chevron-down dropdown-arrow" :class="{ 'rotated': open }"></i>
                                                </div>

                                                <div x-show="open" 
                                                     @click.away="open = false"
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
        /* Single-Select Searchable Dropdown Styling */
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


