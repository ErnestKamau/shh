<?php

namespace App\Jobs\Sampleworkflow;

use App\Http\Controllers\MailController;
use App\Models\CRM\CustomerNotification;
use App\Models\Sampleworkflow\SampleRejectionLog;
use App\Services\SubmissionForm\SubmissionFormAdditionalInfoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendSampleRejectionNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $sampleRejectionLogId,
    ) {}

    public function handle(SubmissionFormAdditionalInfoService $additionalInfoService): void
    {
        $log = SampleRejectionLog::query()
            ->with(['submissionFormInstance.crmCustomer', 'submissionFormInstance.submittedBy'])
            ->find($this->sampleRejectionLogId);

        if ($log === null) {
            return;
        }

        $instance = $log->submissionFormInstance;
        $customerId = $instance?->crm_customer_id;

        if ($customerId) {
            $reasonSummary = collect($log->reasons ?? [])
                ->map(fn (array $row) => (string) ($row['label'] ?? $row['key'] ?? ''))
                ->filter()
                ->take(3)
                ->implode(', ');

            CustomerNotification::query()->create([
                'customer_id' => $customerId,
                'entity_type' => SampleRejectionLog::class,
                'entity_id' => $log->id,
                'notification_type' => CustomerNotification::TYPE_SAMPLE_REJECTION,
                'notification_description' => 'Your sample request '
                    . ($log->request_no ?: 'submission')
                    . ' has been rejected.'
                    . ($reasonSummary !== '' ? ' Reasons: ' . $reasonSummary . '.' : ''),
            ]);
        }

        if ($instance === null) {
            return;
        }

        $email = $additionalInfoService->resolveCustomerEmail($instance);
        if ($email === null) {
            Log::info('Sample rejection notification skipped: no customer email.', [
                'log_id' => $log->id,
                'instance_id' => $instance->id,
            ]);

            return;
        }

        $displayName = $additionalInfoService->resolveCustomerDisplayName($instance);
        $appName = config('app.name', 'LIMS');
        $formLabel = (string) ($log->request_no ?: $instance->getDocumentControlNumber() ?? 'your submission');

        $reasonRows = collect($log->reasons ?? [])
            ->map(function (array $row) {
                $label = e((string) ($row['label'] ?? $row['key'] ?? 'Reason'));
                $explanation = nl2br(e(trim((string) ($row['explanation'] ?? ''))));

                return '<li><strong>'.$label.'</strong><br>'.$explanation.'</li>';
            })
            ->implode('');

        $body = 'Hi '.e($displayName).',<br><br>'
            .'Your sample request <strong>'.e($formLabel).'</strong> has been rejected by the laboratory.<br><br>'
            .'<strong>Notice:</strong><br>'
            .e($log->integrity_notice).'<br><br>'
            .'<strong>Rejection reasons:</strong><ul>'.$reasonRows.'</ul>'
            .'Please log in to the customer portal for more details.<br><br>'
            .'Regards,<br>'.$appName;

        try {
            (new MailController)->html_email([
                'contacts' => [$email],
                'subject' => '['.$appName.'] Sample request rejected — '.$formLabel,
                'body' => $body,
            ], 'default');
        } catch (Throwable $exception) {
            report($exception);
            Log::warning('Failed to send sample rejection email.', [
                'log_id' => $log->id,
                'email' => $email,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
