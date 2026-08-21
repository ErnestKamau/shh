{{-- Approvals configuration — LS table matching quotation overview theme --}}
@php
    $roleSearchOptions = collect($roleOptions)->map(fn ($role) => [
        'value' => (string) ($role['name'] ?? ''),
        'label' => (string) ($role['name'] ?? ''),
    ])->filter(fn ($role) => $role['value'] !== '')->values()->all();

    $titleSearchOptions = collect([$configTitle, $editTitle])
        ->filter(fn ($title) => filled($title))
        ->unique()
        ->map(fn ($title) => ['value' => (string) $title, 'label' => (string) $title])
        ->values()
        ->all();
@endphp
<div class="ls-quotation-shell ls-quotation-approval-settings ls-ui-kit">
    <div class="ls-quotation-panel card shadow-sm border-0 mb-0">
        <div class="card-header border-0 ls-quotation-list-header">
            <div class="ls-quotation-list-header__top">
                <div>
                    <h5 class="card-title mb-1">
                        <i class="mdi mdi-account-check-outline"></i>
                        Approvals Configuration
                    </h5>
                    <p class="text-muted small mb-0">
                        All active users with the configured role can approve quotations. The first approval wins.
                    </p>
                </div>
                <button type="button"
                    class="rm-act-btn rm-act-btn--edit"
                    wire:click="openEditModal"
                    title="Edit approval role">
                    <i class="mdi mdi-pencil-outline"></i>
                </button>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="ls-quotation-table-scroll">
                <table class="table table-hover ls-table ls-table--dense mb-0">
                    <thead>
                        <tr>
                            <th style="width: 1%;">#</th>
                            <th>Title</th>
                            <th>Role</th>
                            <th>Approvers</th>
                            <th style="width: 1%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>
                                <div class="ls-table__stack-primary">{{ $configTitle !== '' ? $configTitle : '—' }}</div>
                                <div class="ls-table__stack-secondary">Quotation approval rule</div>
                            </td>
                            <td>
                                @if($approverRole !== '')
                                    <span class="quotation-status-chip quotation-status-chip--approval">
                                        {{ $approverRole }}
                                    </span>
                                @else
                                    <span class="quotation-status-chip quotation-status-chip--prep">Not set</span>
                                @endif
                            </td>
                            <td>
                                @if($approverUsers->isEmpty())
                                    <span class="text-muted small">No active users currently have this role.</span>
                                @else
                                    <div class="ls-quotation-approver-chips">
                                        @foreach($approverUsers as $user)
                                            <span class="ls-quotation-approver-chip" title="{{ $user->email }}">
                                                <i class="mdi mdi-account"></i>
                                                {{ $user->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex quotation-actions-cell">
                                    <button type="button"
                                        class="rm-act-btn rm-act-btn--edit"
                                        wire:click="openEditModal"
                                        title="Edit">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($showEditModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.45); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content ls-quotation-approval-modal">
                    <div class="modal-header">
                        <h5 class="modal-title mb-0">
                            <i class="mdi mdi-pencil-outline"></i> Edit Approval
                        </h5>
                        <button type="button" class="close" wire:click="closeEditModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="ls-form-grid ls-form-grid--2 ls-compact">
                            @include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
                                'label' => 'Name',
                                'id' => 'quotation-approver-name-edit',
                                'wireModel' => 'editTitle',
                                'required' => true,
                                'placeholder' => 'Search or enter name…',
                                'options' => $titleSearchOptions,
                                'selected' => $editTitle !== '' ? $editTitle : null,
                                'disableSuccess' => true,
                                'allowCustom' => true,
                                'error' => $errors->first('editTitle'),
                            ])

                            @include('layouts.lab.partials.ls-ui.fields.ls-field-search-basic', [
                                'label' => 'Select Role',
                                'id' => 'quotation-approver-role-edit',
                                'wireModel' => 'editRole',
                                'required' => true,
                                'placeholder' => 'Type to search roles…',
                                'options' => $roleSearchOptions,
                                'selected' => $editRole !== '' ? $editRole : null,
                                'disableSuccess' => true,
                                'hint' => 'Every user with this role can approve quotations across billing and Process Enquiry.',
                                'error' => $errors->first('editRole'),
                            ])
                        </div>
                    </div>
                    <div class="modal-footer" style="gap: 8px;">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeEditModal">Close</button>
                        <button type="button"
                            class="btn btn-primary"
                            wire:click="save"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                <i class="mdi mdi-content-save"></i> Save
                            </span>
                            <span wire:loading wire:target="save">Saving…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

<style>
    .ls-quotation-approval-settings .ls-quotation-list-header__top {
        flex-direction: row;
        align-items: flex-start;
        justify-content: space-between;
    }

    .ls-quotation-approver-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }

    .ls-quotation-approver-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.15rem 0.55rem;
        border-radius: 999px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #334155;
        font-size: 0.72rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .ls-quotation-approver-chip .mdi {
        font-size: 0.85rem;
        color: #64748b;
    }

    .ls-quotation-approval-modal {
        border: 0;
        border-radius: 12px;
        overflow: visible;
    }

    .ls-quotation-approval-modal .modal-body {
        overflow: visible;
    }

    .ls-quotation-approval-modal .ls-search-basic {
        position: relative;
        z-index: 1;
    }

    .ls-quotation-approval-modal .ls-search-basic.is-open {
        z-index: 5;
    }

    .ls-quotation-approval-modal .ls-search-basic[data-ls-disable-success="1"].is-success .ls-field__control {
        border-color: var(--ls-border, #e2e8f0) !important;
        box-shadow: none !important;
        background: #fff !important;
    }

    @media (max-width: 575.98px) {
        .ls-quotation-approval-modal .ls-form-grid--2 {
            grid-template-columns: 1fr;
        }
    }
</style>
</div>
