<?php

namespace App\Jobs\Commercial;

use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;
use App\Services\Commercial\QuotationApprovalService;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyQuotationApproversJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public string $quotationHeaderId,
        public ?string $enquiryId,
        public string $actorUserId,
        public bool $notifyEmail = true,
    ) {}

    public function handle(QuotationApprovalService $approvalService): void
    {
        $header = QuotationHeader::query()->find($this->quotationHeaderId);
        $actor = User::query()->find($this->actorUserId);
        $enquiry = $this->enquiryId
            ? SampleSubmissionRequest::query()->find($this->enquiryId)
            : null;

        if ($header === null || $actor === null) {
            Log::warning('NotifyQuotationApproversJob skipped: missing models', [
                'quotation_header_id' => $this->quotationHeaderId,
                'enquiry_id' => $this->enquiryId,
                'actor_user_id' => $this->actorUserId,
            ]);

            return;
        }

        $approvalService->deliverApproverNotifications(
            $header,
            $enquiry,
            $actor,
            $this->notifyEmail,
        );
    }
}
