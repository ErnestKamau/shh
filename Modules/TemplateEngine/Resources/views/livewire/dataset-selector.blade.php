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
        <p class="small text-info"><i class="fas fa-info-circle"></i> This will populate the dropdown with data from the selected table.</p>
    @endif
</div>
