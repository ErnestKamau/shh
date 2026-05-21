<div class="acc-wizard-root">
    @if($showModal)
        <div class="acc-wizard-backdrop" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg acc-wizard-dialog" role="document">
                <div class="modal-content acc-wizard-modal">
                    <div class="acc-wizard-header">
                        <div class="acc-wizard-header-text">
                            <span class="acc-wizard-eyebrow">QARM / F / 01</span>
                            <h4 class="acc-wizard-title">
                                <i class="mdi mdi-close-circle-outline"></i>
                                Sample Rejection Form
                            </h4>
                        </div>
                        <button type="button" class="acc-wizard-close" wire:click="closeWizard" aria-label="Close">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>

                    <div class="acc-wizard-body">
                        <section class="acc-wizard-section">
                            <h6 class="acc-wizard-section-title">Request details</h6>
                            <div class="row acc-wizard-fields">
                                <div class="col-md-6 form-group">
                                    <label class="acc-label">Request NO</label>
                                    <input type="text" class="form-control acc-input" value="{{ $requestNo }}" readonly>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="acc-label">Client name</label>
                                    <input type="text" class="form-control acc-input" value="{{ $clientName }}" readonly>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="acc-label">Date sample received</label>
                                    <input type="date" class="form-control acc-input" value="{{ $dateSampleReceived }}" readonly>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="acc-label">Type of sample</label>
                                    <input type="text" class="form-control acc-input" value="{{ $typeOfSample }}" readonly>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="acc-label">Number of samples</label>
                                    <input type="number" class="form-control acc-input" value="{{ $numberOfSamples }}" readonly>
                                </div>
                            </div>
                        </section>

                        <div class="rej-integrity-card" role="alert">
                            <div class="rej-integrity-card-head">
                                <i class="mdi mdi-information-outline"></i>
                                <strong>Sample integrity notice</strong>
                            </div>
                            <p class="mb-0">
                                The above sample has not met the criteria of sample integrity for the tests requested.
                                Analysis of such sample will yield unreliable results. Therefore, we cannot receive the samples for further analysis.
                            </p>
                        </div>

                        <section class="acc-wizard-section">
                            <h6 class="acc-wizard-section-title">Rejection reasons</h6>

                            @if($reasonsConfigMissing)
                                <div class="alert alert-warning mb-0">
                                    Rejection reasons are not configured. Add a <code>sample_rejection_reasons</code> system configuration entry.
                                </div>
                            @else
                                <p class="acc-wizard-hint mb-3">Select one or more reasons. Provide a separate explanation for each selected reason.</p>

                                @error('reasons')
                                    <div class="alert alert-danger py-2">{{ $message }}</div>
                                @enderror
                                @error('reasons.*')
                                    <div class="alert alert-danger py-2">{{ $message }}</div>
                                @enderror

                                <div class="rej-reason-list">
                                    @foreach($availableReasons as $reason)
                                        @php $key = (string) ($reason['key'] ?? ''); @endphp
                                        @if($key === '')
                                            @continue
                                        @endif
                                        <div class="rej-reason-item {{ !empty($selectedReasonKeys[$key]) ? 'is-selected' : '' }}">
                                            <label class="rej-reason-toggle mb-0">
                                                <input
                                                    type="checkbox"
                                                    wire:model.live="selectedReasonKeys.{{ $key }}"
                                                    value="1"
                                                >
                                                <span class="rej-reason-check-ui"></span>
                                                <span class="rej-reason-label">{{ $reason['label'] ?? $key }}</span>
                                            </label>

                                            @if(!empty($selectedReasonKeys[$key]))
                                                <div class="rej-reason-explanation mt-2">
                                                    <label class="acc-label">Explanation for this reason</label>
                                                    <textarea
                                                        class="form-control acc-input"
                                                        rows="2"
                                                        wire:model="reasonExplanations.{{ $key }}"
                                                        placeholder="Describe how this reason applies to the sample…"
                                                    ></textarea>
                                                    @error('reasons.'.$loop->index.'.explanation')
                                                        <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    </div>

                    <div class="acc-wizard-footer">
                        <button type="button" class="btn btn-light acc-btn-ghost" wire:click="closeWizard">Cancel</button>
                        <button
                            type="button"
                            class="btn btn-danger acc-btn-reject"
                            wire:click="submitRejection"
                            wire:loading.attr="disabled"
                            @if($reasonsConfigMissing) disabled @endif
                        >
                            <span wire:loading.remove wire:target="submitRejection">
                                <i class="mdi mdi-close-circle-outline"></i> Submit rejection
                            </span>
                            <span wire:loading wire:target="submitRejection">Submitting…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .acc-wizard-root {
            --acc-accent: #3b5fc0;
            --acc-accent-dark: #2f4ba0;
            --acc-accent-soft: #eef2ff;
            --acc-border: #e2e8f0;
            --acc-text: #1e293b;
            --acc-muted: #64748b;
            --acc-param-bg: #fafbfc;
        }

        .acc-wizard-backdrop {
            position: fixed;
            inset: 0;
            z-index: 1060;
            background: rgba(15, 23, 42, 0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .acc-wizard-dialog { margin: 0; max-width: 820px; width: 100%; }

        .acc-wizard-modal {
            border: none;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
        }

        .acc-wizard-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 1.1rem 1.35rem;
            background: linear-gradient(135deg, #7f1d1d 0%, #b91c1c 55%, #dc2626 100%);
            color: #fff;
        }

        .acc-wizard-eyebrow {
            display: block;
            font-size: 0.7rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            opacity: 0.85;
        }

        .acc-wizard-title {
            margin: 0.15rem 0 0;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .acc-wizard-title i { margin-right: 0.35rem; }

        .acc-wizard-close {
            border: none;
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            line-height: 1;
        }

        .acc-wizard-body {
            padding: 1.25rem 1.5rem 1rem;
            background: #f8fafc;
            max-height: min(72vh, 680px);
            overflow-y: auto;
        }

        .acc-wizard-section {
            background: #fff;
            border: 1px solid var(--acc-border);
            border-radius: 12px;
            padding: 1.15rem 1.25rem;
            margin-bottom: 1rem;
        }

        .acc-wizard-section-title {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--acc-muted);
            margin-bottom: 0.85rem;
        }

        .acc-wizard-hint { font-size: 0.8rem; color: var(--acc-muted); }

        .acc-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--acc-muted);
            margin-bottom: 0.35rem;
        }

        .acc-input {
            border-radius: 8px;
            border-color: var(--acc-border);
            font-size: 0.9rem;
        }

        .acc-input[readonly] { background: #f1f5f9; }

        .acc-wizard-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.65rem;
            padding: 0.9rem 1.35rem;
            background: #fff;
            border-top: 1px solid var(--acc-border);
        }

        .acc-btn-ghost { border-radius: 8px; }

        .acc-btn-reject {
            border-radius: 8px;
            font-weight: 600;
            padding: 0.45rem 1.1rem;
        }

        .rej-integrity-card {
            margin-bottom: 1rem;
            padding: 1rem 1.1rem;
            border-radius: 10px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            font-size: 0.875rem;
            line-height: 1.55;
        }

        .rej-integrity-card-head {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .rej-integrity-card-head i { font-size: 1.25rem; }

        .rej-reason-list { display: flex; flex-direction: column; gap: 0.65rem; }

        .rej-reason-item {
            border: 1px solid var(--acc-border);
            border-radius: 10px;
            padding: 0.85rem 1rem;
            background: #fafbfc;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .rej-reason-item.is-selected {
            border-color: #fca5a5;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.08);
        }

        .rej-reason-toggle {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            cursor: pointer;
            font-weight: 600;
            color: var(--acc-text);
        }

        .rej-reason-toggle input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .rej-reason-check-ui {
            width: 18px;
            height: 18px;
            border: 2px solid #cbd5e1;
            border-radius: 4px;
            flex-shrink: 0;
            margin-top: 2px;
            background: #fff;
        }

        .rej-reason-toggle input:checked + .rej-reason-check-ui {
            background: #dc2626;
            border-color: #dc2626;
            box-shadow: inset 0 0 0 3px #fff;
        }

        .rej-reason-label { line-height: 1.4; font-size: 0.9rem; }
    </style>
</div>
