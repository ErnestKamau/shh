<div class="feedback-submit-wrap">
    <style>
        .feedback-submit-wrap {
            min-height: 100vh;
            background: #f8fafc;
            font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #1e293b;
            padding-bottom: 3rem;
        }

        .fs-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .fs-header-band {
            background: #fff;
            padding: 2rem 2.5rem;
            color: #1e293b;
            border-bottom: 1px solid #e2e8f0;
        }

        .fs-section-label {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .fs-section-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.15rem;
            background: #f0f9ff;
            color: #0284c7;
            border: 1px solid #e0f2fe;
        }

        .fs-section-title {
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: .05em;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
        }

        .fs-identity-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1.5rem;
        }

        /* Rating Grid Structure */
        .fs-metric-block {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.5rem;
            transition: all 0.2s ease;
        }

        .fs-metric-block:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        .fs-rating-options {
            display: flex;
            justify-content: space-between;
            background: #f1f5f9;
            padding: 6px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            width: 100%;
            gap: 4px;
        }

        .fs-rating-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 38px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: #fff;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            user-select: none;
            position: relative;
            flex: 1;
            margin: 0;
        }

        .fs-rating-btn input[type=radio] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .fs-rating-btn .fs-rating-num {
            font-size: 0.95rem;
            font-weight: 700;
            color: #64748b;
        }

        .fs-rating-btn:hover {
            border-color: #94a3b8;
            transform: scale(1.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            z-index: 2;
        }

        /* Active styling based on Checked radio */
        .fs-rating-btn:has(input:checked) {
            transform: scale(1.1);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
            border-color: transparent !important;
            z-index: 3;
        }

        .fs-rating-btn:has(input:checked) .fs-rating-num {
            color: #fff !important;
        }

        /* Red-to-Teal HSL Visual Scale */
        .fs-rating-val-0:has(input:checked) { background: #ef4444 !important; }
        .fs-rating-val-1:has(input:checked) { background: #f97316 !important; }
        .fs-rating-val-2:has(input:checked) { background: #f97316 !important; }
        .fs-rating-val-3:has(input:checked) { background: #f59e0b !important; }
        .fs-rating-val-4:has(input:checked) { background: #eab308 !important; }
        .fs-rating-val-5:has(input:checked) { background: #84cc16 !important; }
        .fs-rating-val-6:has(input:checked) { background: #22c55e !important; }
        .fs-rating-val-7:has(input:checked) { background: #10b981 !important; }
        .fs-rating-val-8:has(input:checked) { background: #14b8a6 !important; }
        .fs-rating-val-9:has(input:checked) { background: #06b6d4 !important; }
        .fs-rating-val-10:has(input:checked) { background: #0891b2 !important; }

        /* Form Layout */
        .fs-input {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            color: #0f172a;
            width: 100%;
            background: #fff;
            transition: all 0.15s ease;
        }

        .fs-input:focus {
            outline: none;
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
        }

        .fs-label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #475569;
            margin-bottom: 8px;
            display: block;
        }

        /* Checkbox Styling */
        .fs-checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 12px;
        }

        .fs-checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.85rem 1.2rem;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 0;
            font-size: 0.88rem;
            font-weight: 500;
            color: #334155;
            user-select: none;
        }

        .fs-checkbox-label:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .fs-checkbox-label:has(input:checked) {
            background: #f0f9ff;
            border-color: #0284c7;
            color: #0369a1;
            font-weight: 600;
        }

        .fs-checkbox-label input[type="checkbox"] {
            width: 17px;
            height: 17px;
            border-radius: 4px;
            border: 1.5px solid #cbd5e1;
            cursor: pointer;
            accent-color: #0284c7;
        }

        .fs-submit-btn {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 1.1rem 3rem;
            font-size: 0.9rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            transition: all .25s ease;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
        }

        .fs-submit-btn:hover {
            background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
            transform: translateY(-1.5px);
            box-shadow: 0 10px 20px -3px rgba(2, 132, 199, 0.35);
        }

        .fs-submit-btn:disabled {
            background: #94a3b8;
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
        }

        .fs-required {
            color: #ef4444;
            margin-left: 2px;
        }

        /* ISO Toggle Controls */
        .fs-iso-card {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 1.25rem;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .fs-pill-group {
            display: flex;
            background: #e2e8f0;
            padding: 3px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
        }

        .fs-pill {
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            color: #475569;
            transition: all 0.2s;
            margin-bottom: 0;
            user-select: none;
        }

        .fs-pill:has(input:checked) {
            background: #0284c7;
            color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .fs-pill input {
            display: none;
        }

        .thankyou-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 1px solid #bae6fd;
            border-radius: 12px;
            padding: 2rem;
            margin-top: 3rem;
            position: relative;
            overflow: hidden;
        }

        .thankyou-card::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: rgba(2, 132, 199, 0.03);
            border-radius: 50%;
        }

        @media (max-width: 576px) {
            .fs-rating-options {
                flex-wrap: wrap;
                justify-content: center;
            }
            .fs-rating-btn {
                flex: 0 1 calc(20% - 4px);
                min-width: 36px;
                height: 36px;
            }
            .fs-iso-card {
                flex-direction: column;
                align-items: flex-start;
            }
            .fs-pill-group {
                width: 100%;
            }
            .fs-pill {
                flex: 1;
                text-align: center;
            }
            .fs-header-band {
                padding: 1.25rem;
            }
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
            .fs-identity-card {
                background: transparent !important;
                border: 1px solid #000 !important;
            }
            .fs-metric-block {
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
        }
    </style>

    <div class="container-fluid py-4 px-md-5">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">
                
                <div class="d-flex justify-content-end mb-3 d-print-none w-100">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded shadow-sm" onclick="window.print()" style="background: #fff; border-color: #cbd5e1; color: #334155; font-weight: 600;">
                        <i class="mdi mdi-printer mr-1"></i> Print Form
                    </button>
                </div>

                <div class="fs-card">

                    {{-- ── AMSPEC ISO HEADER TABLE ────────────────────────────────────────── --}}
                    <div class="fs-header-band p-0" style="border-bottom: none;">
                        <table class="w-100 border text-center fs-iso-table" style="border-collapse: collapse; margin-bottom: 0; border: 2px solid #334155;">
                            <tbody>
                                <tr>
                                    <td class="border p-3 align-middle" style="width: 25%; border: 1px solid #94a3b8; background: #fafafa;">
                                        @if($logoUrl)
                                            <img src="{{ $logoUrl }}" alt="{{ $company->name ?? 'AmSpec' }}" style="max-height: 55px; object-fit: contain;">
                                        @else
                                            <span style="font-weight: 900; color: #0c4a6e; font-size: 1.5rem; letter-spacing: 0.08em; font-family: 'Montserrat', sans-serif;">AMSPEC</span>
                                        @endif
                                    </td>
                                    <td class="border p-3 align-middle" style="width: 50%; border: 1px solid #94a3b8;">
                                        <h4 class="font-weight-extrabold mb-1" style="font-size: 1.25rem; text-transform: uppercase; color: #0c4a6e; letter-spacing: 0.03em;">{{ $company->name ?? 'AmSpec Services' }}</h4>
                                        <h5 class="font-weight-bold mb-0" style="font-size: 0.95rem; text-transform: uppercase; color: #475569; letter-spacing: 0.05em;">Customer Feedback Record</h5>
                                    </td>
                                    <td class="border p-2 align-middle text-left" style="width: 25%; font-size: 0.75rem; line-height: 1.6; border: 1px solid #94a3b8; background: #f8fafc; color: #334155;">
                                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1" style="border-color: #e2e8f0 !important;">
                                            <span class="text-muted">Doc No:</span>
                                            <span class="font-weight-bold">AMS/QMS/QMF/043</span>
                                        </div>
                                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1" style="border-color: #e2e8f0 !important;">
                                            <span class="text-muted">Revision Date:</span>
                                            <span class="font-weight-bold">12 Oct 2025</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Revision No:</span>
                                            <span class="font-weight-bold">10.2025.R0</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="p-4" style="background: #f0f9ff; border-bottom: 1px solid #bae6fd;">
                            <p class="mb-0 text-dark font-weight-medium" style="font-size: 0.88rem; line-height: 1.6; text-align: justify; color: #0369a1 !important;">
                                AmSpec is committed to providing services that consistently meet or exceed your requirements and expectations. To help us achieve this, please take a moment to evaluate our performance. Your feedback is highly appreciated and will be treated with absolute confidentiality.
                            </p>
                        </div>
                    </div>

                    {{-- ── FLASH MESSAGES ─────────────────────────────────────── --}}
                    @if (session()->has('error'))
                        <div class="alert alert-danger mx-5 mt-4 border-0 shadow-sm" style="border-radius:6px; font-size:0.85rem; background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444;">
                            <i class="mdi mdi-alert-circle mr-2"></i> {{ session('error') }}
                        </div>
                    @endif
                    @if (session()->has('success'))
                        <div class="alert alert-success mx-5 mt-4 border-0 shadow-sm" style="border-radius:6px; font-size:0.85rem; background: #f0fdf4; color: #166534; border-left: 4px solid #22c55e;">
                            <i class="mdi mdi-check-circle mr-2"></i> {{ session('success') }}
                        </div>
                    @endif

                    <div class="card-body px-4 px-md-5 py-4">

                        {{-- ════════════════════════════════════════════════════ --}}
                        {{-- STATE: Link Expired --}}
                        {{-- ════════════════════════════════════════════════════ --}}
                        @if($linkExpired)
                            <div class="text-center py-5">
                                <div class="mb-4" style="font-size: 4rem; color: #94a3b8;">
                                    <i class="mdi mdi-timer-off-outline"></i>
                                </div>
                                <h5 class="font-weight-extrabold text-dark mb-2" style="font-size: 1.25rem;">Evaluation Window Closed</h5>
                                <p class="text-muted" style="font-size:0.88rem; max-width: 480px; margin: 0 auto;">This quality assessment link has expired or is no longer active.</p>
                                <p class="text-muted small mt-2">Please contact our registry or Quality QA team for a new reference link.</p>
                            </div>

                        {{-- ════════════════════════════════════════════════════ --}}
                        {{-- STATE: Already Submitted --}}
                        {{-- ════════════════════════════════════════════════════ --}}
                        @elseif($linkUsed)
                            <div class="text-center py-5">
                                <div class="mb-4" style="font-size: 4rem; color: #0284c7;">
                                    <i class="mdi mdi-clipboard-check-outline"></i>
                                </div>
                                <h5 class="font-weight-extrabold text-dark mb-2" style="font-size: 1.25rem;">Record Already Filed</h5>
                                <p class="text-muted" style="font-size:0.88rem; max-width: 480px; margin: 0 auto;">Your evaluation for this service reference has already been received in our quality system.</p>
                                <p class="text-muted small mt-2">Duplicate entries are restricted to maintain ISO compliance and data integrity.</p>
                            </div>

                        {{-- ════════════════════════════════════════════════════ --}}
                        {{-- STATE: Submitted Successfully --}}
                        {{-- ════════════════════════════════════════════════════ --}}
                        @elseif($isSubmitted)
                            <div class="text-center py-5">
                                <div class="mb-4" style="font-size: 4.5rem; color: #22c55e;">
                                    <i class="mdi mdi-check-circle"></i>
                                </div>
                                <h5 class="font-weight-extrabold text-dark mb-2" style="font-size: 1.5rem;">Thank You for Your Feedback!</h5>
                                <p class="text-muted mb-4" style="font-size:0.92rem; max-width: 520px; margin: 0 auto;">
                                    The quality evaluation for <strong>{{ $contact->customer->name ?? 'your organization' }}</strong> was successfully logged.
                                </p>
                                <div class="mb-4 d-inline-block">
                                    <span class="fs-ref-chip font-weight-bold" style="background:#f1f5f9; border:1px solid #cbd5e1; border-radius:6px; padding:12px 24px; font-size:1.15rem; color:#0f172a; font-family: monospace;">
                                        REF: FB{{ str_pad($feedback->id ?? 0, 5, '0', STR_PAD_LEFT) }}
                                    </span>
                                </div>
                                <p class="text-muted small">Your submission has been filed under ISO audit control records.</p>
                            </div>

                        {{-- ════════════════════════════════════════════════════ --}}
                        {{-- STATE: Active Form --}}
                        {{-- ════════════════════════════════════════════════════ --}}
                        @else

                            <form wire:submit.prevent="save">

                                {{-- ── SECTION 1: Customer Details ──────────────────── --}}
                                <div class="mb-5">
                                    <div class="fs-section-label">
                                        <span class="fs-section-icon"><i class="mdi mdi-account-circle-outline"></i></span>
                                        <h6 class="fs-section-title">1. Customer Demographics</h6>
                                    </div>
                                    <div class="fs-identity-card">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="fs-label">Customer (Company Name)</label>
                                                <input type="text" class="fs-input bg-light" value="{{ $contact->customer->name ?? '' }}" readonly style="font-weight: 600; color: #334155;">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="fs-label">Customer Representative</label>
                                                <input type="text" class="fs-input bg-light" value="{{ $contact_person }}" readonly style="font-weight: 600; color: #334155;">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="fs-label">Location / Office Branch</label>
                                                <input type="text" class="fs-input bg-light" value="{{ $contact->customer->office_address ?? $contact->customer->city ?? 'Central Office' }}" readonly style="font-weight: 600; color: #334155;">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="fs-label">Date of Evaluation</label>
                                                <input type="text" class="fs-input bg-light" value="{{ now()->format('d M Y') }}" readonly style="font-weight: 600; color: #334155;">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── QUESTION 1: Business Frequency ─────────────────── --}}
                                <div class="mb-5">
                                    <div class="fs-section-label">
                                        <span class="fs-section-icon"><i class="mdi mdi-calendar-clock"></i></span>
                                        <h6 class="fs-section-title">Question 1: Business Frequency <span class="fs-required">*</span></h6>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="fs-label">How often do you do business with us?</label>
                                            <select class="fs-input" wire:model="business_frequency">
                                                <option value="">Select an option...</option>
                                                <option value="First time">First time</option>
                                                <option value="Many times a week">Many times a week</option>
                                                <option value="Once a week">Once a week</option>
                                                <option value="Once a month">Once a month</option>
                                                <option value="Once in 6 months">Once in 6 months</option>
                                                <option value="Once a year">Once a year</option>
                                                <option value="Rarely">Rarely</option>
                                            </select>
                                            @error('business_frequency') 
                                                <div class="text-danger mt-1 font-weight-bold" style="font-size:0.75rem;">
                                                    {{ $message }}
                                                </div> 
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- ── QUESTION 2: Services Availed ─────────────────── --}}
                                <div class="mb-5">
                                    <div class="fs-section-label">
                                        <span class="fs-section-icon"><i class="mdi mdi-layers-outline"></i></span>
                                        <h6 class="fs-section-title">Question 2: Services Availed <span class="fs-required">*</span></h6>
                                    </div>
                                    <p class="text-muted small mb-3">Which of our services do you avail? (Select all that apply)</p>
                                    <div class="fs-checkbox-group">
                                        @foreach(['Cargo Inspection', 'Laboratory Testing', 'Calibration Services', 'Agri-Inspection', 'Dry Cargo Inspection'] as $service)
                                            <label class="fs-checkbox-label">
                                                <input type="checkbox" value="{{ $service }}" wire:model="service_type">
                                                <span>{{ $service }}</span>
                                            </label>
                                        @endforeach
                                        <label class="fs-checkbox-label">
                                            <input type="checkbox" value="Other" wire:model="service_type">
                                            <span>Other Services</span>
                                        </label>
                                    </div>

                                    @if(is_array($service_type) && in_array('Other', $service_type))
                                        <div class="mt-3" style="max-width: 480px;">
                                            <label class="fs-label">Please specify the other services:</label>
                                            <input type="text" class="fs-input" placeholder="e.g. Tank Calibration, Bunker Survey..." wire:model="service_type_other">
                                            @error('service_type_other') 
                                                <div class="text-danger mt-1 font-weight-bold" style="font-size:0.75rem;">{{ $message }}</div> 
                                            @enderror
                                        </div>
                                    @endif

                                    @error('service_type') 
                                        <div class="text-danger mt-2 font-weight-bold" style="font-size:0.75rem;">
                                            Please select at least one service.
                                        </div> 
                                    @enderror
                                </div>

                                {{-- ── QUESTION 3: Service Evaluation Metrics ──────── --}}
                                <div class="mb-5">
                                    <div class="fs-section-label">
                                        <span class="fs-section-icon"><i class="mdi mdi-star-half-full"></i></span>
                                        <h6 class="fs-section-title">Question 3: Service Performance Ratings <span class="fs-required">*</span></h6>
                                    </div>
                                    <p class="text-muted small mb-4">
                                        Please evaluate our performance based on the following criteria on a scale of <strong>0 (Poor)</strong> to <strong>10 (Excellent)</strong>.
                                    </p>

                                    <div class="d-flex flex-column gap-3">
                                        @foreach($activeMetrics as $index => $metric)
                                            <div class="fs-metric-block shadow-xs" style="border-left: 4px solid #0284c7 !important;">
                                                <div class="mb-3 d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="font-weight-bold text-dark mb-1" style="font-size: 0.95rem;">
                                                            {{ $index + 1 }}. {{ $metric->name }}
                                                        </h6>
                                                        @if($metric->prompt_text)
                                                            <p class="text-muted small mb-0">{{ $metric->prompt_text }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <div class="fs-rating-options mb-2">
                                                    @for($i = 0; $i <= 10; $i++)
                                                        <label class="fs-rating-btn fs-rating-val-{{ $i }}">
                                                            <input type="radio" name="metric_{{ $metric->id }}" value="{{ $i }}" wire:model="dynamic_ratings.{{ $metric->id }}">
                                                            <span class="fs-rating-num">{{ $i }}</span>
                                                        </label>
                                                    @endfor
                                                </div>

                                                <div class="d-flex justify-content-between text-muted px-2" style="font-size: 0.72rem; font-weight: 600;">
                                                    <span>0 = Poor</span>
                                                    <span>5 = Satisfactory</span>
                                                    <span>10 = Excellent</span>
                                                </div>

                                                @error('dynamic_ratings.' . $metric->id)
                                                    <div class="text-danger mt-2 font-weight-bold" style="font-size:0.75rem;">
                                                        Please select a rating for this metric.
                                                    </div>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- ── QUESTION 5: Recommendation ───────────────────────────── --}}
                                <div class="mb-5">
                                    <div class="fs-section-label">
                                        <span class="fs-section-icon"><i class="mdi mdi-thumb-up-outline"></i></span>
                                        <h6 class="fs-section-title">Question 5: Referral & Advocacy <span class="fs-required">*</span></h6>
                                    </div>
                                    <div class="fs-iso-card">
                                        <span style="font-size:0.88rem; font-weight:600; color:#1e293b;">Would you recommend our services to others?</span>
                                        <div class="fs-pill-group">
                                            <label class="fs-pill">
                                                <input type="radio" value="1" wire:model="will_recommend"> Yes
                                            </label>
                                            <label class="fs-pill">
                                                <input type="radio" value="0" wire:model="will_recommend"> No
                                            </label>
                                        </div>
                                    </div>
                                    @error('will_recommend') 
                                        <div class="text-danger mt-2 font-weight-bold" style="font-size:0.75rem;">
                                            Please indicate your recommendation choice.
                                        </div> 
                                    @enderror
                                </div>

                                {{-- ── QUESTION 6: Comments ───────────────────────────── --}}
                                <div class="mb-5">
                                    <div class="fs-section-label">
                                        <span class="fs-section-icon"><i class="mdi mdi-comment-text-outline"></i></span>
                                        <h6 class="fs-section-title">Question 6: Customer Comments</h6>
                                    </div>
                                    <label class="fs-label">What did you particularly appreciate about our services?</label>
                                    <textarea class="fs-input" rows="4" wire:model="specific_feedback" placeholder="Share any specific details, personnel commendations, or positive observations..."></textarea>
                                </div>

                                {{-- ── QUESTION 7: Discovery Source ─────────────────────────── --}}
                                <div class="mb-5">
                                    <div class="fs-section-label">
                                        <span class="fs-section-icon"><i class="mdi mdi-compass-outline"></i></span>
                                        <h6 class="fs-section-title">Question 7: Service Discovery</h6>
                                    </div>
                                    <p class="text-muted small mb-3">How did you hear about our services? (Select all that apply)</p>
                                    <div class="fs-checkbox-group">
                                        @foreach(['Website', 'Word of Mouth / Referral', 'LinkedIn / Social Media', 'Exhibitions / Conferences', 'Direct Sales / Marketing Representative'] as $source)
                                            <label class="fs-checkbox-label">
                                                <input type="checkbox" value="{{ $source }}" wire:model="hear_about_us">
                                                <span>{{ $source }}</span>
                                            </label>
                                        @endforeach
                                        <label class="fs-checkbox-label">
                                            <input type="checkbox" value="Other" wire:model="hear_about_us">
                                            <span>Other</span>
                                        </label>
                                    </div>

                                    @if(is_array($hear_about_us) && in_array('Other', $hear_about_us))
                                        <div class="mt-3" style="max-width: 480px;">
                                            <label class="fs-label">Please specify the discovery source:</label>
                                            <input type="text" class="fs-input" placeholder="e.g. Tender, Industry Listing..." wire:model="hear_about_us_other">
                                            @error('hear_about_us_other') 
                                                <div class="text-danger mt-1 font-weight-bold" style="font-size:0.75rem;">{{ $message }}</div> 
                                            @enderror
                                        </div>
                                    @endif
                                </div>

                                {{-- ── QUESTION 8: Critical Services ─────────────────────────── --}}
                                <div class="mb-5">
                                    <div class="fs-section-label">
                                        <span class="fs-section-icon"><i class="mdi mdi-flag-outline"></i></span>
                                        <h6 class="fs-section-title">Question 8: Critical Services</h6>
                                    </div>
                                    <p class="text-muted small mb-3">Please list the top 3 services critical to your operations:</p>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="fs-label font-weight-bold">1. Critical Service</label>
                                            <input type="text" class="fs-input" placeholder="Primary Service..." wire:model="critical_services.0">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="fs-label font-weight-bold">2. Critical Service</label>
                                            <input type="text" class="fs-input" placeholder="Secondary Service..." wire:model="critical_services.1">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="fs-label font-weight-bold">3. Critical Service</label>
                                            <input type="text" class="fs-input" placeholder="Tertiary Service..." wire:model="critical_services.2">
                                        </div>
                                    </div>
                                </div>

                                {{-- ── QUESTION 9: Improvements ───────────────────────────── --}}
                                <div class="mb-5">
                                    <div class="fs-section-label">
                                        <span class="fs-section-icon"><i class="mdi mdi-lightbulb-on-outline"></i></span>
                                        <h6 class="fs-section-title">Question 9: Services & Processes Improvements</h6>
                                    </div>
                                    <label class="fs-label">Please specify any services/processes where you would like to see improvements:</label>
                                    <textarea class="fs-input" rows="4" wire:model="suggestions" placeholder="Please outline any areas where we can improve our workflows, TAT, communication, or inspection processes to serve you better..."></textarea>
                                </div>

                                {{-- ── SECTION 10: Follow-up Consent ───────────────────────────── --}}
                                <div class="mb-5">
                                    <div class="fs-section-label">
                                        <span class="fs-section-icon"><i class="mdi mdi-shield-account-outline"></i></span>
                                        <h6 class="fs-section-title">10. Quality Follow-up Consent</h6>
                                    </div>
                                    <div class="fs-iso-card">
                                        <span style="font-size:0.88rem; font-weight:600; color:#1e293b;">May we contact you to follow up on your feedback for service quality review?</span>
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
                                        <div class="mt-3" style="max-width: 480px;">
                                            <label class="fs-label">Preferred Contact Email or Phone</label>
                                            <input type="text" class="fs-input" placeholder="e.g. quality@client.com or +1 234 567..." wire:model="preferred_contact_method">
                                        </div>
                                    @endif
                                </div>

                                {{-- ── THANK YOU BANNER ────────────────────────────────────────── --}}
                                <div class="thankyou-card text-center mb-4">
                                    <p class="mb-0 text-dark font-weight-bold" style="font-size: 0.95rem; line-height: 1.6; color: #0369a1 !important;">
                                        Thank you for taking the time to complete this questionnaire. Your valuable feedback is highly appreciated and will be utilized to improve our service delivery.
                                    </p>
                                </div>

                                {{-- ── SUBMIT BUTTON ──────────────────────────────────────── --}}
                                <div class="text-center pt-4 border-top">
                                    <button type="submit" class="fs-submit-btn" wire:loading.attr="disabled">
                                        <span wire:loading.remove>
                                            Submit Feedback
                                        </span>
                                        <span wire:loading>
                                            Saving Submission...
                                        </span>
                                    </button>
                                    <p class="text-muted mt-3 mb-0" style="font-size:0.68rem; font-weight:500;">
                                        <i class="mdi mdi-shield-check-outline mr-1 text-success"></i> ISO Record Control: This feedback is stored securely and is subject to document control standards.
                                    </p>
                                </div>

                                {{-- ── PRINT FOOTER ──────────────────────────────────────── --}}
                                <div class="fs-print-footer mt-5 pt-4 border-top d-none d-print-block">
                                    <div class="row text-center small">
                                        <div class="col-4">
                                            <div class="border-bottom mb-2 pb-2" style="height: 40px;"></div>
                                            <p class="font-weight-bold mb-0">Approved by:</p>
                                            <p class="text-muted">Quality Manager</p>
                                        </div>
                                        <div class="col-4">
                                            <div class="mb-2 pb-2 d-flex flex-column gap-1" style="height: 40px; justify-content: center;">
                                                <div class="d-flex align-items-center justify-content-center gap-2">
                                                    <span class="border px-2">1. Quality</span>
                                                    <span class="border px-2">2. Management</span>
                                                </div>
                                            </div>
                                            <p class="font-weight-bold mb-0">Issued to:</p>
                                            <p class="text-muted">Compliance Archive</p>
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

                <div class="text-center mt-4 text-muted" style="font-size:0.75rem; font-weight: 500;">
                    &copy; {{ date('Y') }} {{ $company->name ?? 'AmSpec Services' }}. Quality Management System Controlled Record.
                </div>

            </div>
        </div>
    </div>
</div>