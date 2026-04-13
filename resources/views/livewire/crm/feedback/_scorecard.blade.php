<div class="scorecard-view">
    <style>
        .scorecard-view .scorecard-header {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px 8px 0 0;
        }

        .scorecard-view .meta-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #718096;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .scorecard-view .meta-value {
            font-size: 1rem;
            color: #2d3748;
            font-weight: 600;
        }

        .scorecard-view .rating-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            height: 100%;
            transition: all 0.2s ease;
        }

        .scorecard-view .rating-box:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
            border-color: #cbd5e0;
        }

        .scorecard-view .rating-title {
            font-size: 0.875rem;
            color: #4a5568;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .scorecard-view .rating-stars {
            color: #ecc94b;
            font-size: 1.25rem;
            letter-spacing: 2px;
        }

        .scorecard-view .rating-stars .empty {
            color: #e2e8f0;
        }

        .scorecard-view .iso-alert {
            background: #fff5f5;
            border-left: 4px solid #f56565;
            padding: 16px;
            border-radius: 4px;
            margin-bottom: 24px;
        }

        .scorecard-view .iso-alert-title {
            color: #c53030;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .scorecard-view .iso-alert-body {
            color: #742a2a;
        }

        .scorecard-view .narrative-section {
            margin-top: 24px;
        }

        .scorecard-view .narrative-box {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }

        .scorecard-view .narrative-box.issues {
            background: #fffff0;
            border-color: #fefcbf;
            color: #744210;
        }

        .scorecard-view .narrative-box.suggestions {
            background: #f0fff4;
            border-color: #c6f6d5;
            color: #22543d;
        }

        .scorecard-view .narrative-title {
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
        }
    </style>

    <div class="scorecard-header">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="meta-label">Service Type</div>
                <div class="meta-value">{{ $feedback->service_type ?? 'N/A' }}</div>
                @if($feedback->service_type === 'Other' && $feedback->service_type_other)
                    <small class="text-muted">({{ $feedback->service_type_other }})</small>
                @endif
            </div>
            <div class="col-md-4">
                <div class="meta-label">Reference No.</div>
                <div class="meta-value">{{ $feedback->service_reference_no ?? 'N/A' }}</div>
            </div>
            <div class="col-md-4">
                <div class="meta-label">Sample/Equip ID</div>
                <div class="meta-value">{{ $feedback->equipment_sample_id ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-4">
                <div class="meta-label">Results Issued Date</div>
                <div class="meta-value">
                    {{ $feedback->results_issued_date ? date('d M Y', strtotime($feedback->results_issued_date)) : 'N/A' }}
                </div>
            </div>
            <div class="col-md-4">
                <div class="meta-label">Overall Rating</div>
                <div class="meta-value">
                    @if($feedback->rating_overall)
                        <span class="badge {{ $feedback->rating_overall >= 4 ? 'badge-success' : ($feedback->rating_overall < 2.5 ? 'badge-danger' : 'badge-warning') }} p-2" style="font-size: 1rem;">
                            {{ $feedback->rating_overall }} / 5
                        </span>
                    @else
                        N/A
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ISO Compliance Alert --}}
    @if($feedback->iso_impartiality === 'No' || $feedback->iso_confidentiality === 'No')
        <div class="iso-alert">
            <div class="iso-alert-title">
                <i class="mdi mdi-alert-circle"></i> ISO COMPLIANCE ALERT
            </div>
            <div class="iso-alert-body">
                @if($feedback->iso_impartiality === 'No')
                    <p class="mb-1"><strong>Impartiality Issue:</strong> Customer reported a concern regarding impartiality.</p>
                @endif
                @if($feedback->iso_confidentiality === 'No')
                    <p class="mb-1"><strong>Confidentiality Issue:</strong> Customer reported a concern regarding
                        confidentiality.</p>
                @endif

                @if($feedback->iso_concerns_description)
                    <div class="mt-2 p-2 bg-white rounded border border-danger">
                        <strong>Customer Detail:</strong> {{ $feedback->iso_concerns_description }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="row mb-4">
        @php
            // Ensure ratings are loaded
            if (!$feedback->relationLoaded('ratings')) {
                $feedback->load('ratings.metric');
            }

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

        @if($feedback->ratings && $feedback->ratings->count() > 0)
            @foreach($feedback->ratings as $ratingRecord)
                @php
                    $score = (int) $ratingRecord->rating;
                    $max = $ratingRecord->metric->max_rating ?? 5;
                    $title = $ratingRecord->metric ? $ratingRecord->metric->name : 'Unknown Metric';
                @endphp
                <div class="col-md-4 mb-3">
                    <div class="rating-box">
                        <div class="rating-title">{{ $title }}</div>
                        <div class="rating-stars" title="{{ $score }}/{{ $max }}">
                            @for($i = 1; $i <= $max; $i++)
                                @if($i <= $score)
                                    <i class="mdi mdi-star"></i>
                                @else
                                    <i class="mdi mdi-star-outline empty"></i>
                                @endif
                            @endfor
                        </div>
                    </div>
                </div>
            @endforeach
        @elseif($hasLegacyRatings)
            @foreach($legacyRatingColumns as $colKey => $title)
                @php
                    $score = (int) ($feedback->$colKey ?? 0);
                    $max = 5; // Legacy was out of 5 usually in this view's hardcoding or 4 in others, but let's stick to 5 for stars
                @endphp
                @if($score > 0)
                    <div class="col-md-4 mb-3">
                        <div class="rating-box">
                            <div class="rating-title">{{ $title }}</div>
                            <div class="rating-stars" title="{{ $score }}/5">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $score)
                                        <i class="mdi mdi-star"></i>
                                    @else
                                        <i class="mdi mdi-star-outline empty"></i>
                                    @endif
                                @endfor
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
    </div>

    <div class="narrative-section">
        @if($feedback->has_issues === 'Yes' || $feedback->issue_description)
            <div class="narrative-box issues">
                <div class="narrative-title">
                    <i class="mdi mdi-alert-octagon"></i> Reported Issues
                </div>
                <p class="mb-0">{{ $feedback->issue_description ?? 'No specific description provided.' }}</p>
                @if($feedback->reported_previously === 'Yes')
                    <small class="d-block mt-2 text-danger font-weight-bold">
                        <i class="mdi mdi-history"></i> This issue has been reported previously.
                    </small>
                @endif
            </div>
        @endif

        @if($feedback->suggestions)
            <div class="narrative-box suggestions">
                <div class="narrative-title">
                    <i class="mdi mdi-lightbulb-on"></i> Suggestions for Improvement
                </div>
                <p class="mb-0">{{ $feedback->suggestions }}</p>
            </div>
        @endif

        @if(!$feedback->has_issues && !$feedback->suggestions)
            <div class="text-center text-muted p-4">
                <i class="mdi mdi-comment-text-outline" style="font-size: 24px;"></i>
                <p>No written narrative feedback provided.</p>
            </div>
        @endif
    </div>

    <div class="row mt-4 pt-3 border-top">
        <div class="col-md-6">
            <strong>Will Use Again:</strong>
            <span
                class="crm-badge {{ $feedback->will_use_again === 'Yes' ? 'crm-badge-success' : 'crm-badge-neutral' }}">
                {{ $feedback->will_use_again ?? 'N/A' }}
            </span>
        </div>
        <div class="col-md-6">
            <strong>Will Recommend:</strong>
            <span
                class="crm-badge {{ $feedback->will_recommend === 'Yes' ? 'crm-badge-success' : 'crm-badge-neutral' }}">
                {{ $feedback->will_recommend ?? 'N/A' }}
            </span>
        </div>
    </div>
</div>