@extends('layouts.registry.layout.app')

@section('title2')
    <title>{{ $registryRequest->reference_no }} — Registry</title>
    <style>
        .registry-request-show {
            --rr-primary: #2563eb;
            --rr-primary-soft: #eff6ff;
            --rr-slate-50: #f8fafc;
            --rr-slate-100: #f1f5f9;
            --rr-slate-200: #e2e8f0;
            --rr-slate-400: #94a3b8;
            --rr-slate-500: #64748b;
            --rr-slate-700: #334155;
            --rr-slate-800: #1e293b;
            --rr-radius: 16px;
            --rr-radius-sm: 10px;
            --rr-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
            --rr-shadow-hover: 0 8px 24px rgba(15, 23, 42, 0.08);
        }

        .registry-request-show .rr-layout {
            margin-top: 0.25rem;
        }

        .registry-request-show .rr-panel {
            background: #fff;
            border: 1px solid var(--rr-slate-200);
            border-radius: var(--rr-radius);
            box-shadow: var(--rr-shadow);
            margin-bottom: 1.25rem;
            overflow: hidden;
        }

        .registry-request-show .rr-panel__head {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, var(--rr-slate-50) 0%, var(--rr-primary-soft) 100%);
            border-bottom: 1px solid var(--rr-slate-200);
        }

        .registry-request-show .rr-panel__icon {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: #fff;
            border: 1px solid var(--rr-slate-200);
            color: var(--rr-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .registry-request-show .rr-panel__title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--rr-slate-800);
            margin: 0;
        }

        .registry-request-show .rr-panel__sub {
            font-size: 0.78rem;
            color: var(--rr-slate-500);
            margin: 0.1rem 0 0;
        }

        .registry-request-show .rr-panel__body {
            padding: 1.25rem;
        }

        .registry-request-show .rr-panel__body--flush {
            padding: 0;
        }

        .registry-request-show .rr-actions-form textarea {
            border-radius: var(--rr-radius-sm);
            border: 1px solid var(--rr-slate-200);
            font-size: 0.875rem;
            resize: vertical;
            min-height: 72px;
        }

        .registry-request-show .rr-actions-form textarea:focus {
            border-color: var(--rr-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .registry-request-show .rr-btn {
            border-radius: var(--rr-radius-sm);
            font-weight: 600;
            font-size: 0.8125rem;
            padding: 0.45rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .registry-request-show .rr-btn--approve {
            background: #059669;
            border: none;
            color: #fff;
        }

        .registry-request-show .rr-btn--approve:hover {
            background: #047857;
            color: #fff;
        }

        .registry-request-show .rr-btn--return {
            background: #fff;
            border: 1.5px solid #f59e0b;
            color: #b45309;
        }

        .registry-request-show .rr-btn--return:hover {
            background: #fffbeb;
            color: #92400e;
        }

        .registry-request-show .rr-actions-divider {
            border-top: 1px dashed var(--rr-slate-200);
            margin: 1rem 0;
        }

        .registry-request-show .rr-sidebar .rr-panel:last-child {
            margin-bottom: 0;
        }

        .registry-request-show .rr-tabs-panel {
            background: #fff;
            border: 1px solid var(--rr-slate-200);
            border-radius: var(--rr-radius);
            box-shadow: var(--rr-shadow);
            margin-bottom: 1.25rem;
            overflow: hidden;
        }

        .registry-request-show .rr-tabs-panel .rr-nav-tabs {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 4px;
            padding: 8px 10px 0;
            margin: 0;
            list-style: none;
            background: var(--rr-slate-50);
            border-bottom: 1px solid var(--rr-slate-200);
        }

        .registry-request-show .rr-tabs-panel .rr-nav-tabs .nav-item {
            margin: 0;
        }

        .registry-request-show .rr-tabs-panel .rr-nav-tabs .nav-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.65rem 1rem;
            margin-bottom: -1px;
            border-radius: 8px 8px 0 0;
            border: 1px solid transparent;
            border-bottom: 1px solid transparent;
            color: var(--rr-slate-500);
            font-size: 0.8125rem;
            font-weight: 600;
            text-decoration: none;
            background: transparent;
            transition: color 0.15s ease, background 0.15s ease, border-color 0.15s ease;
        }

        .registry-request-show .rr-tabs-panel .rr-nav-tabs .nav-link:hover {
            color: var(--rr-slate-700);
            background: rgba(255, 255, 255, 0.8);
        }

        .registry-request-show .rr-tabs-panel .rr-nav-tabs .nav-link.active {
            color: var(--rr-slate-800);
            background: #fff;
            border-color: var(--rr-slate-200);
            border-bottom-color: #fff;
        }

        .registry-request-show .rr-tabs-panel .rr-nav-tabs .nav-link.active .mdi {
            color: var(--rr-primary);
        }

        .registry-request-show .rr-tabs-panel .rr-nav-tabs .nav-link .badge {
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
            background: var(--rr-slate-200);
            color: var(--rr-slate-700);
        }

        .registry-request-show .rr-tabs-panel .rr-nav-tabs .nav-link.active .badge {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .registry-request-show .rr-tabs-panel .tab-content {
            padding: 0;
        }

        .registry-request-show .rr-tab-pane-content {
            max-width: 100%;
            padding: 1.25rem;
        }

        @include('layouts.registry.partials.request-show-styles')
    </style>
@endsection

@section('content2')
<main class="registry-request-show">
    @php
        $items = [
            ['link' => route('registry.dashboard'), 'name' => 'Registry Dashboard', 'icon' => null],
            ['link' => route('registry.requests.index'), 'name' => 'Registry Requests', 'icon' => null],
            [
                'link' => route('registry.requests.show', $registryRequest->id),
                'name' => $registryRequest->reference_no,
                'icon' => null,
            ],
        ];
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="container-fluid px-0">
        @livewire('registry.registry-request-details', ['requestId' => $registryRequest->id])

        <div class="rr-layout">
            @livewire('registry.registry-workflow-timeline', ['requestId' => $registryRequest->id])

            @php
                $assignmentCount = $registryRequest->assignments->count();
                $documentCount = $registryRequest->documents->count();
                $auditTabActive = auth()->user()?->can('registry.components.audit trail.view') ?? false;
            @endphp

            <div class="rr-tabs-panel">
                <ul class="nav rr-nav-tabs" id="registry-request-tabs" role="tablist">
                    @can('registry.components.audit trail.view')
                        <li class="nav-item">
                            <a class="nav-link {{ $auditTabActive ? 'active' : '' }}"
                               id="audit-tab"
                               data-toggle="tab"
                               href="#registry-tab-audit"
                               role="tab"
                               aria-controls="registry-tab-audit"
                               aria-selected="{{ $auditTabActive ? 'true' : 'false' }}">
                                <i class="mdi mdi-history"></i> Audit Trail
                            </a>
                        </li>
                    @endcan
                    <li class="nav-item">
                        <a class="nav-link {{ $auditTabActive ? '' : 'active' }}"
                           id="assignments-tab"
                           data-toggle="tab"
                           href="#registry-tab-assignments"
                           role="tab"
                           aria-controls="registry-tab-assignments"
                           aria-selected="{{ $auditTabActive ? 'false' : 'true' }}">
                            <i class="mdi mdi-account-multiple-outline"></i> Assignments
                            @if($assignmentCount > 0)
                                <span class="badge">{{ $assignmentCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                           id="documents-tab"
                           data-toggle="tab"
                           href="#registry-tab-documents"
                           role="tab"
                           aria-controls="registry-tab-documents"
                           aria-selected="false">
                            <i class="mdi mdi-paperclip"></i> Documents
                            @if($documentCount > 0)
                                <span class="badge">{{ $documentCount }}</span>
                            @endif
                        </a>
                    </li>
                    @can('registry.components.approval queue.edit')
                        <li class="nav-item">
                            <a class="nav-link"
                               id="approvals-tab"
                               data-toggle="tab"
                               href="#registry-tab-approvals"
                               role="tab"
                               aria-controls="registry-tab-approvals"
                               aria-selected="false">
                                <i class="mdi mdi-gavel"></i> Approvals
                            </a>
                        </li>
                    @endcan
                </ul>

                <div class="tab-content" id="registry-request-tabs-content">
                    @can('registry.components.audit trail.view')
                        <div class="tab-pane fade {{ $auditTabActive ? 'show active' : '' }}"
                             id="registry-tab-audit"
                             role="tabpanel"
                             aria-labelledby="audit-tab">
                            @livewire('registry.registry-audit-trail', ['requestId' => $registryRequest->id])
                        </div>
                    @endcan
                    <div class="tab-pane fade {{ $auditTabActive ? '' : 'show active' }}"
                         id="registry-tab-assignments"
                         role="tabpanel"
                         aria-labelledby="assignments-tab">
                        @livewire('registry.registry-assignment-panel', ['requestId' => $registryRequest->id])
                    </div>
                    <div class="tab-pane fade"
                         id="registry-tab-documents"
                         role="tabpanel"
                         aria-labelledby="documents-tab">
                        @livewire('registry.registry-document-uploader', ['requestId' => $registryRequest->id], key('registry-docs-'.$registryRequest->id))
                    </div>
                    @can('registry.components.approval queue.edit')
                        <div class="tab-pane fade"
                             id="registry-tab-approvals"
                             role="tabpanel"
                             aria-labelledby="approvals-tab">
                            <div class="rr-tab-pane-content">
                                <p class="text-muted small mb-3">Approve or return this request. Comments are stored in the audit trail.</p>
                                <form method="POST" action="{{ route('registry.requests.approve', $registryRequest->id) }}" class="rr-actions-form mb-0">
                                    @csrf
                                    <label class="rr-meta-label d-block mb-1">Approval comment</label>
                                    <textarea name="comment" class="form-control mb-2" placeholder="Optional note for the record…"></textarea>
                                    <button type="submit" class="btn rr-btn rr-btn--approve">
                                        <i class="mdi mdi-check-circle-outline"></i> Approve
                                    </button>
                                </form>
                                <div class="rr-actions-divider"></div>
                                <form method="POST" action="{{ route('registry.requests.reject', $registryRequest->id) }}" class="rr-actions-form mb-0">
                                    @csrf
                                    <label class="rr-meta-label d-block mb-1">Return reason <span class="text-danger">*</span></label>
                                    <textarea name="comment" class="form-control mb-2" placeholder="Explain why this request is being returned…" required></textarea>
                                    <button type="submit" class="btn rr-btn rr-btn--return">
                                        <i class="mdi mdi-arrow-u-left-top"></i> Return
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
