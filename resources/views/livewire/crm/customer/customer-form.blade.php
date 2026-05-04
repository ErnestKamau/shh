<div>
    <div class="modal fade show"
        style="display: flex; align-items: flex-start; overflow-y: auto; background-color: rgba(0,0,0,0.5); padding-top: 30px; padding-bottom: 30px;"
        wire:click.self="close" tabindex="-1" role="dialog" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="mdi mdi-{{ $customer ? 'pencil' : 'plus' }}"></i>
                        {{ $customer ? 'Edit' : 'Add' }} Customer
                    </h4>
                    <button type="button" class="close" wire:click="close" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        wire:model="name" placeholder="Name..." required />
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Postal Address</label>
                                    <textarea class="form-control @error('postal_address') is-invalid @enderror"
                                        wire:model="postal_address" placeholder="Postal Address..."></textarea>
                                    @error('postal_address') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Physical Address <span
                                            class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('physical_address') is-invalid @enderror"
                                        wire:model="physical_address" placeholder="Location..." required />
                                    @error('physical_address') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Website</label>
                                    <input type="text" class="form-control @error('website') is-invalid @enderror"
                                        wire:model="website" placeholder="Website..." />
                                    @error('website') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Country</label>
                                    <select class="form-control" wire:model="country_id">
                                        <option value="">-- Select Country --</option>
                                        @if(is_array($countries))
                                            @foreach($countries as $country)
                                                <option value="{{ $country->id ?? $country['id'] }}">
                                                    {{ $country->name ?? $country['name'] }}
                                                </option>
                                            @endforeach
                                        @else
                                            @foreach($countries as $country)
                                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('country_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                wire:model="lpos_required" />
                                            <label class="form-check-label">LPO Required?</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" wire:model="active" />
                                            <label class="form-check-label">Is Active?</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" wire:model="is_internal" />
                                            <label class="form-check-label">Internal Customer?</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label">Fax</label>
                                    <input type="text" class="form-control @error('fax') is-invalid @enderror"
                                        wire:model="fax" placeholder="Fax..." />
                                    @error('fax') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        wire:model="email" placeholder="Email..." required />
                                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Phone 1 <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control @error('telephone1') is-invalid @enderror"
                                        wire:model="telephone1" placeholder="Phone 1..." required />
                                    @error('telephone1') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Phone 2</label>
                                    <input type="tel" class="form-control @error('telephone2') is-invalid @enderror"
                                        wire:model="telephone2" placeholder="Phone 2..." />
                                    @error('telephone2') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Credit Days</label>
                                    <input type="number" name="credit_days"
                                        class="form-control @error('credit_days') is-invalid @enderror"
                                        wire:model="credit_days" />
                                    @error('credit_days') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Account Settings <span class="text-danger">*</span></label>
                                    <select class="form-control" wire:model="account_status" required>
                                        <option value="">-- Select Account Settings --</option>
                                        @forelse($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->key }}</option>
                                        @empty
                                            <option disabled>No account settings available</option>
                                        @endforelse
                                    </select>
                                    @error('account_status') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
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

</div>