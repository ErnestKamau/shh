{{-- Burgundy enquiry header — identity + quotation bell + primary CTA + Actions dropdown --}}
@php
    $primary = $nextStepActions['primary'] ?? null;
    $secondary = $nextStepActions['secondary'] ?? [];
    $danger = $nextStepActions['danger'] ?? null;
    $hasMenuActions = count($secondary) > 0 || $danger !== null;
    $hasActions = $primary !== null || $hasMenuActions;
    $quotationPendingApproval = (bool) ($quotationPendingApproval ?? false);
    $quotationApprovedReadyToSend = (bool) ($quotationApprovedReadyToSend ?? false);
    $canApproveQuotation = (bool) ($canApproveQuotation ?? false);
    $showQuoteBell = $quotationPendingApproval || $quotationApprovedReadyToSend || $canApproveQuotation
        || ($quotationHeader !== null && (int) ($quotationHeader->is_approved ?? 0) === 1);
    $quoteBellCount = ($canApproveQuotation ? 1 : 0) + ($quotationApprovedReadyToSend ? 1 : 0);
    $quoteBellShouldRing = $canApproveQuotation || $quotationApprovedReadyToSend;
@endphp
<div class="rv-header workflow-board-header">
    {{-- Scoped here so nothing in lab-surface / Bootstrap can paint Actions items burgundy --}}
    <style>
        .rv-actions-dropdown {
            position: relative;
            flex-shrink: 0;
        }

        .rv-actions-dropdown > .dropdown-menu {
            position: absolute !important;
            top: 100% !important;
            right: 0 !important;
            left: auto !important;
            transform: none !important;
            float: none;
            margin-top: 0.35rem;
            min-width: 15.5rem;
            max-width: 20rem;
            max-height: min(70vh, 520px);
            overflow-y: auto;
            overflow-x: hidden;
            padding: 0.35rem 0;
            border: 1px solid #dbe5f0;
            border-radius: 10px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
            z-index: 1050;
            background: #fff;
            font-family: Roboto, sans-serif;
            font-size: 13px;
        }

        .rv-actions-dropdown .dropdown-menu > li {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .rv-actions-dropdown .rv-action-item,
        .rv-actions-dropdown .rv-action-item:link,
        .rv-actions-dropdown .rv-action-item:visited {
            display: flex !important;
            align-items: center;
            gap: 0.45rem;
            width: 100%;
            padding: 0.5rem 0.9rem;
            border: 0;
            background: transparent !important;
            color: #334155 !important;
            text-align: left;
            font-size: 13px;
            text-decoration: none !important;
            cursor: pointer;
        }

        .rv-actions-dropdown .rv-action-item:hover,
        .rv-actions-dropdown .rv-action-item:focus,
        .rv-actions-dropdown .rv-action-item:active {
            background: #f1f5f9 !important;
            color: #0f172a !important;
        }

        .rv-actions-dropdown .rv-action-item .mdi,
        .rv-actions-dropdown .rv-action-item:hover .mdi,
        .rv-actions-dropdown .rv-action-item:focus .mdi,
        .rv-actions-dropdown .rv-action-item:active .mdi {
            color: #475569 !important;
        }

        .rv-actions-dropdown .request-view-actions-form {
            margin: 0;
            padding: 0;
            display: block;
            width: 100%;
        }

        .rv-actions-dropdown .dropdown-divider {
            margin: 0.35rem 0;
            border-top: 1px solid #e8eef4;
        }
    </style>

    <div class="batch-header-bar rv-header-bar">
        <div class="batch-header-top rv-header-top">
            <div class="rv-header-identity">
                @if(!empty($viewHeader['request_number']))
                    <span class="rv-header-request">{{ $viewHeader['request_number'] }}</span>
                @endif
                @if(!empty($viewHeader['request_number']) && !empty($viewHeader['form_name']))
                    <span class="rv-header-sep" aria-hidden="true">·</span>
                @endif
                @if(!empty($viewHeader['form_name']))
                    <span class="rv-header-form-name">{{ $viewHeader['form_name'] }}</span>
                @endif
                @if($viewHeader['has_enquiry'] && $viewHeader['enquiry_stage'])
                    <span class="rv-header-stage batch-stage-pill">{{ $viewHeader['enquiry_stage'] }}</span>
                @endif
                @if(!empty($viewHeader['quotation_content_stale']))
                    <span class="badge badge-warning ml-2" title="Tests were synced from an edited quotation after send">Content changed since send</span>
                @endif
            </div>

            <div class="d-flex align-items-center flex-wrap batch-header-actions rv-header-actions" style="gap: 6px;">
                @if($showQuoteBell)
                    <div class="rv-quote-bell"
                         x-data="{ open: false, rejectOpen: false }"
                         @click.outside="open = false; rejectOpen = false">
                        <button type="button"
                            class="btn btn-sm btn-outline-secondary rv-quote-bell__btn {{ $quoteBellShouldRing ? 'is-ringing' : '' }}"
                            @click.stop="open = !open; rejectOpen = false"
                            :aria-expanded="open"
                            title="Quotation actions"
                            aria-label="Quotation actions">
                            <i class="mdi {{ $quoteBellShouldRing ? 'mdi-bell-ring' : 'mdi-bell-outline' }}" aria-hidden="true"></i>
                            @if($quoteBellCount > 0)
                                <span class="rv-quote-bell__badge">{{ $quoteBellCount }}</span>
                            @endif
                        </button>
                        <div class="rv-quote-bell__menu" x-show="open" x-cloak @click.stop>
                            @if($quotationHeader)
                                @php
                                    $quoteBellLabSections = $quotationHeader->relationLoaded('labSections')
                                        ? $quotationHeader->labSections->pluck('name')->filter()->implode(', ')
                                        : $quotationHeader->labSections()->pluck('name')->filter()->implode(', ');
                                    $quoteBellLabel = trim((string) ($quotationHeader->quote_number ?? ''));
                                    if ($quoteBellLabSections !== '') {
                                        $quoteBellLabel = ($quoteBellLabel !== '' ? $quoteBellLabel.' - ' : '').$quoteBellLabSections;
                                    } elseif ($quoteBellLabel === '') {
                                        $quoteBellLabel = 'Quotation';
                                    }
                                @endphp
                                <div class="rv-quote-bell__meta">
                                    {{ $quoteBellLabel }}
                                    @if($quotationPendingApproval)
                                        · Pending approval
                                    @elseif((int) ($quotationHeader->is_approved ?? 0) === 1)
                                        · Approved
                                    @endif
                                </div>
                                <a class="rv-quote-bell__item"
                                   href="{{ route('quotation.preview.pdf', ['id' => $quotationHeader->id]) }}"
                                   target="_blank"
                                   rel="noopener">
                                    <i class="mdi mdi-file-pdf-box" aria-hidden="true"></i>
                                    {{ $quoteBellLabel }}
                                </a>
                            @endif

                            @if($canApproveQuotation)
                                <button type="button"
                                    class="rv-quote-bell__item"
                                    wire:click="openApproveQuotationModal"
                                    @click="open = false">
                                    <i class="mdi mdi-bell-ring text-success" aria-hidden="true"></i>
                                    Approve quotation
                                </button>
                                <button type="button"
                                    class="rv-quote-bell__item rv-quote-bell__item--danger"
                                    @click="rejectOpen = !rejectOpen">
                                    <i class="mdi mdi-close-circle-outline" aria-hidden="true"></i>
                                    Reject (reason required)
                                </button>
                                <div class="rv-quote-bell__panel" x-show="rejectOpen" x-cloak>
                                    <label class="small font-weight-bold d-block mb-1" for="rv-quote-reject-reason">Rejection reason</label>
                                    <textarea id="rv-quote-reject-reason"
                                        class="form-control form-control-sm mb-2"
                                        rows="3"
                                        wire:model="approvalDecisionComments"
                                        placeholder="Explain why this quotation is being returned"></textarea>
                                    @error('approvalDecisionComments')
                                        <span class="text-danger small d-block mb-2">{{ $message }}</span>
                                    @enderror
                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        wire:click="rejectEnquiryQuotation"
                                        wire:loading.attr="disabled">
                                        Confirm reject
                                    </button>
                                </div>
                            @elseif($quotationPendingApproval)
                                <div class="rv-quote-bell__meta">Waiting for an approver to review this quotation.</div>
                            @endif

                            @if($quotationApprovedReadyToSend)
                                <button type="button"
                                    class="rv-quote-bell__item"
                                    wire:click="sendApprovedQuotationToCustomer"
                                    wire:loading.attr="disabled"
                                    @click="open = false">
                                    <i class="mdi mdi-send" aria-hidden="true"></i>
                                    Send to customer
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                @if($hasActions)
                    @if($primary)
                        @include('livewire.submission-forms.request-view.cards.action-button', [
                            'action' => $primary,
                            'variant' => 'header-primary',
                        ])
                    @endif

                    @if($hasMenuActions)
                        <div class="btn-group workflow-actions-dropdown rv-actions-dropdown"
                             x-data="{ open: false }"
                             @click.outside="open = false">
                            <button type="button"
                                class="btn btn-sm btn-outline-secondary btn-action-sm dropdown-toggle"
                                id="requestViewActionsDropdown"
                                @click.stop="open = !open"
                                :aria-expanded="open"
                                aria-haspopup="true">
                                <i class="mdi mdi-dots-horizontal"></i> Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right request-view-actions-menu"
                                :class="{ 'show': open }"
                                aria-labelledby="requestViewActionsDropdown"
                                @click="if ($event.target.closest('.rv-action-item, form, button, a')) { open = false; }">

                                @foreach($secondary as $action)
                                    <li>
                                        @include('livewire.submission-forms.request-view.cards.action-button', [
                                            'action' => $action,
                                            'variant' => 'dropdown',
                                        ])
                                    </li>
                                @endforeach

                                @if($danger)
                                    @if(count($secondary) > 0)
                                        <li><div class="dropdown-divider"></div></li>
                                    @endif
                                    <li>
                                        @include('livewire.submission-forms.request-view.cards.action-button', [
                                            'action' => $danger,
                                            'variant' => 'dropdown',
                                        ])
                                    </li>
                                @endif
                            </ul>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
