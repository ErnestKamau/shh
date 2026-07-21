<div class="card border-0 shadow-sm mb-4 fws-card">
    <div class="card-header fws-card-header">
        <i class="mdi mdi-clipboard-text-outline text-primary"></i>
        <span>Worksheet Run Details</span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6 col-md-2">
                <label class="fws-label">Date</label>
                <input type="date"
                       class="form-control form-control-sm"
                       wire:model.live.debounce.500ms="sharedWorksheetMeta.date"
                       @if($worksheetsReadOnly) disabled @endif>
            </div>
            <div class="col-6 col-md-2">
                <label class="fws-label">Lab No</label>
                <input type="text"
                       class="form-control form-control-sm"
                       wire:model.live.debounce.500ms="sharedWorksheetMeta.lab_no"
                       placeholder="{{ $batch->batch_code }}"
                       @if($worksheetsReadOnly) disabled @endif>
            </div>
            <div class="col-6 col-md-2">
                <label class="fws-label">Time In</label>
                <input type="time"
                       class="form-control form-control-sm"
                       wire:model.live.debounce.500ms="sharedWorksheetMeta.time_in"
                       @if($worksheetsReadOnly) disabled @endif>
            </div>
            <div class="col-6 col-md-2">
                <label class="fws-label">Done By</label>
                <select class="form-control form-control-sm no-select2"
                        wire:model.live="sharedWorksheetMeta.done_by_user_id"
                        @if($worksheetsReadOnly) disabled @endif>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="fws-label">Time Out</label>
                <input type="time"
                       class="form-control form-control-sm"
                       wire:model.live.debounce.500ms="sharedWorksheetMeta.time_out"
                       @if($worksheetsReadOnly) disabled @endif>
            </div>
            <div class="col-6 col-md-2">
                <label class="fws-label">Read Date</label>
                <input type="date"
                       class="form-control form-control-sm"
                       wire:model.live.debounce.500ms="sharedWorksheetMeta.read_date"
                       @if($worksheetsReadOnly) disabled @endif>
            </div>
            <div class="col-12 col-md-4">
                <label class="fws-label">Read By</label>
                <select class="form-control form-control-sm no-select2"
                        wire:model.live="sharedWorksheetMeta.read_by_user_id"
                        @if($worksheetsReadOnly) disabled @endif>
                    <option value="">Select...</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
