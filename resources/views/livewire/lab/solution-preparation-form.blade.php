<div class="solution-preparation-form container-fluid py-3 lab-surface-theme ls-admin-page" data-ls-type="plex">
    <div class="scd-hero card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <a href="{{ route('solutions-preparation-index') }}" class="scd-back-btn mb-3 d-inline-flex"><i class="mdi mdi-arrow-left"></i></a>
            <p class="scd-eyebrow mb-1">Preparation tracking</p>
            <h2 class="scd-title mb-0">Start new preparation</h2>
        </div>
    </div>

    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }}">{{ $message }}</div>
    @endif

    <div class="scd-panel card border-0 shadow-sm">
        <div class="card-body p-4">
            <form wire:submit.prevent="save">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="scd-label">Solution <span class="text-danger">*</span></label>
                        <x-searchable-select
                            wire:model.live="form.solution_id"
                            :options="$this->solutions->map(fn($sol) => ['id' => $sol->id, 'name' => $sol->name])"
                            placeholder="Search solutions..."
                            empty-label="Select solution..."
                            class="{{ $errors->has('form.solution_id') ? 'is-invalid' : '' }}"
                        />
                        @error('form.solution_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="scd-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" step="0.0001" wire:model="form.quantity_prepared" class="form-control scd-input @error('form.quantity_prepared') is-invalid @enderror">
                        @error('form.quantity_prepared')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="scd-label">UOM <span class="text-danger">*</span></label>
                        <x-searchable-select
                            wire:model="form.uom_id"
                            :options="$this->reportingUnits->map(fn($u) => ['id' => $u->id, 'name' => $u->name])"
                            placeholder="Search units..."
                            empty-label="Select unit..."
                        />
                    </div>
                    <div class="col-md-4">
                        <div class="form-check mt-4">
                            <input type="checkbox" wire:model.live="form.is_new_batch" class="form-check-input" id="is_new_batch">
                            <label class="form-check-label" for="is_new_batch">New batch</label>
                        </div>
                    </div>
                    @if($form['is_new_batch'])
                        <div class="col-md-4">
                            <label class="scd-label">Batch number</label>
                            <input type="text" wire:model="form.batch_number" class="form-control scd-input @error('form.batch_number') is-invalid @enderror">
                            @error('form.batch_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @endif
                    <div class="col-md-4">
                        <div class="form-check mt-4">
                            <input type="checkbox" wire:model="form.create_with_alternative" class="form-check-input" id="create_alt">
                            <label class="form-check-label" for="create_alt">Also create for alternative solution</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="scd-label">Notes</label>
                        <textarea wire:model="form.notes" class="form-control scd-input" rows="3"></textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="mdi mdi-play"></i> Start preparation</button>
                    <a href="{{ route('solutions-preparation-index') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    @include('livewire.lab.partials.scd-styles')
</div>
