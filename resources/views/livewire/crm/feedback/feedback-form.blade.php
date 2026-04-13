<div x-data="{
    initSelect2() {
        setTimeout(() => {
            let select = $('#receivedFromSelect');
            if (select.length) {
                // Destroy existing instance to prevent duplicates
                if (select.hasClass('select2-hidden-accessible')) {
                    select.select2('destroy');
                }

                select.select2({
                    placeholder: 'Select Customer',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: select.closest('.modal'),
                    closeOnSelect: true
                }).on('change', function (e) {
                    var data = $(this).val();
                    $wire.set('received_from', data);
                });

                // Initial value check
                let val = $wire.get('received_from');
                if (val) {
                    select.val(val).trigger('change');
                }
            }
        }, 100);
    }
}" x-init="initSelect2()">
    <template x-teleport="body">
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1"
            role="dialog" wire:click.self="close" wire:ignore.self>
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                            <i class="mdi mdi-{{ $feedbackId ? 'pencil' : 'plus' }}"></i>
                            {{ $feedbackId ? 'Edit' : 'Add' }} Feedback
                        </h4>
                        <button type="button" class="close" wire:click="close" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="form-group" wire:ignore>
                                <label class="control-label">Received From <span class="text-danger">*</span></label>
                                <select class="form-control @error('received_from') is-invalid @enderror"
                                    id="receivedFromSelect" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->name }}" {{ $received_from == $customer->name ? 'selected' : '' }}>
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('received_from') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label class="control-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date') is-invalid @enderror"
                                    wire:model="date" required />
                                @error('date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label class="control-label">Feedback <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('feedback') is-invalid @enderror"
                                    wire:model="feedback" rows="4" required></textarea>
                                @error('feedback') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" wire:model="isActive" />
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i> Save
                            </button>
                            <button type="button" class="btn btn-default" wire:click="close">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

</div>