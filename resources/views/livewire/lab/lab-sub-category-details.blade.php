<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <h2 class="mb-0">
                        <i class="mdi mdi-format-list-bulleted text-primary"></i>
                        {{ $subCategory->name }}
                    </h2>
                    <p class="text-muted mb-0">Manage sub-category details and reagent items</p>
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

    <!-- Two Panel Layout -->
    <div class="row">
        <!-- Left Panel: Sub-Category Form -->
        <div class="col-xl-4 col-sm-12 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0">
                        <i class="mdi mdi-form-textbox"></i> Sub-Category Details
                    </h6>
                </div>
                <div class="card-body p-3">
                    <form wire:submit.prevent="updateSubCategory">
                        <div class="form-group mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   wire:model="subCategoryForm.name" 
                                   class="form-control @error('subCategoryForm.name') is-invalid @enderror">
                            @error('subCategoryForm.name') 
                                <div class="invalid-feedback">{{ $message }}</div> 
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Description</label>
                            <textarea wire:model="subCategoryForm.description" 
                                      class="form-control @error('subCategoryForm.description') is-invalid @enderror" 
                                      rows="3"></textarea>
                            @error('subCategoryForm.description') 
                                <div class="invalid-feedback">{{ $message }}</div> 
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select wire:model="subCategoryForm.category_id" 
                                    class="form-select @error('subCategoryForm.category_id') is-invalid @enderror">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('subCategoryForm.category_id') 
                                <div class="invalid-feedback">{{ $message }}</div> 
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" 
                                   wire:model="imageUpload" 
                                   class="form-control @error('imageUpload') is-invalid @enderror"
                                   accept="image/*">
                            @error('imageUpload') 
                                <div class="invalid-feedback">{{ $message }}</div> 
                            @enderror
                            
                            @if ($imageUpload)
                                <div class="mt-2">
                                    <img src="{{ $imageUpload->temporaryUrl() }}" class="img-thumbnail" style="max-width: 150px;">
                                </div>
                            @elseif($subCategoryForm['image'])
                                <div class="mt-2">
                                    <img src="{{ $subCategoryForm['image'] }}" class="img-thumbnail" style="max-width: 150px;">
                                </div>
                            @endif
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Unit of Measure <span class="text-danger">*</span></label>
                            <select wire:model="subCategoryForm.reporting_unit" 
                                    class="form-select @error('subCategoryForm.reporting_unit') is-invalid @enderror">
                                <option value="">Select Unit of Measure</option>
                                @foreach($reportingUnits as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('subCategoryForm.reporting_unit') 
                                <div class="invalid-feedback">{{ $message }}</div> 
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Rate</label>
                            <input type="text" 
                                   wire:model="subCategoryForm.rate" 
                                   class="form-control @error('subCategoryForm.rate') is-invalid @enderror"
                                   placeholder="Rate of production">
                            @error('subCategoryForm.rate') 
                                <div class="invalid-feedback">{{ $message }}</div> 
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="mdi mdi-content-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Panel: Reagent Items -->
        <div class="col-xl-8 col-sm-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0">
                        <i class="mdi mdi-flask"></i> Reagent Items
                    </h6>
                    <button wire:click="showAddItemModal" class="btn btn-sm btn-primary">
                        <i class="mdi mdi-plus"></i> Add Reagent
                    </button>
                </div>
                <div class="card-body p-3">
                    @if($categoryItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>No</th>
                                        <th>Reagent Name</th>
                                        <th>Reagent Code</th>
                                        <th>Amount Used</th>
                                        <th>Unit</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categoryItems as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->reagent->name ?? 'N/A' }}</td>
                                            <td>{{ $item->reagent->code ?? 'N/A' }}</td>
                                            <td>{{ $item->amount_used }}</td>
                                            <td>{{ $item->unitMeasure->name ?? 'N/A' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="showEditItemModal({{ $item->id }})" 
                                                            class="btn btn-sm btn-outline-warning" 
                                                            title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button wire:click="deleteItem({{ $item->id }})" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this item?')">
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
                        <div class="text-center py-4">
                            <i class="mdi mdi-flask-outline text-muted" style="font-size: 3rem;"></i>
                            <h6 class="text-muted mt-3">No reagent items added yet</h6>
                            <p class="text-muted">Click "Add Reagent" to start adding items.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reagent Item Modal -->
    @if($showItemModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto;">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingItem ? 'pencil' : 'plus' }}"></i>
                            {{ $editingItem ? 'Edit' : 'Add' }} Reagent Item
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeItemModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveItem">
                            <div class="form-group mb-3">
                                <label class="form-label">Reagent <span class="text-danger">*</span></label>
                                <select wire:model="itemForm.reagent_id" 
                                        class="form-select @error('itemForm.reagent_id') is-invalid @enderror">
                                    <option value="">Select Reagent</option>
                                    @foreach($reagents as $reagent)
                                        <option value="{{ $reagent->id }}">{{ $reagent->name }} ({{ $reagent->code }})</option>
                                    @endforeach
                                </select>
                                @error('itemForm.reagent_id') 
                                    <div class="invalid-feedback">{{ $message }}</div> 
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Amount Used <span class="text-danger">*</span></label>
                                <input type="number" 
                                       wire:model="itemForm.amount_used" 
                                       class="form-control @error('itemForm.amount_used') is-invalid @enderror"
                                       step="0.01"
                                       placeholder="Enter amount used">
                                @error('itemForm.amount_used') 
                                    <div class="invalid-feedback">{{ $message }}</div> 
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Unit of Measure <span class="text-danger">*</span></label>
                                <select wire:model="itemForm.unit_measure_id" 
                                        class="form-select @error('itemForm.unit_measure_id') is-invalid @enderror">
                                    <option value="">Select Unit</option>
                                    @foreach($reportingUnits as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                @error('itemForm.unit_measure_id') 
                                    <div class="invalid-feedback">{{ $message }}</div> 
                                @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeItemModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveItem">
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

    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .btn-close {
        background: transparent;
        border: 0;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        color: #000;
        opacity: .5;
    }

    .btn-close:hover {
        opacity: .75;
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
