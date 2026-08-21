{{-- Matches Inventory RFQ "Approval Configuration" table UX --}}
<div>
    <h5 class="card-title mb-3">
        Approvals Configuration
        <button type="button"
            class="btn btn-outline-primary btn-sm float-right"
            wire:click="openEditModal"
            title="Edit approval role">
            <i class="mdi mdi-key-plus"></i>
        </button>
    </h5>

    <p class="text-muted small mb-3">
        All active users with the configured role can approve quotations. The first approval wins.
    </p>

    <div class="table-responsive">
        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
            <thead>
                <tr>
                    <th style="width: 1%;">#</th>
                    <th nowrap>Title</th>
                    <th nowrap>Users</th>
                    <th style="width: 1%;"></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>
                        <strong>{{ $configTitle }}</strong>
                        <div class="small text-muted">Role: {{ $approverRole !== '' ? $approverRole : 'Not set' }}</div>
                    </td>
                    <td>
                        @forelse($approverUsers as $user)
                            <span class="my-small-text d-inline-block mr-2 mb-1">
                                <i class="mdi mdi-account"></i>
                                <strong>{{ $user->name }}</strong>
                                <small class="text-muted">&lt;{{ $user->email }}&gt;</small>
                            </span>
                            @if(! $loop->last), @endif
                        @empty
                            <span class="text-muted small">No active users currently have this role.</span>
                        @endforelse
                    </td>
                    <td nowrap>
                        <button type="button"
                            class="btn btn-primary btn-sm"
                            wire:click="openEditModal"
                            title="Edit">
                            <i class="mdi mdi-pencil-outline"></i>
                            <small class="hidden-sm-up">Edit</small>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($showEditModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.45); z-index: 1060;">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title mb-0">
                            <i class="mdi mdi-pencil-outline"></i> Edit Approval
                        </h4>
                        <button type="button" class="close" wire:click="closeEditModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label" for="quotation-approver-name-edit">Name</label>
                            <input type="text"
                                id="quotation-approver-name-edit"
                                class="form-control"
                                wire:model="editTitle"
                                placeholder="Name..."
                                required>
                            @error('editTitle')
                                <span class="text-danger small d-block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label class="control-label" for="quotation-approver-role-edit">Select Role</label>
                            <select id="quotation-approver-role-edit"
                                class="form-control"
                                wire:model="editRole"
                                required>
                                <option value="">Select role…</option>
                                @foreach($roleOptions as $role)
                                    <option value="{{ $role['name'] }}">{{ $role['name'] }}</option>
                                @endforeach
                            </select>
                            @error('editRole')
                                <span class="text-danger small d-block mt-1">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                Every user with this role can approve quotations across billing and Process Enquiry.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button"
                            class="btn btn-primary"
                            wire:click="save"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                <i class="mdi mdi-content-save"></i> Save
                            </span>
                            <span wire:loading wire:target="save">Saving…</span>
                        </button>
                        <button type="button" class="btn btn-default" wire:click="closeEditModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
