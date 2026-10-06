{{-- Request header — identity + quotation approve control + primary CTA + Actions dropdown --}}
@php
    $primary = $nextStepActions['primary'] ?? null;
    $secondary = $nextStepActions['secondary'] ?? [];
    $danger = $nextStepActions['danger'] ?? null;
    $hasMenuActions = count($secondary) > 0 || $danger !== null;
    $hasActions = $primary !== null || $hasMenuActions;
    $quotationPendingApproval = (bool) ($quotationPendingApproval ?? false);
    $quotationApprovedReadyToSend = (bool) ($quotationApprovedReadyToSend ?? false);
    $canApproveQuotation = (bool) ($canApproveQuotation ?? false);
    // Green check only — no document icon chip. Show when pending AND configured approver.
    $showQuoteApproveControl = $quotationPendingApproval && $canApproveQuotation;
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
            z-index: 1200;
            background: #fff;
            font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
            font-size: 0.8rem;
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
            padding: 0.5rem 0.85rem;
            border: 0;
            background: transparent !important;
            color: #0369a1 !important;
            text-align: left;
            font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif) !important;
            font-size: 0.8rem !important;
            font-weight: 500 !important;
            line-height: 1.35;
            text-decoration: none !important;
            cursor: pointer;
        }

        .rv-actions-dropdown .rv-action-item:hover,
        .rv-actions-dropdown .rv-action-item:focus,
        .rv-actions-dropdown .rv-action-item:active {
            background: #eff6ff !important;
            color: #1e3a8a !important;
        }

        .rv-actions-dropdown .rv-action-item .mdi {
            flex-shrink: 0;
            width: 1.1rem;
            text-align: center;
            font-size: 1.05rem !important;
            line-height: 1;
            color: #0369a1 !important;
        }

        .rv-actions-dropdown .rv-action-item:hover .mdi,
        .rv-actions-dropdown .rv-action-item:focus .mdi,
        .rv-actions-dropdown .rv-action-item:active .mdi {
            color: #1e3a8a !important;
        }

        /* Scenario G — Accept / approve rows use Acc green */
        .rv-actions-dropdown .rv-action-item.rv-action-item--acc,
        .rv-actions-dropdown .rv-action-item.rv-action-item--acc:link,
        .rv-actions-dropdown .rv-action-item.rv-action-item--acc:visited {
            color: #047857 !important;
        }

        .rv-actions-dropdown .rv-action-item.rv-action-item--acc .mdi,
        .rv-actions-dropdown .rv-action-item.rv-action-item--acc:hover .mdi {
            color: #047857 !important;
        }

        .rv-actions-dropdown .rv-action-item.rv-action-item--acc:hover,
        .rv-actions-dropdown .rv-action-item.rv-action-item--acc:focus {
            background: #ecfdf5 !important;
            color: #047857 !important;
        }

        .rv-actions-dropdown .rv-action-item.rv-action-item--danger,
        .rv-actions-dropdown .rv-action-item.rv-action-item--danger:link {
            color: #b91c1c !important;
        }

        .rv-actions-dropdown .rv-action-item.rv-action-item--danger .mdi {
            color: #b91c1c !important;
        }

        .rv-actions-dropdown .rv-action-item.rv-action-item--danger:hover {
            background: #fef2f2 !important;
            color: #b91c1c !important;
        }

        .rv-quote-bell__item {
            color: #0369a1 !important;
        }

        .rv-quote-bell__item .mdi {
            color: #0369a1 !important;
        }

        .rv-quote-bell__item .text-success,
        .rv-quote-bell__item .mdi.text-success {
            color: #047857 !important;
        }

        .rv-quote-bell__item--danger,
        .rv-quote-bell__item--danger .mdi {
            color: #b91c1c !important;
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
                @if($showQuoteApproveControl)
                    <div class="rv-quote-bell rv-quote-approve"
                         x-data="{
                            open: false,
                            rejectOpen: false,
                            menuStyle: {},
                            contentLeft() {
                                const main = document.getElementById('main-container-body');
                                let left = main ? main.getBoundingClientRect().left : 0;
                                const sidebar = document.getElementById('sidebar-container');
                                if (
                                    sidebar
                                    && sidebar.offsetParent !== null
                                    && ! sidebar.classList.contains('hidden')
                                    && ! sidebar.classList.contains('d-none')
                                ) {
                                    // Keep the panel inside the page, clear of a docked or floating sidebar.
                                    left = Math.max(left, sidebar.getBoundingClientRect().right);
                                }
                                return left;
                            },
                            placeMenu() {
                                const btn = this.$refs.approveBtn;
                                if (!btn) {
                                    return;
                                }
                                const rect = btn.getBoundingClientRect();
                                const menuWidth = Math.min(288, window.innerWidth - 16);
                                const gutter = 8;
                                const minLeft = this.contentLeft() + gutter;
                                const maxRight = window.innerWidth - gutter;
                                // Prefer under the button, right-aligned into the page (never into the sidebar).
                                let left = rect.right - menuWidth;
                                if (left < minLeft) {
                                    left = minLeft;
                                }
                                if (left + menuWidth > maxRight) {
                                    left = Math.max(minLeft, maxRight - menuWidth);
                                }
                                this.menuStyle = {
                                    position: 'fixed',
                                    top: (rect.bottom + 6) + 'px',
                                    left: left + 'px',
                                    right: 'auto',
                                    width: menuWidth + 'px',
                                    zIndex: 1055,
                                };
                            },
                            toggle() {
                                this.rejectOpen = false;
                                this.open = !this.open;
                                if (this.open) {
                                    this.$nextTick(() => this.placeMenu());
                                }
                            },
                            close() {
                                this.open = false;
                                this.rejectOpen = false;
                            }
                         }"
                         @click.outside="close()"
                         @keydown.escape.window="close()"
                         @resize.window="open && placeMenu()"
                         @scroll.window.capture="open && placeMenu()">
                        <button type="button"
                            x-ref="approveBtn"
                            class="btn btn-sm rv-header-chip-btn rv-quote-bell__btn rv-quote-approve__btn is-awaiting"
                            @click.stop="toggle()"
                            :aria-expanded="open"
                            title="Quotation awaiting your approval"
                            aria-label="Quotation awaiting your approval">
                            <i class="mdi mdi-check-circle" aria-hidden="true"></i>
                            <span class="rv-quote-bell__badge rv-quote-approve__badge">1</span>
                        </button>
                        <div class="rv-quote-bell__menu"
                             x-show="open"
                             x-cloak
                             x-bind:style="menuStyle"
                             @click.stop>
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
                                    {{ $quoteBellLabel }} · Pending your approval
                                </div>
                                <a class="rv-quote-bell__item"
                                   href="{{ route('quotation.preview.pdf', ['id' => $quotationHeader->id]) }}"
                                   target="_blank"
                                   rel="noopener">
                                    <i class="mdi mdi-file-pdf-box" aria-hidden="true"></i>
                                    {{ $quoteBellLabel }}
                                </a>
                            @endif

                            <button type="button"
                                class="rv-quote-bell__item"
                                wire:click="openApproveQuotationModal"
                                @click="close()">
                                <i class="mdi mdi-check-decagram text-success" aria-hidden="true"></i>
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
                        </div>
                    </div>
                @elseif($quotationApprovedReadyToSend)
                    <button type="button"
                        class="btn btn-sm rv-header-primary-btn"
                        wire:click="sendApprovedQuotationToCustomer"
                        wire:loading.attr="disabled"
                        title="Send approved quotation to customer">
                        <i class="mdi mdi-send" aria-hidden="true"></i>
                        Send to customer
                    </button>
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
                                class="btn btn-sm rv-header-chip-btn dropdown-toggle"
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
