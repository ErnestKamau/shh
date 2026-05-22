<?php

namespace App\Jobs;

use App\Mail\FeedbackCampaignMail;
use App\Models\CRM\CustomerFeedback;
use App\Models\CRM\FeedbackRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendFeedbackEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public string $feedbackId,
        public string $requestId,
        public ?string $companyId = null
    ) {}

    public function handle(): void
    {
        $feedback = CustomerFeedback::find($this->feedbackId);
        if (!$feedback) {
            Log::error("SendFeedbackEmail: CustomerFeedback record not found for ID {$this->feedbackId}");
            return;
        }

        $request = FeedbackRequest::find($this->requestId);
        if (!$request) {
            $feedback->update(['delivery_status' => 'failed']);
            Log::error("SendFeedbackEmail: FeedbackRequest record not found for ID {$this->requestId}");
            return;
        }

        $contact = $feedback->contact;
        if (!$contact || !$contact->email) {
            $feedback->update(['delivery_status' => 'failed']);
            Log::error("SendFeedbackEmail: Missing contact or email for feedback ID {$this->feedbackId}");
            return;
        }

        // Set state to sending_email
        $feedback->update(['delivery_status' => 'sending_email']);

        try {
            // Generate Signed URL
            $feedbackLink = URL::temporarySignedRoute(
                'feedback.form',
                now()->addDays(7),
                [
                    'contact_id' => $contact->id,
                    'token'      => $request->token 
                ]
            );

            $activeCompany = $this->companyId ? \App\Company::find($this->companyId) : getActiveCompany();
            $companyName = $activeCompany?->name ?? config('app.name');
            
            $subject = "We Value Your Feedback - {$companyName}";
            $recipientName = $contact->first_name ?? ($contact->surname ?? 'Valued Customer');
            
            $body = "Thank you for choosing {$companyName}. We are committed to providing you with the best possible service, and your feedback is incredibly important to us.\n\nPlease take a few moments to share your thoughts, suggestions, and experiences with our team by clicking the button below.";

            Mail::to($contact->email)->send(
                new FeedbackCampaignMail($body, $subject, $recipientName, $feedbackLink, $activeCompany)
            );

            // Update status to sent
            $feedback->update([
                'delivery_status' => 'sent',
                'last_reminded_at' => now(), // Initialize last reminded timestamp
            ]);

        } catch (\Throwable $e) {
            $feedback->update(['delivery_status' => 'failed']);
            Log::error("SendFeedbackEmail failed for feedback ID {$this->feedbackId}: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendFeedbackEmail job failed completely for feedback ID {$this->feedbackId}: " . $exception->getMessage());
        $feedback = CustomerFeedback::find($this->feedbackId);
        if ($feedback) {
            $feedback->update(['delivery_status' => 'failed']);
        }
    }
}
