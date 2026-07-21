{{-- Burgundy enquiry header — identity + Actions dropdown (Samples Receiving pattern) --}}
@php
    $primary = $nextStepActions['primary'] ?? null;
    $secondary = $nextStepActions['secondary'] ?? [];
    $danger = $nextStepActions['danger'] ?? null;
    $hasActions = $primary !== null || count($secondary) > 0 || $danger !== null;
@endphp
<div class="rv-header workflow-board-header">
    {{-- Scoped here so nothing in lab-surface / Bootstrap can paint Actions items burgundy --}}
    <style>
        /* Match Samples Receiving .workflow-actions-dropdown exactly (measured) */
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
            align-items: center !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0.5rem 0.95rem !important;
            font-family: Roboto, sans-serif !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            line-height: 1.35 !important;
            letter-spacing: normal !important;
            color: #334155 !important;
            background: transparent !important;
            background-color: transparent !important;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            text-align: left !important;
            text-decoration: none !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            cursor: pointer !important;
        }

        .rv-actions-dropdown .rv-action-item:hover,
        .rv-actions-dropdown .rv-action-item:focus,
        .rv-actions-dropdown .rv-action-item:focus-visible,
        .rv-actions-dropdown .rv-action-item:active,
        .rv-actions-dropdown .rv-action-item.active,
        .rv-actions-dropdown .rv-action-item:focus:active,
        .rv-actions-dropdown .rv-action-item.show {
            color: #1e293b !important;
            background: #f1f5f9 !important;
            background-color: #f1f5f9 !important;
            outline: none !important;
            box-shadow: none !important;
            text-decoration: none !important;
        }

        .rv-actions-dropdown .rv-action-item .mdi {
            flex-shrink: 0 !important;
            display: inline-block !important;
            width: 1.125rem !important;
            margin-right: 0.5rem !important;
            font-size: 13px !important;
            line-height: 1 !important;
            text-align: center !important;
            color: #64748b !important;
        }

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
            </div>

            @if($hasActions)
                <div class="d-flex align-items-center flex-wrap batch-header-actions rv-header-actions" style="gap: 6px;">
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

                            @if($primary)
                                <li>
                                    @include('livewire.submission-forms.request-view.cards.action-button', [
                                        'action' => $primary,
                                        'variant' => 'dropdown',
                                    ])
                                </li>
                            @endif

                            @foreach($secondary as $action)
                                <li>
                                    @include('livewire.submission-forms.request-view.cards.action-button', [
                                        'action' => $action,
                                        'variant' => 'dropdown',
                                    ])
                                </li>
                            @endforeach

                            @if($danger)
                                @if($primary || count($secondary) > 0)
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
                </div>
            @endif
        </div>
    </div>
</div>
