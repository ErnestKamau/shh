<?php

namespace App\Services\Commercial;

use App\Models\EnquiryQuotation;
use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;
use App\Services\Billing\QuotationRevisionService;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class EnquiryQuotationService
{
    public function __construct(
        private readonly QuotationRevisionService $quotationRevisionService,
    ) {}

    public function linkEnquiryToQuotation(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $quotation,
        string $linkSource,
        ?string $linkedByUserId = null,
    ): EnquiryQuotation {
        $existing = $this->findEngagement($enquiry, $quotation);
        if ($existing !== null) {
            return $existing;
        }

        return EnquiryQuotation::query()->create([
            'sample_submission_request_id' => $enquiry->id,
            'quotation_header_id' => $quotation->id,
            'link_source' => $linkSource,
            'linked_at' => now(),
            'linked_by_user_id' => $linkedByUserId ?? (Auth::id() !== null ? (string) Auth::id() : null),
        ]);
    }

    public function findEngagement(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $quotation,
    ): ?EnquiryQuotation {
        return EnquiryQuotation::query()
            ->where('sample_submission_request_id', $enquiry->id)
            ->where('quotation_header_id', $quotation->id)
            ->first();
    }

    public function currentEngagement(SampleSubmissionRequest $enquiry): ?EnquiryQuotation
    {
        if ($enquiry->current_quotation_header_id === null) {
            return null;
        }

        return EnquiryQuotation::query()
            ->where('sample_submission_request_id', $enquiry->id)
            ->where('quotation_header_id', $enquiry->current_quotation_header_id)
            ->first();
    }

    public function engagementOrCreate(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $quotation,
        string $linkSource,
    ): EnquiryQuotation {
        return $this->findEngagement($enquiry, $quotation)
            ?? $this->linkEnquiryToQuotation($enquiry, $quotation, $linkSource);
    }

    /**
     * True when the enquiry has been sent this quotation version (pivot first, header fallback for legacy exclusive quotes).
     */
    public function wasSentToCustomer(
        SampleSubmissionRequest $enquiry,
        ?QuotationHeader $quotation = null,
    ): bool {
        $quotation ??= $enquiry->currentQuotation;

        if ($quotation === null && $enquiry->current_quotation_header_id) {
            $quotation = QuotationHeader::query()->find($enquiry->current_quotation_header_id);
        }

        if ($quotation === null) {
            return false;
        }

        $engagement = $this->findEngagement($enquiry, $quotation);
        if ($engagement !== null && $engagement->wasSentToCustomer()) {
            return true;
        }

        if ($this->isEnquiryExclusiveQuotation($quotation)) {
            return $quotation->sent_to_customer_at !== null;
        }

        return $enquiry->quotation_first_sent_to_customer_at !== null;
    }

    public function recordSentToCustomer(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $quotation,
        bool $sentViaPortal,
        bool $sentViaEmail,
        ?CarbonInterface $sentAt = null,
        string $linkSource = EnquiryQuotation::LINK_SOURCE_PROCESS_ENQUIRY_EXISTING,
    ): EnquiryQuotation {
        $engagement = $this->engagementOrCreate($enquiry, $quotation, $linkSource);
        $sentAt ??= now();

        $engagement->sent_to_customer_at = $sentAt;
        $engagement->sent_via_portal = $sentViaPortal || (bool) $engagement->sent_via_portal;
        $engagement->sent_via_email = $sentViaEmail || (bool) $engagement->sent_via_email;
        $engagement->save();

        if ($this->isEnquiryExclusiveQuotation($quotation)) {
            $quotation->sent_to_customer_at = $sentAt;
            $quotation->save();
        }

        return $engagement->fresh() ?? $engagement;
    }

    /**
     * @param  array{
     *     signature?: string|null,
     *     signer_name?: string|null,
     *     contact_id?: string|null,
     *     signed_at?: string|\DateTimeInterface|null,
     *     channel?: string|null,
     * }  $acceptance
     */
    public function recordAcceptance(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $quotation,
        array $acceptance,
        ?CarbonInterface $acceptedAt = null,
        string $linkSource = EnquiryQuotation::LINK_SOURCE_PROCESS_ENQUIRY_EXISTING,
    ): EnquiryQuotation {
        $engagement = $this->engagementOrCreate($enquiry, $quotation, $linkSource);
        $acceptedAt ??= now();

        $signedAt = ! empty($acceptance['signed_at'])
            ? Carbon::parse($acceptance['signed_at'])
            : $acceptedAt;

        $engagement->accepted_at = $acceptedAt;
        $engagement->customer_acceptance_signature = trim((string) ($acceptance['signature'] ?? '')) ?: null;
        $engagement->customer_acceptance_signer_name = trim((string) ($acceptance['signer_name'] ?? '')) ?: null;
        $engagement->customer_acceptance_signed_at = $signedAt;
        $engagement->customer_acceptance_contact_id = filled($acceptance['contact_id'] ?? null)
            ? (string) $acceptance['contact_id']
            : null;
        $engagement->customer_acceptance_channel = filled($acceptance['channel'] ?? null)
            ? (string) $acceptance['channel']
            : null;
        $engagement->save();

        if ($this->isEnquiryExclusiveQuotation($quotation)) {
            $quotation->customer_acceptance_signature = $engagement->customer_acceptance_signature;
            $quotation->customer_acceptance_signer_name = $engagement->customer_acceptance_signer_name;
            $quotation->customer_acceptance_signed_at = $engagement->customer_acceptance_signed_at;
            $quotation->customer_acceptance_contact_id = $engagement->customer_acceptance_contact_id;
            $quotation->customer_acceptance_channel = $engagement->customer_acceptance_channel;
            $quotation->save();
        }

        return $engagement->fresh() ?? $engagement;
    }

    public function optInToRevision(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $revision,
    ): EnquiryQuotation {
        if ($revision->revision_of_quotation_header_id === null) {
            throw new RuntimeException('The selected quotation is not a revision.');
        }

        if ((string) $revision->status !== 'Quote Complete') {
            throw new RuntimeException('Only completed revision quotations can be selected.');
        }

        return DB::transaction(function () use ($enquiry, $revision): EnquiryQuotation {
            $engagement = $this->linkEnquiryToQuotation(
                $enquiry,
                $revision,
                EnquiryQuotation::LINK_SOURCE_REVISION_OPT_IN,
            );

            $enquiry->current_quotation_header_id = $revision->id;
            $enquiry->save();

            return $engagement;
        });
    }

    public function latestCompleteRevisionInFamily(QuotationHeader $header): ?QuotationHeader
    {
        $family = $this->quotationRevisionService->collectRevisionFamily($header);

        return $family
            ->filter(static fn (QuotationHeader $member): bool => (string) $member->status === 'Quote Complete')
            ->sortByDesc(static fn (QuotationHeader $member): int => (int) ($member->revision_number ?? 1))
            ->first();
    }

    public function enquiryHasNewerRevisionAvailable(SampleSubmissionRequest $enquiry): bool
    {
        if ($enquiry->current_quotation_header_id === null) {
            return false;
        }

        $current = QuotationHeader::query()->find($enquiry->current_quotation_header_id);
        if ($current === null) {
            return false;
        }

        $latest = $this->latestCompleteRevisionInFamily($current);
        if ($latest === null) {
            return false;
        }

        return (string) $latest->id !== (string) $current->id;
    }

    public function markPriorRevisionSuperseded(QuotationHeader $completedRevision): void
    {
        $priorId = trim((string) ($completedRevision->revision_of_quotation_header_id ?? ''));
        if ($priorId === '') {
            return;
        }

        $prior = QuotationHeader::query()->find($priorId);
        if ($prior === null) {
            return;
        }

        if ($prior->superseded_by_quotation_header_id !== null) {
            return;
        }

        $prior->superseded_by_quotation_header_id = $completedRevision->id;
        $prior->superseded_at = now();
        $prior->save();
    }

    public function formatPickerLabel(QuotationHeader $quotation): string
    {
        $number = trim((string) ($quotation->quote_number ?? ''));
        $revision = max(1, (int) ($quotation->revision_number ?? 1));

        if ($number === '') {
            return 'Rev '.$revision;
        }

        return $number.' Rev '.$revision;
    }

    /**
     * Complete, unexpired quotations for Process Enquiry "use existing" (all revisions, shared quotes allowed).
     *
     * @return Collection<int, QuotationHeader>
     */
    public function eligibleQuotationsForCustomer(string $customerId): Collection
    {
        if ($customerId === '') {
            return collect();
        }

        return QuotationHeader::query()
            ->where('crm_customer_id', $customerId)
            ->where('status', 'Quote Complete')
            ->whereNotNull('expiring_date')
            ->whereDate('expiring_date', '>=', now()->toDateString())
            ->orderBy('quote_number')
            ->orderBy('revision_number')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @return list<array{id: string, label: string, quote_number: string, revision_number: int, family_root_id: string}>
     */
    public function eligibleQuotationPickerOptions(string $customerId): array
    {
        $quotations = $this->eligibleQuotationsForCustomer($customerId);
        $options = [];

        foreach ($quotations as $quotation) {
            $root = $this->quotationRevisionService->findRoot($quotation);
            $options[] = [
                'id' => (string) $quotation->id,
                'label' => $this->formatPickerLabel($quotation),
                'quote_number' => (string) ($quotation->quote_number ?? ''),
                'revision_number' => max(1, (int) ($quotation->revision_number ?? 1)),
                'family_root_id' => (string) $root->id,
            ];
        }

        usort($options, static function (array $a, array $b): int {
            $numberCompare = strcmp($a['quote_number'], $b['quote_number']);
            if ($numberCompare !== 0) {
                return $numberCompare;
            }

            return $a['revision_number'] <=> $b['revision_number'];
        });

        return $options;
    }

    /**
     * @return Collection<int, EnquiryQuotation>
     */
    public function engagementsForQuotation(QuotationHeader $quotation): Collection
    {
        return EnquiryQuotation::query()
            ->where('quotation_header_id', $quotation->id)
            ->with(['enquiry'])
            ->orderByDesc('linked_at')
            ->get();
    }

    /**
     * Shared billing quotations must not claim sample_submission_request_id.
     */
    public function isSharedBillingQuotation(QuotationHeader $quotation): bool
    {
        return trim((string) ($quotation->sample_submission_request_id ?? '')) === ''
            && ! (bool) ($quotation->from_enquiry ?? false);
    }

    private function isEnquiryExclusiveQuotation(QuotationHeader $quotation): bool
    {
        return trim((string) ($quotation->sample_submission_request_id ?? '')) !== '';
    }
}
