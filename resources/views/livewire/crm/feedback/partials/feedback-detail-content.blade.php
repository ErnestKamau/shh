@php $feedback = $feedback ?? null; @endphp
@if($feedback)

    {{-- ── SECTION 1: Service Context ────────────────────────────────────── --}}
    <div class="d-flex align-items-center mb-3">
        <span class="mr-2 d-flex align-items-center justify-content-center rounded"
            style="width:24px;height:24px;background:#eef2ff;flex-shrink:0;">
            <i class="mdi mdi-flask-outline text-primary" style="font-size:0.85rem;"></i>
        </span>
        <span class="text-uppercase font-weight-bold text-muted" style="font-size:0.65rem;letter-spacing:.07em;">Service
            Context</span>
    </div>
    <div class="row mb-4 px-1">
        <div class="col-md-6 mb-2">
            <p class="mb-0 text-muted"
                style="font-size:0.67rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Service Type</p>
            <p class="font-weight-bold text-dark mb-0" style="font-size:0.85rem;">
                {{ $feedback->service_type }}
                @if($feedback->service_type === 'Other')
                    <span class="text-muted">({{ $feedback->service_type_other }})</span>
                @endif
            </p>
        </div>
        <div class="col-md-6 mb-2">
            <p class="mb-0 text-muted"
                style="font-size:0.67rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Service Reference
                No.</p>
            <p class="font-weight-bold text-dark mb-0" style="font-size:0.85rem;font-family:monospace;">
                {{ $feedback->service_reference_no ?? '—' }}
            </p>
        </div>
        <div class="col-md-6 mb-2">
            <p class="mb-0 text-muted"
                style="font-size:0.67rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Equipment / Sample
                ID</p>
            <p class="font-weight-bold text-dark mb-0" style="font-size:0.85rem;">
                {{ $feedback->equipment_sample_id ?? 'N/A' }}
            </p>
        </div>
        <div class="col-md-6 mb-2">
            <p class="mb-0 text-muted"
                style="font-size:0.67rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Results Issued Date
            </p>
            <p class="font-weight-bold text-dark mb-0" style="font-size:0.85rem;">
                {{ $feedback->results_issued_date ?? 'N/A' }}
            </p>
        </div>
        @if(!empty($feedback->business_frequency))
            <div class="col-md-6 mb-2">
                <p class="mb-0 text-muted"
                    style="font-size:0.67rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Business Frequency</p>
                <p class="font-weight-bold text-dark mb-0" style="font-size:0.85rem;">
                    {{ $feedback->business_frequency }}
                </p>
            </div>
        @endif
    </div>

    <hr style="border-color:#f0f0f0;margin:0 0 1rem 0;">

    {{-- ── SECTION 2: Quality Performance Ratings ─────────────────────────── --}}
    <div class="d-flex align-items-center mb-3">
        <span class="mr-2 d-flex align-items-center justify-content-center rounded"
            style="width:24px;height:24px;background:#fffbe6;flex-shrink:0;">
            <i class="mdi mdi-star-outline text-warning" style="font-size:0.85rem;"></i>
        </span>
        <span class="text-uppercase font-weight-bold text-muted" style="font-size:0.65rem;letter-spacing:.07em;">Quality
            Performance Ratings</span>
        @if($feedback->rating_overall)
            @php
                $isTenScale = ($feedback->rating_overall > 5) || ($feedback->ratings()->whereHas('metric', function($q) { $q->where('max_rating', '>', 5); })->exists());
                $maxScale = $isTenScale ? 10 : 5;
                $scorePercent = ($feedback->rating_overall / $maxScale) * 100;
                $badgeClass = $scorePercent >= 80 ? 'badge-success' : ($scorePercent < 50 ? 'badge-danger' : 'badge-warning');
            @endphp
            <span class="badge {{ $badgeClass }} ml-auto py-1 px-2" style="font-size: 0.75rem;">
                Overall Score: {{ number_format($feedback->rating_overall, 1) }} / {{ $maxScale }}
            </span>
        @endif
    </div>

    @php
        $ratingColors = [4 => '#22c55e', 3 => '#3b82f6', 2 => '#f59e0b', 1 => '#ef4444', 0 => '#e2e8f0'];
        $fallbackLabels = [4 => 'Excellent', 3 => 'Good', 2 => 'Fair', 1 => 'Poor'];

        // Ensure ratings are loaded
        if (!$feedback->relationLoaded('ratings')) {
            $feedback->load('ratings.metric');
        }
    @endphp

    @php
        $legacyRatingColumns = [
            'rating_communication' => 'Communication',
            'rating_turnaround' => 'Turnaround Time',
            'rating_technical' => 'Technical Competence',
            'rating_accuracy' => 'Accuracy of Results',
            'rating_reports' => 'Quality of Reports',
            'rating_professionalism' => 'Professionalism',
            'rating_handling' => 'Handling of Complaints',
        ];
        $hasLegacyRatings = false;
        foreach ($legacyRatingColumns as $colKey => $label) {
            $v = $feedback->$colKey ?? null;
            if ($v !== null && (int) $v > 0) {
                $hasLegacyRatings = true;
                break;
            }
        }
    @endphp

    <div class="mb-4 px-1">
        @if($feedback->ratings && $feedback->ratings->count() > 0)
            @foreach($feedback->ratings as $ratingRecord)
                @php
                    $val = (int) $ratingRecord->rating;
                    $max = $ratingRecord->metric->max_rating ?? 4;

                    // Scale color lookup for non-standard scales
                    $colorIndex = $max == 4 ? $val : ceil(($val / $max) * 4);
                    $col = $ratingColors[$colorIndex] ?? $ratingColors[0];

                    // Priority: 1. Configured label, 2. Hardcoded fallback, 3. Numeric value
                    $lbl = ($ratingRecord->metric && !empty($ratingRecord->metric->rating_labels[$val]))
                        ? $ratingRecord->metric->rating_labels[$val]
                        : ($fallbackLabels[$val] ?? $val);

                    $pct = $val > 0 ? ($val / $max) * 100 : 0;
                    $metricName = $ratingRecord->metric ? $ratingRecord->metric->name : 'Unknown Metric';
                @endphp
                <div class="d-flex align-items-center mb-2">
                    <i class="mdi mdi-check-circle-outline text-muted mr-2" style="font-size:0.85rem;width:16px;flex-shrink:0;"></i>
                    <span class="text-dark mr-2" style="font-size:0.78rem;min-width:190px;">{!! $metricName !!}</span>
                    <div class="flex-grow-1 mx-2" style="height:6px;background:#f1f5f9;border-radius:99px;overflow:hidden;">
                        <div style="height:100%;width:{{ $pct }}%;background:{{ $col }};border-radius:99px;transition:width .3s;">
                        </div>
                    </div>
                    <span class="ml-2 font-weight-bold" style="font-size:0.72rem;color:{{ $col }};min-width:60px;">
                        {{ $lbl }} ({{ $val }}/{{ $max }})
                    </span>
                </div>
            @endforeach
        @elseif($hasLegacyRatings)
            @foreach($legacyRatingColumns as $colKey => $metricName)
                @php
                    $val = (int) ($feedback->$colKey ?? 0);
                    $pct = $val > 0 ? ($val / 4) * 100 : 0;
                    $col = $ratingColors[$val] ?? $ratingColors[0];
                    $lbl = $fallbackLabels[$val] ?? $val;
                @endphp
                @if($val > 0)
                    <div class="d-flex align-items-center mb-2">
                        <i class="mdi mdi-check-circle-outline text-muted mr-2" style="font-size:0.85rem;width:16px;flex-shrink:0;"></i>
                        <span class="text-dark mr-2" style="font-size:0.78rem;min-width:190px;">{{ $metricName }}</span>
                        <div class="flex-grow-1 mx-2" style="height:6px;background:#f1f5f9;border-radius:99px;overflow:hidden;">
                            <div style="height:100%;width:{{ $pct }}%;background:{{ $col }};border-radius:99px;transition:width .3s;">
                            </div>
                        </div>
                        <span class="ml-2 font-weight-bold" style="font-size:0.72rem;color:{{ $col }};min-width:60px;">
                            {{ $lbl }} ({{ $val }}/4)
                        </span>
                    </div>
                @endif
            @endforeach
        @else
            <div class="text-muted" style="font-size:0.8rem;">No quality ratings recorded for this feedback.</div>
        @endif
    </div>

    <hr style="border-color:#f0f0f0;margin:0 0 1rem 0;">

    {{-- ── SECTION 3: Specific Feedback ───────────────── --}}
    <div class="d-flex align-items-center mb-3">
        <span class="mr-2 d-flex align-items-center justify-content-center rounded"
            style="width:24px;height:24px;background:#f0fdf4;flex-shrink:0;">
            <i class="mdi mdi-comment-text-multiple-outline text-success" style="font-size:0.85rem;"></i>
        </span>
        <span class="text-uppercase font-weight-bold text-muted" style="font-size:0.65rem;letter-spacing:.07em;">Specific
            Feedback</span>
    </div>

    <div class="px-1 mb-4">
        @if(trim($feedback->specific_feedback ?? '') !== '')
            <div class="p-3 rounded border" style="background:#fafbfd;">
                <p class="mb-0 text-dark" style="font-size:0.85rem;line-height:1.6;white-space: pre-wrap;">{{ $feedback->specific_feedback }}</p>
            </div>
        @else
            <div class="d-flex align-items-center" style="color:#94a3b8;">
                <i class="mdi mdi-information-outline mr-2" style="font-size:1rem;"></i>
                <span style="font-size:0.8rem;font-weight:500;">No specific feedback was provided.</span>
            </div>
        @endif
    </div>

    <hr style="border-color:#f0f0f0;margin:0 0 1rem 0;">

    {{-- ── SECTION 4: Improvement Suggestions ───────────────── --}}
    @if($feedback->suggestions && trim((string) $feedback->suggestions) !== '')
        <div class="d-flex align-items-center mb-3">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:24px;height:24px;background:#eef2ff;flex-shrink:0;">
                <i class="mdi mdi-lightbulb-on-outline text-primary" style="font-size:0.85rem;"></i>
            </span>
            <span class="text-uppercase font-weight-bold text-muted" style="font-size:0.65rem;letter-spacing:.07em;">Improvement Suggestions</span>
        </div>
        <div class="px-1 mb-4">
             <div class="p-3 rounded border" style="background:#f8faff;border-color:#e0e7ff !important;">
                <p class="mb-0 text-secondary" style="font-size:0.82rem;line-height:1.6;">{{ $feedback->suggestions }}</p>
            </div>
        </div>
        <hr style="border-color:#f0f0f0;margin:0 0 1rem 0;">
    @endif

    {{-- ── SECTION 5: Referral & Advocacy ───────────────── --}}
    <div class="d-flex align-items-center mb-3">
        <span class="mr-2 d-flex align-items-center justify-content-center rounded"
            style="width:24px;height:24px;background:#f0f9ff;flex-shrink:0;">
            <i class="mdi mdi-account-star-outline text-info" style="font-size:0.85rem;"></i>
        </span>
        <span class="text-uppercase font-weight-bold text-muted" style="font-size:0.65rem;letter-spacing:.07em;">Referral & Advocacy</span>
    </div>
    <div class="px-1 mb-4">
        <p class="mb-1 text-muted" style="font-size:0.7rem;font-weight:600;text-transform:uppercase;">Would you recommend our services to others?</p>
        @if($feedback->will_recommend === 1 || $feedback->will_recommend === true || $feedback->will_recommend === "1")
            <span class="badge badge-success px-3 py-2" style="font-size:0.8rem;"><i class="mdi mdi-check-circle mr-1"></i> YES, Highly Recommended</span>
        @elseif($feedback->will_recommend === 0 || $feedback->will_recommend === false || $feedback->will_recommend === "0")
            <span class="badge badge-danger px-3 py-2" style="font-size:0.8rem;"><i class="mdi mdi-close-circle mr-1"></i> NO</span>
        @else
            <span class="text-muted" style="font-size:0.8rem;">— Not Specified —</span>
        @endif
    </div>

    @if((!empty($feedback->hear_about_us) && count(array_filter($feedback->hear_about_us)) > 0) || (!empty($feedback->critical_services) && count(array_filter($feedback->critical_services)) > 0))
        <hr style="border-color:#f0f0f0;margin:0 0 1rem 0;">

        {{-- ── SECTION: Survey Discovery & Services ─────────────────── --}}
        <div class="d-flex align-items-center mb-3">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:24px;height:24px;background:#eef2ff;flex-shrink:0;">
                <i class="mdi mdi-compass-outline text-primary" style="font-size:0.85rem;"></i>
            </span>
            <span class="text-uppercase font-weight-bold text-muted" style="font-size:0.65rem;letter-spacing:.07em;">Survey Discovery &amp; Critical Services</span>
        </div>

        <div class="row mb-4 px-1">
            @if(!empty($feedback->hear_about_us) && count(array_filter($feedback->hear_about_us)) > 0)
                <div class="col-md-6 mb-3">
                    <p class="mb-1 text-muted"
                        style="font-size:0.67rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">How they heard about us</p>
                    <div class="d-flex flex-wrap" style="gap: 4px;">
                        @foreach(array_filter($feedback->hear_about_us) as $source)
                            <span class="badge badge-light border text-dark px-2 py-1 mr-1 mb-1" style="font-size:0.75rem; border-radius:4px;">
                                {{ $source }}
                                @if($source === 'Other' && !empty($feedback->hear_about_us_other))
                                    <span class="text-muted">({{ $feedback->hear_about_us_other }})</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($feedback->critical_services) && count(array_filter($feedback->critical_services)) > 0)
                <div class="col-md-6 mb-3">
                    <p class="mb-1 text-muted"
                        style="font-size:0.67rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Critical Services (Ranked Top 3)</p>
                    <ol class="pl-3 mb-0" style="font-size:0.82rem; line-height:1.5;">
                        @foreach(array_filter($feedback->critical_services) as $index => $service)
                            <li class="text-dark font-weight-bold">
                                <span class="text-muted font-weight-normal mr-1">#{{ $index + 1 }}</span> {{ $service }}
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif
        </div>
    @endif

    <hr style="border-color:#f0f0f0;margin:0 0 1rem 0;">

    {{-- ── SECTION 6: Follow-up Consent ───────────────── --}}
    <div class="d-flex align-items-center mb-3">
        <span class="mr-2 d-flex align-items-center justify-content-center rounded"
            style="width:24px;height:24px;background:#fff1f2;flex-shrink:0;">
            <i class="mdi mdi-shield-account-outline text-danger" style="font-size:0.85rem;"></i>
        </span>
        <span class="text-uppercase font-weight-bold text-muted" style="font-size:0.65rem;letter-spacing:.07em;">Follow-up Consent</span>
    </div>
    <div class="row px-1 mb-4">
        <div class="col-md-6">
            <p class="mb-1 text-muted" style="font-size:0.7rem;font-weight:600;text-transform:uppercase;">May we contact you?</p>
            @if($feedback->consent_contact)
                <span class="text-success font-weight-bold" style="font-size:0.85rem;"><i class="mdi mdi-check-circle mr-1"></i> Permission Granted</span>
            @else
                <span class="text-muted" style="font-size:0.85rem;"><i class="mdi mdi-minus-circle-outline mr-1"></i> No Consent Provided</span>
            @endif
        </div>
        @if($feedback->consent_contact && $feedback->preferred_contact_method)
            <div class="col-md-6">
                <p class="mb-1 text-muted" style="font-size:0.7rem;font-weight:600;text-transform:uppercase;">Preferred Method</p>
                <p class="font-weight-bold text-dark mb-0" style="font-size:0.85rem;">{{ $feedback->preferred_contact_method }}</p>
            </div>
        @endif
    </div>

    @php
        $hasIso = !empty(trim((string) ($feedback->iso_impartiality ?? '')))
            || !empty(trim((string) ($feedback->iso_confidentiality ?? '')))
            || !empty(trim((string) ($feedback->iso_concerns_description ?? '')));
    @endphp
    @if($hasIso)
        <hr style="border-color:#f0f0f0;margin:0 0 1rem 0;">
        {{-- ── SECTION: ISO / Compliance ──────────────────────────────────── --}}
        <div class="d-flex align-items-center mb-3">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:24px;height:24px;background:#fef3c7;flex-shrink:0;">
                <i class="mdi mdi-shield-check-outline text-warning" style="font-size:0.85rem;"></i>
            </span>
            <span class="text-uppercase font-weight-bold text-muted" style="font-size:0.65rem;letter-spacing:.07em;">ISO /
                Compliance</span>
        </div>
        <div class="row mb-4 px-1">
            <div class="col-md-6 mb-2">
                <p class="mb-0 text-muted"
                    style="font-size:0.67rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Impartiality</p>
                <p class="font-weight-bold text-dark mb-0" style="font-size:0.85rem;">
                    {{ trim((string) ($feedback->iso_impartiality ?? '')) ?: '—' }}
                </p>
            </div>
            <div class="col-md-6 mb-2">
                <p class="mb-0 text-muted"
                    style="font-size:0.67rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Confidentiality</p>
                <p class="font-weight-bold text-dark mb-0" style="font-size:0.85rem;">
                    {{ trim((string) ($feedback->iso_confidentiality ?? '')) ?: '—' }}
                </p>
            </div>
            @if(trim((string) ($feedback->iso_concerns_description ?? '')) !== '')
                <div class="col-12 mb-2">
                    <p class="mb-0 text-muted"
                        style="font-size:0.67rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">ISO Concerns
                        (description)</p>
                    <p class="font-weight-bold text-dark mb-0" style="font-size:0.85rem;line-height:1.5;white-space: pre-wrap;">
                        {{ $feedback->iso_concerns_description }}</p>
                </div>
            @endif
        </div>
    @endif

    {{-- ── Footer ─────────────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-end pt-3 mt-2 border-top">
        <small class="text-muted" style="font-size:0.67rem;">
            <i class="mdi mdi-calendar-check-outline mr-1"></i>Submitted
            {{ $feedback->created_at->format('d M Y \a\t H:i') }}
        </small>
    </div>

@endif