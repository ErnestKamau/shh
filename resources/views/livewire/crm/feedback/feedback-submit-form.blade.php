<div class="feedback-submit-wrap">
    <style>
        .feedback-submit-wrap {
            min-height: 100vh;
            background: #f1f5f9;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #1e293b;
        }

        .fs-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            overflow: hidden;
        }

        .fs-header-band {
            background: #fff;
            padding: 2.5rem 3rem;
            color: #1e293b;
            border-bottom: 1px solid #e2e8f0;
        }

        .fs-iso-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 4px 12px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: .05em;
            color: #475569;
            text-transform: uppercase;
        }

        .fs-section-label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .fs-section-icon {
            width: 32px;
            height: 32px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1rem;
            background: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .fs-section-title {
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: .05em;
            color: #334155;
            margin: 0;
            text-transform: uppercase;
        }

        .fs-identity-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .fs-identity-avatar {
            width: 44px;
            height: 44px;
            border-radius: 4px;
            background: #4f46e5;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        /* Rating Grid Structure */
        .fs-rating-row {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            border: 1px solid #e2e8f0;
            background: #fff;
            gap: 10px;
        }

        .fs-rating-row:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .fs-rating-row-label {
            font-size: 0.88rem;
            font-weight: 600;
            color: #334155;
        }

        .fs-rating-options {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            width: 100%;
        }

        .fs-rating-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 16px;
            border-radius: 4px;
            border: none;
            background: transparent;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: capitalize;
            letter-spacing: 0.01em;
            cursor: pointer;
            transition: all 0.1s ease;
            color: #64748b;
            position: relative;
            flex: 1 1 auto;
            min-width: 80px;
            text-align: center;
        }

        .fs-rating-btn input[type=radio] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .fs-rating-btn:hover {
            background: rgba(255, 255, 255, 0.5);
            color: #475569;
        }

        .fs-rating-btn:has(input:checked) {
            color: #fff !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* --- Per-level colours (1 = worst → highest = best) --- */
        .fs-rating-1:has(input:checked) {
            background: #dc2626 !important;
        }

        /* Poor      – Red      */
        .fs-rating-2:has(input:checked) {
            background: #d97706 !important;
        }

        /* Fair      – Amber    */
        .fs-rating-3:has(input:checked) {
            background: #2563eb !important;
        }

        /* Good      – Blue     */
        .fs-rating-4:has(input:checked) {
            background: #16a34a !important;
        }

        /* Excellent – Green    */
        .fs-rating-5:has(input:checked) {
            background: #0891b2 !important;
        }

        /* Outstanding – Teal   */
        .fs-rating-6:has(input:checked) {
            background: #7c3aed !important;
        }

        /* Very Excellent – Violet */
        .fs-rating-7:has(input:checked) {
            background: #4f46e5 !important;
        }

        /* Extra level – Indigo */

        /* Form Layout */
        .fs-input {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            color: #0f172a;
            width: 100%;
            background: #fff;
            transition: all 0.15s ease;
        }

        .fs-input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .fs-label {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #475569;
            margin-bottom: 8px;
            display: block;
        }

        .fs-submit-btn {
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 1rem 3rem;
            font-size: 0.85rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .1em;
            transition: all .2s ease;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
        }

        .fs-submit-btn:hover {
            background: #4338ca;
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        }

        .fs-required {
            color: #ef4444;
            margin-left: 2px;
        }

        /* ISO Toggle Controls */
        .fs-iso-card {
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            padding: 1.25rem;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .fs-pill-group {
            display: flex;
            background: #f1f5f9;
            padding: 2px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .fs-pill {
            padding: 6px 16px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            color: #64748b;
            transition: all 0.1s;
        }

        .fs-pill:has(input:checked) {
            background: #4f46e5;
            color: #fff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .fs-pill input {
            display: none;
        }

        @media print {
            body { 
                background: #fff !important; 
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            .feedback-submit-wrap {
                background: #fff !important;
                padding: 0 !important;
            }
            .fs-card {
                border: none !important;
                box-shadow: none !important;
            }
            .fs-submit-btn, .fs-label::after, .d-print-none {
                display: none !important;
            }
            .fs-input {
                border: none !important;
                border-bottom: 1px dotted #000 !important;
                border-radius: 0 !important;
                background: transparent !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                color: #000 !important;
                box-shadow: none !important;
                -webkit-appearance: none;
            }
            .fs-section-label {
                border-bottom: 1px solid #000 !important;
            }
            .fs-section-title, .fs-label {
                color: #000 !important;
            }
            .fs-section-icon {
                border-color: #000 !important;
                color: #000 !important;
                background: transparent !important;
            }
            .fs-iso-table {
                border: 2px solid #000 !important;
            }
            .fs-iso-table td {
                border: 1px solid #000 !important;
            }
            p.text-dark {
                color: #000 !important;
            }
            .fs-identity-card {
                background: transparent !important;
                border: 1px solid #000 !important;
            }
            .fs-rating-row {
                border: 1px solid #000 !important;
                break-inside: avoid;
                box-shadow: none !important;
            }
            .fs-rating-options {
                background: transparent !important;
                border: none !important;
            }
            .fs-rating-btn {
                border: 1px solid #000 !important;
                color: #000 !important;
            }
            .fs-pill-group {
                background: transparent !important;
                border: none !important;
            }
            .fs-pill {
                border: 1px solid #000 !important;
                color: #000 !important;
            }
            .bg-light { background: transparent !important; }
        }

        .gap-1 { gap: 0.25rem; }
        .gap-2 { gap: 0.5rem; }
        .gap-3 { gap: 1rem; }
    </style>

    <div class="container-fluid py-5 px-md-5">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">
                
                <div class="d-flex justify-content-end mb-3 d-print-none w-100">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded shadow-sm" onclick="window.print()" style="background: #fff; border-color: #cbd5e1; color: #334155; font-weight: 600;">
                        <i class="mdi mdi-printer mr-1"></i> Print Form
                    </button>
                </div>

                <div class="fs-card">

                    {{-- ── OFFICIAL ISO HEADER BAND ────────────────────────────────────────── --}}
                    <div class="fs-header-band p-0" style="border-bottom: none;">
                        <table class="w-100 border text-center fs-iso-table" style="border-collapse: collapse; margin-bottom: 0;">
                            <tbody>
                                <tr>
                                    <td class="border p-3 align-middle" style="width: 25%;">
                                        @if($logoUrl)
                                            <img src="{{ $logoUrl }}" alt="{{ $company->name ?? 'NAS SERVAIR' }}" style="max-height: 60px; object-fit: contain;">
                                        @else
                                            <span style="font-weight: 800; color: #1e293b; font-size: 1.2rem; line-height: 1;">NAS<br><span style="color:#2563eb;">SERVAIR</span></span>
                                        @endif
                                    </td>
                                    <td class="border p-3 align-middle" style="width: 50%;">
                                        <h4 class="font-weight-bold mb-1" style="font-size: 1.1rem; text-transform: uppercase;">{{ $company->name ?? 'NAS Airport Services Limited' }}</h4>
                                        <h5 class="font-weight-bold mb-0" style="font-size: 1rem; text-transform: uppercase;">Customer Satisfaction Survey</h5>
                                    </td>
                                    <td class="border p-2 align-middle text-left" style="width: 25%; font-size: 0.8rem; line-height: 1.6;">
                                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                                            <span class="text-muted">Document Ref:</span>
                                            <span class="font-weight-bold">{{ $doc_ref ?? 'LR-05' }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                                            <span class="text-muted">Version:</span>
                                            <span class="font-weight-bold">{{ $doc_version ?? '07' }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Page No:</span>
                                            <span class="font-weight-bold">1 of 1</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="p-4" style="background: #fff; border-bottom: 1px solid #e2e8f0;">
                            <p class="mb-0 text-dark" style="font-size: 0.85rem; line-height: 1.6; text-align: justify; font-style: italic;">
                                Customer feedback is a critical input to our continuous improvement in provision of services to our customers. The NAS Food Laboratory kindly requests you to take a few minutes of your time and give us feedback on our service delivery through this questionnaire.
                            </p>
                        </div>
                    </div>

                    {{-- ── FLASH MESSAGES ─────────────────────────────────────── --}}
                    @if (session()->has('error'))
                        <div class="alert alert-danger mx-5 mt-4 border-0" style="border-radius:4px; font-size:0.85rem;">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if (session()->has('success'))
                        <div class="alert alert-success mx-5 mt-4 border-0" style="border-radius:4px; font-size:0.85rem;">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="card-body px-5 py-5">

                        {{-- ════════════════════════════════════════════════════ --}}
                        {{-- STATE: Link Expired --}}
                        {{-- ════════════════════════════════════════════════════ --}}
                        @if($linkExpired)
                            <div class="text-center py-5">
                                <div class="fs-state-icon" style="color:#64748b;"><i class="mdi mdi-timer-off-outline"></i>
                                </div>
                                <h5 class="font-weight-bold text-dark mb-2">Evaluation Window Closed</h5>
                                <p class="text-muted" style="font-size:0.85rem;">This quality assessment link has expired or
                                    is no longer active.</p>
                                <p class="text-muted small">Please contact Registry or Quality QA for a new reference.</p>
                            </div>

                            {{-- ════════════════════════════════════════════════════ --}}
                            {{-- STATE: Already Submitted --}}
                            {{-- ════════════════════════════════════════════════════ --}}
                        @elseif($linkUsed)
                            <div class="text-center py-5">
                                <div class="fs-state-icon" style="color:#4f46e5;"><i
                                        class="mdi mdi-clipboard-check-outline"></i></div>
                                <h5 class="font-weight-bold text-dark mb-2">Record Already Filed</h5>
                                <p class="text-muted" style="font-size:0.85rem;">Your evaluation for this service reference
                                    has reached our quality system.</p>
                                <p class="text-muted small">Duplicate entries are restricted to maintain data integrity.</p>
                            </div>

                            {{-- ════════════════════════════════════════════════════ --}}
                            {{-- STATE: Submitted Successfully --}}
                            {{-- ════════════════════════════════════════════════════ --}}
                        @elseif($isSubmitted)
                            <div class="text-center py-5">
                                <div class="fs-state-icon text-success"><i class="mdi mdi-check-circle"></i></div>
                                <h5 class="font-weight-extrabold text-dark mb-2">Evaluation Recorded
                                </h5>
                                <p class="text-muted mb-4" style="font-size:0.85rem;">The quality evaluation for
                                    <strong>{{ $contact->customer->name ?? 'Valued Client' }}</strong> was successfully
                                    archived.
                                </p>
                                <div class="mb-4">
                                    <span class="fs-ref-chip"
                                        style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:4px; padding:10px 20px; font-size:1.1rem; color:#0f172a;">
                                        REF: FB{{ str_pad($feedback->id ?? 0, 5, '0', STR_PAD_LEFT) }}
                                    </span>
                                </div>
                                <p class="text-muted small">A system notification has been sent to the Laboratory Quality
                                    Manager.</p>
                            </div>

                            {{-- ════════════════════════════════════════════════════ --}}
                            {{-- STATE: Active Form --}}
                            {{-- ════════════════════════════════════════════════════ --}}
                        @else

                            <form wire:submit.prevent="save">

                                {{-- ── SECTION 1: Contact Information ──────────────────── --}}
                                <div class="mb-5">
                                    <div class="fs-section-label">
                                        <span class="fs-section-icon"><i class="mdi mdi-account-circle-outline"></i></span>
                                        <p class="fs-section-title">Section 1: Contact Information</p>
                                    </div>
                                    <div class="fs-identity-card flex-column align-items-stretch">
                                        <div class="d-flex align-items-center gap-3 mb-3">
                                            <div class="fs-identity-avatar">
                                                {{ strtoupper(substr($contact->customer->name ?? $contact->first_name ?? '?', 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-weight-bold text-dark mb-0"
                                                    style="font-size:0.95rem; letter-spacing: -0.01em;">
                                                    {{ $contact->customer->name ?? '' }}
                                                </p>
                                                <small class="text-muted font-weight-500">Client Organization</small>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="fs-label">Contact Person</label>
                                                <input type="text" class="fs-input bg-white" wire:model="contact_person" readonly>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="fs-label">Position / Title</label>
                                                <input type="text" class="fs-input" wire:model="contact_position" placeholder="e.g. Operations Manager">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="fs-label">Telephone Number(s)</label>
                                                <input type="text" class="fs-input" wire:model="contact_phone" placeholder="e.g. +254 700 000000">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="fs-label">Usage Duration</label>
                                                <select class="fs-input" wire:model="usage_duration">
                                                    <option value="">Select Duration...</option>
                                                    <option value="< 1 year">Less than 1 year</option>
                                                    <option value="1-3 years">1 - 3 years</option>
                                                    <option value="3+ years">More than 3 years</option>
                                                    <option value="In-house lab">In-house laboratory</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── SECTION 2: Service Details ─────────────────── --}}
                                <div class="mb-5">
                                    <div class="fs-section-label">
                                        <span class="fs-section-icon"><i class="mdi mdi-flask-outline"></i></span>
                                        <p class="fs-section-title">Section 2: Service Details</p>
                                    </div>

                                    <div class="mb-4">
                                        <label class="fs-label">Type of Service <span class="fs-required">*</span></label>
                                        <select class="fs-input" wire:model="service_type" style="max-width:320px;">
                                            <option value="Testing">Laboratory Testing</option>
                                            <option value="Sampling">On-site Sampling</option>
                                            <option value="Other">Other Specialized Service</option>
                                        </select>
                                        @if($service_type === 'Other')
                                            <input type="text" class="fs-input mt-2" style="max-width:320px;"
                                                placeholder="Please specify service nature" wire:model="service_type_other">
                                            @error('service_type_other') <div class="text-danger mt-1"
                                            style="font-size:0.75rem; font-weight:600;">{{ $message }}</div> @enderror
                                        @endif
                                        @error('service_type') <div class="text-danger mt-1"
                                        style="font-size:0.75rem; font-weight:600;">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="fs-label">Service / Test Ref. <span
                                                    class="fs-required">*</span></label>
                                            <input type="text" class="fs-input" wire:model="service_reference_no"
                                                placeholder="e.g. TST-2025-001">
                                            @error('service_reference_no') <div class="text-danger mt-1"
                                            style="font-size:0.75rem; font-weight:600;">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="fs-label">Sample / Asset ID</label>
                                            <input type="text" class="fs-input" wire:model="equipment_sample_id"
                                                placeholder="e.g. EQ-2025-045">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="fs-label">Date of issue</label>
                                            <input type="date" class="fs-input" wire:model="results_issued_date">
                                        </div>
                                    </div>
                                </div>

                                {{-- ── SECTION 3: How Did We Do? ──────── --}}
                                <div class="mb-5">
                                    <div class="fs-section-label">
                                        <span class="fs-section-icon"><i class="mdi mdi-matrix"></i></span>
                                        <p class="fs-section-title">Section 3: How Did We Do?</p>
                                    </div>
                                    <p class="text-muted mb-4" style="font-size:0.85rem; font-weight:500;">
                                        Please share your feedback on the categories below. All fields are required. <span
                                            class="fs-required">*</span>
                                    </p>

                                    @foreach($activeMetrics as $metric)
                                        <div class="fs-metric-block mb-4 p-4 border rounded shadow-sm bg-white" style="border-left: 4px solid #4f46e5 !important;">
                                            <div class="mb-3">
                                                <h5 class="font-weight-bold text-dark mb-1">{{ $metric->name }}</h5>
                                                @if($metric->prompt_text)
                                                    <p class="text-muted small mb-0">{{ $metric->prompt_text }}</p>
                                                @endif
                                            </div>
                                            
                                            <div class="fs-rating-options mb-2">
                                                @php
                                                    // Arrange from highest to lowest as requested or standard?
                                                    // The user request shows: [ Poor ] [ Fair ] [ Good ] [ Excellent ] [ Outstanding ] [ Very Excellent ]
                                                    // That's lowest to highest.
                                                @endphp
                                                @for($i = 1; $i <= $metric->max_rating; $i++)
                                                    @php
                                                        $label = $metric->rating_labels[$i] ?? match ($i) {
                                                            1 => 'Poor',
                                                            2 => 'Fair',
                                                            3 => 'Good',
                                                            4 => 'Excellent',
                                                            5 => 'Outstanding',
                                                            6 => 'Very Excellent',
                                                            default => $i
                                                        };
                                                    @endphp
                                                    <label class="fs-rating-btn fs-rating-{{ $i }}">
                                                        <input type="radio" name="metric_{{ $metric->id }}" value="{{ $i }}"
                                                            wire:model="dynamic_ratings.{{ $metric->id }}">
                                                        {{ $label }}
                                                    </label>
                                                @endfor
                                            </div>

                                            @error('dynamic_ratings.' . $metric->id)
                                                <div class="text-danger mt-2"
                                                    style="font-size:0.75rem; font-weight:600;">
                                                    Please provide a rating for this metric.
                                                </div>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>

                                {{-- ── SECTION 4: Tell us about your experience ──────────── --}}
                                <div class="mb-5">
                                    <div class="fs-section-label">
                                        <span class="fs-section-icon"><i
                                                class="mdi mdi-comment-processing-outline"></i></span>
                                        <p class="fs-section-title">Section 4: Tell us about your experience</p>
                                    </div>
                                    <label class="fs-label">What did you particularly appreciate?</label>
                                    <textarea class="fs-input" rows="4" wire:model="specific_feedback"
                                        placeholder="Share any specific details or observations..."></textarea>
                                    @error('specific_feedback') <div class="text-danger mt-1"
                                        style="font-size:0.75rem; font-weight:600;">
                                        {{ $message }}
                                    </div> @enderror
                                </div>

                                {{-- ── SECTION 5: Improvement Suggestions ─────────────── --}}
                                <div class="mb-5">
                                    <div class="fs-section-label">
                                        <span class="fs-section-icon"><i class="mdi mdi-lightbulb-on-outline"></i></span>
                                        <p class="fs-section-title">Section 5: Improvement Suggestions</p>
                                    </div>
                                    <label class="fs-label">How can we improve our services for your next visit?</label>
                                    <textarea class="fs-input" rows="3" wire:model="suggestions"
                                        placeholder="Share any ideas or suggestions for improvement..."></textarea>
                                </div>

                                {{-- ── SECTION 6: Referral & Advocacy ───────────────────────────── --}}
                                <div class="mb-5">
                                    <div class="fs-section-label">
                                        <span class="fs-section-icon"><i class="mdi mdi-account-star-outline"></i></span>
                                        <p class="fs-section-title">Section 6: Referral & Advocacy</p>
                                    </div>
                                    <div class="fs-iso-card">
                                        <span style="font-size:0.85rem; font-weight:600; color:#334155;">Would you recommend
                                            our laboratory to others? <span class="fs-required">*</span></span>
                                        <div class="fs-pill-group">
                                            <label class="fs-pill">
                                                <input type="radio" value="1" wire:model="will_recommend"> Yes
                                            </label>
                                            <label class="fs-pill">
                                                <input type="radio" value="0" wire:model="will_recommend"> No
                                            </label>
                                        </div>
                                    </div>
                                    @error('will_recommend') <div class="text-danger mt-1"
                                    style="font-size:0.75rem; font-weight:600;">{{ $message }}</div> @enderror
                                </div>

                                {{-- ── SECTION 7: Follow-up Consent ───────────────────────────── --}}
                                <div class="mb-5">
                                    <div class="fs-section-label">
                                        <span class="fs-section-icon"><i class="mdi mdi-shield-account-outline"></i></span>
                                        <p class="fs-section-title">Section 7: Follow-up Consent</p>
                                    </div>
                                    <div class="fs-iso-card">
                                        <span style="font-size:0.85rem; font-weight:600; color:#334155;">May we contact you
                                            to follow up on your feedback?</span>
                                        <div class="fs-pill-group">
                                            <label class="fs-pill">
                                                <input type="radio" value="1" wire:model.live="consent_contact"> Yes
                                            </label>
                                            <label class="fs-pill">
                                                <input type="radio" value="0" wire:model.live="consent_contact"> No
                                            </label>
                                        </div>
                                    </div>

                                    @if($consent_contact)
                                        <div class="mt-3">
                                            <label class="fs-label">Preferred Contact Reference</label>
                                            <input type="text" class="fs-input" style="max-width:320px;"
                                                placeholder="e.g. Email or Phone number" wire:model="preferred_contact_method">
                                        </div>
                                    @endif
                                </div>

                                {{-- ── SUBMIT ──────────────────────────────────────── --}}
                                <div class="text-center pt-4 border-top">
                                    <button type="submit" class="fs-submit-btn" wire:loading.attr="disabled">
                                        <span wire:loading.remove>
                                            Submit Feedback
                                        </span>
                                        <span wire:loading>
                                            Sending...
                                        </span>
                                    </button>
                                    <p class="text-muted mt-3 mb-0"
                                        style="font-size:0.65rem; font-weight:500; letter-spacing:0.02em;">
                                        <i class="mdi mdi-shield-check mr-1"></i>Your privacy is important. Feedback is
                                        securely handled.
                                    </p>
                                </div>

                                {{-- ── PRINT FOOTER ──────────────────────────────────────── --}}
                                <div class="fs-print-footer mt-5 pt-4 border-top d-none d-print-block">
                                    <div class="row text-center small">
                                        <div class="col-4">
                                            <div class="border-bottom mb-2 pb-2" style="height: 40px;"></div>
                                            <p class="font-weight-bold mb-0">Approved by:</p>
                                            <p class="text-muted">Laboratory Manager</p>
                                        </div>
                                        <div class="col-4">
                                            <div class="mb-2 pb-2 d-flex flex-column gap-1" style="height: 40px; justify-content: center;">
                                                <div class="d-flex align-items-center justify-content-center gap-2">
                                                    <span class="border px-2">1. Customer</span>
                                                    <span class="border px-2">2. File</span>
                                                </div>
                                            </div>
                                            <p class="font-weight-bold mb-0">Issued to:</p>
                                            <p class="text-muted">Quality Records</p>
                                        </div>
                                        <div class="col-4">
                                            <div class="border-bottom mb-2 pb-2" style="height: 40px; display: flex; align-items: flex-end; justify-content: center;">
                                                {{ now()->format('d/m/Y') }}
                                            </div>
                                            <p class="font-weight-bold mb-0">Date of issue:</p>
                                            <p class="text-muted">System Generated</p>
                                        </div>
                                    </div>
                                </div>

                            </form>

                        @endif

                    </div>
                </div>

                <div class="text-center mt-3 text-muted" style="font-size:0.7rem;">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </div>

            </div>
        </div>
    </div>
</div>