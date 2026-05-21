<?php

namespace App\Services\Portal;

use App\DTOs\Portal\Crm\FeedbackDTO;
use App\DTOs\Portal\Crm\FeedbackMetricDTO;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\CustomerFeedback;
use App\Models\CRM\EvaluationMetric;
use App\Models\CRM\FeedbackRating;
use App\Models\CRM\FeedbackRequest;
use App\Repositories\PortalCrmRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PortalFeedbackService
{
    public function __construct(
        private readonly PortalCrmRepository $repository,
    ) {}

    /**
     * @return list<FeedbackMetricDTO>
     */
    public function metrics(): array
    {
        return $this->repository->activeFeedbackMetrics();
    }

    public function list(string $customerId, int $page, int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginateFeedback($customerId, $perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submitSpontaneous(string $customerId, array $data, ?string $portalAccountId = null): FeedbackDTO
    {
        $contact = $this->resolveContact($customerId, (string) $data['contact_id']);

        return DB::transaction(function () use ($customerId, $data, $contact, $portalAccountId): FeedbackDTO {
            $feedback = CustomerFeedback::query()->create([
                'customer_id' => $customerId,
                'contact_id' => $contact->id,
                'status' => CustomerFeedback::STATUS_SUBMITTED,
                'is_submitted' => false,
                'received_from' => (string) ($data['submitter_name'] ?? $contact->customer?->name ?? 'Portal'),
                'registered_by' => (string) ($data['submitter_name'] ?? 'Portal Customer'),
            ]);

            $this->assignFeedbackCode($feedback);
            $this->applyFeedbackFields($feedback, $data);
            $this->saveRatings($feedback, $data['ratings'] ?? []);
            $feedback->refresh();

            Log::channel('daily')->info('portal.feedback.submitted', [
                'feedback_id' => $feedback->id,
                'customer_id' => $customerId,
                'portal_account_id' => $portalAccountId,
                'mode' => 'spontaneous',
            ]);

            return $this->mapFeedback($feedback);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function completeTokenRequest(string $customerId, array $data, ?string $portalAccountId = null): FeedbackDTO
    {
        $contact = $this->resolveContact($customerId, (string) $data['contact_id']);

        return DB::transaction(function () use ($customerId, $data, $contact, $portalAccountId): FeedbackDTO {
            $feedbackRequest = FeedbackRequest::query()
                ->where('token', (string) $data['token'])
                ->where('contact_id', $contact->id)
                ->where('customer_id', $customerId)
                ->lockForUpdate()
                ->first();

            if (! $feedbackRequest) {
                throw ValidationException::withMessages([
                    'token' => ['Invalid or unknown feedback link.'],
                ]);
            }

            if ($feedbackRequest->status === FeedbackRequest::STATUS_SUBMITTED) {
                throw ValidationException::withMessages([
                    'token' => ['This feedback has already been submitted.'],
                ]);
            }

            if ($feedbackRequest->isExpired()) {
                $feedbackRequest->update(['status' => FeedbackRequest::STATUS_EXPIRED]);
                throw ValidationException::withMessages([
                    'token' => ['This feedback link has expired.'],
                ]);
            }

            if (! $feedbackRequest->feedback_id) {
                throw ValidationException::withMessages([
                    'token' => ['Feedback request is not linked to a feedback record.'],
                ]);
            }

            $feedback = CustomerFeedback::query()->findOrFail($feedbackRequest->feedback_id);
            $this->applyFeedbackFields($feedback, $data);
            $this->saveRatings($feedback, $data['ratings'] ?? []);
            $feedbackRequest->markAsSubmitted($feedback->id);
            $feedback->refresh();

            Log::channel('daily')->info('portal.feedback.submitted', [
                'feedback_id' => $feedback->id,
                'customer_id' => $customerId,
                'portal_account_id' => $portalAccountId,
                'mode' => 'token',
            ]);

            return $this->mapFeedback($feedback);
        });
    }

    private function resolveContact(string $customerId, string $contactId): CustomerContact
    {
        $contact = CustomerContact::query()
            ->with('customer')
            ->where('id', $contactId)
            ->where('crm_customer_id', $customerId)
            ->first();

        if (! $contact) {
            throw ValidationException::withMessages([
                'contact_id' => ['Contact not found for this customer.'],
            ]);
        }

        return $contact;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function applyFeedbackFields(CustomerFeedback $feedback, array $data): void
    {
        $sanitize = static function ($value) {
            $value = is_string($value) ? trim($value) : $value;

            return ($value === '' || $value === null) ? null : strip_tags((string) $value);
        };

        $feedback->update([
            'service_type' => (string) $data['service_type'],
            'service_type_other' => $sanitize($data['service_type_other'] ?? null),
            'service_reference_no' => (string) $data['service_reference_no'],
            'equipment_sample_id' => $sanitize($data['equipment_sample_id'] ?? null),
            'results_issued_date' => $data['results_issued_date'] ?? null,
            'specific_feedback' => $sanitize($data['specific_feedback'] ?? null),
            'suggestions' => $sanitize($data['suggestions'] ?? null),
            'will_recommend' => $data['will_recommend'] ?? null,
            'consent_contact' => (bool) ($data['consent_contact'] ?? false),
            'preferred_contact_method' => $sanitize($data['preferred_contact_method'] ?? null),
            'contact_position' => $sanitize($data['contact_position'] ?? null),
            'contact_phone' => $sanitize($data['contact_phone'] ?? null),
            'usage_duration' => $sanitize($data['usage_duration'] ?? null),
            'status' => CustomerFeedback::STATUS_SUBMITTED,
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);
    }

    /**
     * @param  list<array{evaluation_metric_id: string, rating: int}>  $ratings
     */
    private function saveRatings(CustomerFeedback $feedback, array $ratings): void
    {
        FeedbackRating::query()
            ->where('customer_feedback_id', $feedback->id)
            ->delete();

        if ($ratings === []) {
            return;
        }

        $metricIds = array_column($ratings, 'evaluation_metric_id');
        $metrics = EvaluationMetric::query()
            ->whereIn('id', $metricIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $rows = [];
        $totalPercentage = 0.0;
        $metricsCount = 0;

        foreach ($ratings as $rating) {
            $metricId = (string) $rating['evaluation_metric_id'];
            $score = (int) $rating['rating'];
            $metric = $metrics->get($metricId);

            if (! $metric) {
                throw ValidationException::withMessages([
                    'ratings' => ['One or more evaluation metrics are invalid or inactive.'],
                ]);
            }

            if ($score < 1 || $score > $metric->max_rating) {
                throw ValidationException::withMessages([
                    "ratings.{$metricId}" => ["Rating must be between 1 and {$metric->max_rating}."],
                ]);
            }

            $rows[] = [
                'id' => (string) Str::uuid7(),
                'customer_feedback_id' => $feedback->id,
                'evaluation_metric_id' => $metricId,
                'rating' => $score,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($metric->max_rating > 0) {
                $totalPercentage += $score / $metric->max_rating;
                $metricsCount++;
            }
        }

        FeedbackRating::query()->insert($rows);

        if ($metricsCount > 0) {
            $scaledScore = round(($totalPercentage / $metricsCount) * 5, 2);
            $feedback->update(['rating_overall' => $scaledScore]);
        }
    }

    private function assignFeedbackCode(CustomerFeedback $feedback): void
    {
        if (strlen((string) $feedback->id) < 4) {
            $diff = 4 - strlen((string) $feedback->id);
            $feedback->code = 'FB'.str_repeat('0', $diff).$feedback->id;
        } else {
            $feedback->code = 'FB'.$feedback->id;
        }

        $feedback->save();
    }

    private function mapFeedback(CustomerFeedback $feedback): FeedbackDTO
    {
        return new FeedbackDTO(
            id: (string) $feedback->id,
            code: $feedback->code,
            ratingOverall: $feedback->rating_overall !== null ? (float) $feedback->rating_overall : null,
            submittedAt: $feedback->submitted_at?->toIso8601String(),
        );
    }
}
