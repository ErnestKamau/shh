<?php

namespace App\Services\SubmissionForm;

use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use App\Services\Commercial\CommercialEnquirySyncService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SubmissionFormInstanceCloneService
{
    public function __construct(
        private readonly SubmissionFormSubmissionService $submissionService,
        private readonly CommercialEnquirySyncService $commercialEnquirySyncService,
    ) {
    }

    /**
     * Clone a submitted (or later) request into a new submitted instance with a fresh form number.
     * Does not copy batches, quotations, intrays, or workflow history.
     */
    public function cloneRequest(SubmissionFormInstance $source): SubmissionFormInstance
    {
        $source->loadMissing(['values', 'submissionForm']);

        if ($source->submissionForm === null) {
            throw new RuntimeException('The selected request has no submission form template.');
        }

        $clone = DB::transaction(function () use ($source): SubmissionFormInstance {
            $clone = $source->replicate([
                'form_number',
                'sequence_number',
                'submitted_at',
                'reviewed_at',
                'reviewed_by',
                'review_notes',
                'additional_info_responded_at',
                'portal_request_id',
                'target_record_type',
                'target_record_id',
                'sampling_schedule_id',
            ]);

            $clone->status = 'submitted';
            $clone->submitted_at = now();
            $clone->submitted_by = Auth::id() ?? $source->submitted_by;
            $clone->reviewed_at = null;
            $clone->reviewed_by = null;
            $clone->review_notes = null;
            $clone->additional_info_responded_at = null;
            $clone->portal_request_id = null;
            $clone->target_record_type = null;
            $clone->target_record_id = null;
            $clone->sampling_schedule_id = null;
            $clone->form_number = null;
            $clone->sequence_number = null;
            $clone->save();

            foreach ($source->values as $value) {
                SubmissionFormInstanceValue::query()->create([
                    'submission_form_instance_id' => $clone->id,
                    'submission_form_element_id' => $value->submission_form_element_id,
                    'array_index' => $value->array_index,
                    'value' => $value->value,
                    'file_path' => $value->file_path,
                ]);
            }

            $this->submissionService->assignFormNumberWithRetry($clone, $source->submissionForm);
            $clone->refresh();

            $fresh = $clone->fresh(['values.element', 'submissionForm', 'crmCustomer']);
            if ($fresh !== null && $this->commercialEnquirySyncService->isCommercialTestRequestForm($fresh)) {
                $this->commercialEnquirySyncService->syncFromSubmittedInstance($fresh);
            }

            return $clone->fresh(['submissionForm', 'values.element', 'sampleSubmissionRequest']) ?? $clone;
        });

        return $clone;
    }
}
