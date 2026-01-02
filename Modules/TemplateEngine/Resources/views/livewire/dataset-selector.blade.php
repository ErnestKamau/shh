<div class="p-3 bg-light border rounded">
    <div class="form-group">
        <label>Select Table</label>
        <select wire:model.live="selectedTable" class="form-control">
            <option value="">-- Choose Table --</option>
            @foreach($tables as $table)
                <option value="{{ $table }}">{{ $table }}</option>
            @endforeach
        </select>
    </div>
    
    @if($selectedTable)
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Value Column (ID)</label>
                    <select wire:model.live="selectedValueColumn" class="form-control">
                        <option value="">-- Choose Column --</option>
                        @foreach($columns as $col)
                            <option value="{{ $col }}">{{ $col }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Label Column (Display Name)</label>
                    <select wire:model.live="selectedLabelColumn" class="form-control">
                        <option value="">-- Choose Column --</option>
                        @foreach($columns as $col)
                            <option value="{{ $col }}">{{ $col }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="mt-3 border-top pt-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                 <h6 class="small font-weight-bold mb-0">Filters</h6>
                 <button class="btn btn-xs btn-outline-primary" wire:click="addFilter">
                    <i class="fas fa-plus"></i> Add Filter
                 </button>
            </div>
            
            @if(count($filters) > 0)
                <div class="bg-white border rounded p-2">
                    @foreach($filters as $index => $filter)
                         <div class="d-flex align-items-center mb-2" wire:key="filter-{{ $index }}">
                             <select wire:model.blur="filters.{{ $index }}.column" class="form-control form-control-sm mr-1" style="width: 35%;">
                                 <option value="">Col...</option>
                                 @foreach($columns as $col)
                                     <option value="{{ $col }}">{{ $col }}</option>
                                 @endforeach
                             </select>
                             <select wire:model.blur="filters.{{ $index }}.operator" class="form-control form-control-sm mr-1" style="width: 20%;">
                                 <option value="=">=</option>
                                 <option value=">">></option>
                                 <option value="<"><</option>
                                 <option value=">=">>=</option>
                                 <option value="<="><=</option>
                                 <option value="LIKE">LIKE</option>
                                 <option value="!=">!=</option>
                             </select>
                             <input type="text" wire:model.blur="filters.{{ $index }}.value" class="form-control form-control-sm mr-1" placeholder="Value" style="width: 35%;">
                             <button class="btn btn-xs btn-outline-danger" wire:click="removeFilter({{ $index }})">
                                 <i class="fas fa-times"></i>
                             </button>
                         </div>
                    @endforeach
                </div>
            @else
                <p class="small text-muted mb-0">No filters applied. All records will be shown.</p>
            @endif
        </div>
        
        <p class="small text-info mt-3"><i class="fas fa-info-circle"></i> This will populate the dropdown with data from the selected table.</p>
    @endif
</div>
