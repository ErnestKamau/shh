<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-account-lock text-primary"></i>
                                {{ __('personnel.locked_accounts') }}
                            </h2>
                            <p class="text-muted mb-0">{{ __('personnel.locked_accounts_overview') }}</p>
                        </div>
                        <div class="text-right">
                            <div class="small text-muted">{{ __('personnel.total_locked') }}</div>
                            <h4 class="mb-0 text-danger">{{ $lockedUsers->total() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($message)
        <div class="alert alert-{{ $messageType === 'error' ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="$set('message', '')">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="card tab-card">
        <div class="card-body">
            <div class="mb-3 ptm-toolbar-strip-wrap">
                <div class="ptm-toolbar-strip d-flex align-items-center flex-nowrap" style="width: 100%">
                    <div class="ptm-search-wrap" style="flex: 0 0 90%; max-width: 90%">
                        <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="{{ __('personnel.search_locked_accounts') }}">
                    </div>

                    <span class="ptm-show-label">{{ __('personnel.show') }}</span>
                    <select class="form-control no-select2 ptm-show-select" wire:model.live="perPage" aria-label="{{ __('personnel.show') }}" style="flex: 1 1 0%; min-width: 0">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive bg-light p-3">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
                    <thead class="bg-light p-2">
                        <tr>
                            <th>{{ __('personnel.no') }}</th>
                            <th>{{ __('personnel.actions') }}</th>
                            <th>{{ __('personnel.name') }}</th>
                            <th>{{ __('personnel.email') }}</th>
                            <th>{{ __('personnel.phone') }}</th>
                            <th>{{ __('personnel.failed_attempts') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lockedUsers as $user)
                            <tr>
                                <td>{{ $lockedUsers->firstItem() + $loop->index }}</td>
                                <td nowrap style="width: 150px">
                                    <div class="d-flex">
                                        <button
                                            type="button"
                                            class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                            wire:click="unlockAccount('{{ $user->id }}')"
                                            title="{{ __('personnel.unlock_account') }}"
                                        >
                                            <i class="mdi mdi-lock-open-variant"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-sm rm-act-btn rm-act-btn--edit"
                                            wire:click="openPasswordResetModal('{{ $user->id }}', '{{ $user->name }}', '{{ $user->email }}')"
                                            title="{{ __('personnel.reset_password') }}"
                                        >
                                            <i class="mdi mdi-key-change"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?? '-' }}</td>
                                <td><span class="badge badge-danger">{{ $user->failed_login_attempts ?? 5 }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">{{ __('personnel.no_locked_accounts_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">
                    {{ __('personnel.showing_to_of', ['from' => $lockedUsers->firstItem() ?? 0, 'to' => $lockedUsers->lastItem() ?? 0, 'total' => $lockedUsers->total()]) }}
                </small>
                {{ $lockedUsers->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    @if($showPasswordResetModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); overflow-y: auto" role="dialog" aria-labelledby="passwordResetModal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable ptm-modern-modal-shell" role="document">
                <div class="modal-content">
                    <div class="modal-header ptm-modern-modal-header">
                        <h4 class="modal-title" id="passwordResetModal"><i class="mdi mdi-key-change"></i> {{ __('personnel.reset_password') }}</h4>
                        <button type="button" class="close" wire:click="closePasswordResetModal()" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body ptm-modern-modal-body">
                        <div class="ptm-modal-intro mb-3">
                            <strong>{{ $selectedUserName }}</strong>
                            <div class="text-muted small">{{ $selectedUserEmail }}</div>
                        </div>

                        <div class="form-group">
                            <label><strong>{{ __('personnel.new_password') }}</strong></label>
                            <input
                                type="password"
                                placeholder="{{ __('personnel.enter_new_password') }}"
                                wire:model="newPassword"
                                class="form-control @error('newPassword') is-invalid @enderror"
                            />
                            @error('newPassword')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('personnel.minimum_8_characters_required') }}</small>
                        </div>

                        <div class="form-group mb-0">
                            <label><strong>{{ __('personnel.confirm_password') }}</strong></label>
                            <input
                                type="password"
                                placeholder="{{ __('personnel.confirm_password_placeholder') }}"
                                wire:model="confirmPassword"
                                class="form-control @error('confirmPassword') is-invalid @enderror"
                            />
                            @error('confirmPassword')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closePasswordResetModal()">{{ __('personnel.cancel') }}</button>
                        <button
                            type="button"
                            class="btn btn-primary"
                            wire:click="resetPassword()"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove><i class="mdi mdi-content-save-outline"></i> {{ __('personnel.reset_password') }}</span>
                            <span wire:loading><i class="mdi mdi-loading mdi-spin"></i> {{ __('personnel.resetting') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
