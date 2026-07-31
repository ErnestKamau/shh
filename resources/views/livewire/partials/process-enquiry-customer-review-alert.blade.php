@if($enquiryStatus === \App\Models\SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW || $customerFeedbackNotes !== '')
    <div class="alert alert-warning border-warning py-3 mb-3" role="status">
        <div class="d-flex align-items-start" style="gap: 0.75rem;">
            <i class="mdi mdi-comment-alert-outline" style="font-size: 1.35rem; line-height: 1.2;"></i>
            <div class="flex-grow-1">
                <strong class="d-block">
                    @if($enquiryStatus === \App\Models\SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW)
                        Customer sent this quotation back for review
                    @else
                        Customer feedback on quotation
                    @endif
                </strong>
                @if($customerFeedbackNotes !== '')
                    <div class="small mt-2 mb-0" style="white-space: pre-wrap;">{{ $customerFeedbackNotes }}</div>
                @else
                    <div class="small mt-2 mb-0 text-muted">
                        No review reason was recorded. Revise the quotation and send it back to the customer for acceptance.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
