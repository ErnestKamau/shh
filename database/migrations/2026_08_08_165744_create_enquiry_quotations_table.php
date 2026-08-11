<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiry_quotations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('sample_submission_request_id')->index();
            $table->uuid('quotation_header_id')->index();
            $table->string('link_source', 64)->index();
            $table->timestamp('linked_at');
            $table->string('linked_by_user_id', 36)->nullable()->index();

            /**
             * Per-enquiry send/accept for this (enquiry, quotation version) pair.
             *
             * IMPORTANT — do NOT use quotation_headers.sent_to_customer_at or
             * quotation_headers.customer_acceptance_* to mean "this enquiry sent/accepted".
             * Those header columns are for billing-only / document-level events when NO enquiry
             * is involved, or for enquiry-EXCLUSIVE quotes (sample_submission_request_id set)
             * during legacy migration. When an enquiry is linked via this pivot, read and write
             * send/accept HERE.
             */
            $table->timestamp('sent_to_customer_at')->nullable();
            $table->boolean('sent_via_portal')->default(false);
            $table->boolean('sent_via_email')->default(false);
            $table->timestamp('accepted_at')->nullable();
            $table->longText('customer_acceptance_signature')->nullable();
            $table->string('customer_acceptance_signer_name')->nullable();
            $table->timestamp('customer_acceptance_signed_at')->nullable();
            $table->uuid('customer_acceptance_contact_id')->nullable();
            $table->string('customer_acceptance_channel', 32)->nullable();

            $table->timestamps();

            $table->unique(
                ['sample_submission_request_id', 'quotation_header_id'],
                'enquiry_quotations_enquiry_quotation_unique',
            );
        });

        $this->backfillFromExistingData();
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiry_quotations');
    }

    private function backfillFromExistingData(): void
    {
        if (! Schema::hasTable('sample_submission_requests') || ! Schema::hasTable('quotation_headers')) {
            return;
        }

        $enquiries = DB::table('sample_submission_requests')
            ->whereNotNull('current_quotation_header_id')
            ->get([
                'id',
                'current_quotation_header_id',
                'created_from_quotation_header_id',
                'quotation_first_sent_to_customer_at',
                'quotation_accepted_at',
                'accepted_quotation_header_id',
            ]);

        foreach ($enquiries as $enquiry) {
            $this->insertPivotRowIfMissing(
                (string) $enquiry->id,
                (string) $enquiry->current_quotation_header_id,
                $this->resolveLinkSource($enquiry),
                $enquiry,
            );

            $createdFrom = trim((string) ($enquiry->created_from_quotation_header_id ?? ''));
            $current = trim((string) ($enquiry->current_quotation_header_id ?? ''));
            if ($createdFrom !== '' && $createdFrom !== $current) {
                $this->insertPivotRowIfMissing(
                    (string) $enquiry->id,
                    $createdFrom,
                    'billing_wizard',
                    $enquiry,
                    copyCommercialState: false,
                );
            }

            $acceptedId = trim((string) ($enquiry->accepted_quotation_header_id ?? ''));
            if ($acceptedId !== '' && $acceptedId !== $current) {
                $this->insertPivotRowIfMissing(
                    (string) $enquiry->id,
                    $acceptedId,
                    'billing_wizard',
                    $enquiry,
                );
            }
        }
    }

    /**
     * @param  object{id: mixed, current_quotation_header_id: mixed, created_from_quotation_header_id: mixed, quotation_first_sent_to_customer_at: mixed, quotation_accepted_at: mixed, accepted_quotation_header_id: mixed}  $enquiry
     */
    private function insertPivotRowIfMissing(
        string $enquiryId,
        string $quotationId,
        string $linkSource,
        object $enquiry,
        bool $copyCommercialState = true,
    ): void {
        $exists = DB::table('enquiry_quotations')
            ->where('sample_submission_request_id', $enquiryId)
            ->where('quotation_header_id', $quotationId)
            ->exists();

        if ($exists) {
            return;
        }

        $header = DB::table('quotation_headers')->where('id', $quotationId)->first();
        if ($header === null) {
            return;
        }

        $sentAt = null;
        $acceptedAt = null;
        $signature = null;
        $signerName = null;
        $signedAt = null;
        $contactId = null;
        $channel = null;

        if ($copyCommercialState) {
            $headerLinkedToEnquiry = trim((string) ($header->sample_submission_request_id ?? '')) === $enquiryId;
            $isCurrentOrAccepted = in_array($quotationId, array_filter([
                trim((string) ($enquiry->current_quotation_header_id ?? '')),
                trim((string) ($enquiry->accepted_quotation_header_id ?? '')),
            ]), true);

            if ($headerLinkedToEnquiry || $isCurrentOrAccepted) {
                $sentAt = $header->sent_to_customer_at ?? $enquiry->quotation_first_sent_to_customer_at ?? null;
                $acceptedAt = $enquiry->quotation_accepted_at ?? null;
                $signature = $header->customer_acceptance_signature ?? null;
                $signerName = $header->customer_acceptance_signer_name ?? null;
                $signedAt = $header->customer_acceptance_signed_at ?? null;
                $contactId = $header->customer_acceptance_contact_id ?? null;
                $channel = $header->customer_acceptance_channel ?? null;
            }
        }

        $now = now();

        DB::table('enquiry_quotations')->insert([
            'id' => (string) Str::uuid(),
            'sample_submission_request_id' => $enquiryId,
            'quotation_header_id' => $quotationId,
            'link_source' => $linkSource,
            'linked_at' => $now,
            'linked_by_user_id' => null,
            'sent_to_customer_at' => $sentAt,
            'sent_via_portal' => $sentAt !== null,
            'sent_via_email' => false,
            'accepted_at' => $acceptedAt,
            'customer_acceptance_signature' => $signature,
            'customer_acceptance_signer_name' => $signerName,
            'customer_acceptance_signed_at' => $signedAt,
            'customer_acceptance_contact_id' => $contactId,
            'customer_acceptance_channel' => $channel,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * @param  object{created_from_quotation_header_id: mixed, current_quotation_header_id: mixed}  $enquiry
     */
    private function resolveLinkSource(object $enquiry): string
    {
        $createdFrom = trim((string) ($enquiry->created_from_quotation_header_id ?? ''));
        $current = trim((string) ($enquiry->current_quotation_header_id ?? ''));

        if ($createdFrom !== '' && ($createdFrom === $current || $createdFrom !== '')) {
            return 'billing_wizard';
        }

        return 'process_enquiry_existing';
    }
};
