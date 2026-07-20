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
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Search</label>
                                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name, code, or description...">
                            </div>
                        </div>
                        <div class="col-md-2">
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
                                <label class="form-label fw-bold">Method Type</label>
                                <select wire:model.live="methodTypeFilter" class="form-control tag-select-native no-select2">
                                    <option value="">All Types</option>
                                    @foreach($methodTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
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

                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">
                                    Method Type <span class="text-danger">*</span>
                                </label>
                                <select wire:model.live="methodForm.method_type_id"
                                        class="form-control livewire-select2 @error('methodForm.method_type_id') is-invalid @enderror">
                                    <option value="">Select method type...</option>
                                    @foreach($methodTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->value }}</option>
                                    @endforeach
                                </select>
                                @error('methodForm.method_type_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="mdi mdi-file-certificate-outline text-primary"></i> Based On Standard
                                        </label>
                                        <x-searchable-select
                                            wire:model.live="methodForm.based_on_standard_id"
                                            :options="collect($standards)->map(fn($standard) => ['id' => $standard->id, 'name' => trim($standard->code . ' - ' . $standard->name . ($standard->is_qc_standard ? ' (QC)' : ''))])"
                                            placeholder="Search standards..."
                                            empty-label="None (optional)"
                                            class="{{ $errors->has('methodForm.based_on_standard_id') ? 'is-invalid' : '' }}"
                                        />
                                        <small class="text-muted">Catalogue/QC standard this method is based on. Separate from Reference Method.</small>
                                        @error('methodForm.based_on_standard_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">QC Schemes (method binding)</label>
                                        <select wire:model.live="methodForm.qc_scheme_ids" multiple class="form-control livewire-select2 @error('methodForm.qc_scheme_ids') is-invalid @enderror" data-placeholder="Select QC schemes...">
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
                                        <x-searchable-select
                                            wire:model="methodForm.qc_condition_equipment_id"
                                            :options="collect($equipmentItems)->map(fn($item) => ['id' => $item->id, 'name' => $item->name . ($item->equipment_number ? ' (' . $item->equipment_number . ')' : '')])"
                                            placeholder="Search equipment..."
                                            empty-label="Any (no filter)"
                                            class="{{ $errors->has('methodForm.qc_condition_equipment_id') ? 'is-invalid' : '' }}"
                                        />
                                        @error('methodForm.qc_condition_equipment_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Condition: Client</label>
                                        <x-searchable-select
                                            wire:model="methodForm.qc_condition_crm_customer_id"
                                            :options="collect($customers)->map(fn($customer) => ['id' => $customer->id, 'name' => $customer->name])"
                                            placeholder="Search clients..."
                                            empty-label="Any (no filter)"
                                            class="{{ $errors->has('methodForm.qc_condition_crm_customer_id') ? 'is-invalid' : '' }}"
                                        />
                                        @error('methodForm.qc_condition_crm_customer_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Condition: Sample type</label>
                                        <x-searchable-select
                                            wire:model="methodForm.qc_condition_sample_type_id"
                                            :options="collect($sampleTypes)->map(fn($sampleType) => ['id' => $sampleType->id, 'name' => $sampleType->name])"
                                            placeholder="Search sample types..."
                                            empty-label="Any (no filter)"
                                            class="{{ $errors->has('methodForm.qc_condition_sample_type_id') ? 'is-invalid' : '' }}"
                                        />
                                        <small class="text-muted">Bindings apply only when all set conditions match the captured result / batch.</small>
                                        @error('methodForm.qc_condition_sample_type_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                @if((string) ($methodForm['method_type_id'] ?? '') === (string) ($ltmMethodTypeId ?? ''))
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold"><i class="mdi mdi-book-open-variant text-info"></i> Reference Method</label>
                                            <x-searchable-select
                                                wire:model.live="methodForm.reference_type_id"
                                                :options="collect($referenceMethods)->map(fn($ref) => ['id' => $ref->id, 'name' => $ref->name])"
                                                placeholder="Search reference methods..."
                                                empty-label="Select reference method..."
                                            />
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
            const initMethodModalSelect2 = () => {
                if (!$.fn.select2) {
                    return;
                }

                const $modal = $('.modal.show');
                if (!$modal.length) {
                    return;
                }

                $modal.find('select.livewire-select2').each(function() {
                    const $el = $(this);

                    if ($el.data('select2')) {
                        try {
                            $el.select2('destroy');
                        } catch (e) {
                            // Livewire morph may have removed select2 markup already.
                        }
                    }

                    $el.select2({
                        placeholder: $el.data('placeholder') || $el.attr('placeholder') || 'Select an option',
                        width: '100%',
                        allowClear: !$el.prop('multiple'),
                        dropdownParent: $modal,
                    });

                    // select2 only fires jQuery events; dispatch a native change
                    // event so Livewire's wire:model picks up the new value.
                    $el.off('select2:select.livewireSync select2:unselect.livewireSync select2:clear.livewireSync')
                        .on('select2:select.livewireSync select2:unselect.livewireSync select2:clear.livewireSync', function () {
                            this.dispatchEvent(new Event('change', { bubbles: true }));
                        });
                });
            };

            Livewire.on('method-modal-opened', () => {
                document.body.classList.add('modal-open');
                document.body.style.overflow = 'hidden';
                setTimeout(initMethodModalSelect2, 50);
            });

            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => {
                    setTimeout(initMethodModalSelect2, 50);
                });
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
    </style>
</div>


