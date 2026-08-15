<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\CRM\CustomerContact;
use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;
use App\Services\Commercial\QuotationApprovalService;
use App\Services\Commercial\QuotationEmailActionService;
use App\Services\Commercial\QuotationFromEnquiryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class QuotationEmailActionController extends Controller
{
    public function approve(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $quotation,
        string $token,
        QuotationEmailActionService $tokens,
        QuotationApprovalService $approvalService,
        QuotationFromEnquiryService $quotationService,
    ): View|RedirectResponse {
        $enquiry->loadMissing(['customer', 'contact', 'currentQuotation']);
        $quotation = $quotation->fresh() ?? $quotation;

        if (! $tokens->verifyApprovalToken($enquiry, $quotation, $token)) {
            abort(403, 'This approval link is invalid or has expired.');
        }

        if ($approvalService->isApprovedReadyToSend($enquiry, $quotation)
            || $quotationService->quotationWasSentToCustomer($enquiry)
        ) {
            return view('commercial.quotations.email-action-result', [
                'title' => 'Quotation already approved',
                'message' => 'Quotation '.$quotation->quote_number.' was already approved and sent to the customer.',
                'variant' => 'info',
            ]);
        }

        if (! $approvalService->isPendingApproval($enquiry, $quotation)) {
            return view('commercial.quotations.email-action-result', [
                'title' => 'Quotation not awaiting approval',
                'message' => 'This quotation is no longer pending approval.',
                'variant' => 'warning',
            ]);
        }

        try {
            $approvalService->approveViaEmailToken($enquiry, $quotation);
            $enquiry = $enquiry->fresh(['customer', 'contact', 'currentQuotation']) ?? $enquiry;
            $quotation = $enquiry->currentQuotation ?? $quotation;

            $sendPortal = strtolower((string) ($enquiry->source_channel ?? '')) === 'portal';
            $quotationService->sendToCustomer(
                $enquiry,
                $quotation,
                sendPortal: $sendPortal,
                sendEmail: true,
            );
        } catch (RuntimeException $exception) {
            return view('commercial.quotations.email-action-result', [
                'title' => 'Could not approve quotation',
                'message' => $exception->getMessage(),
                'variant' => 'danger',
            ]);
        }

        return view('commercial.quotations.email-action-result', [
            'title' => 'Quotation approved and sent',
            'message' => 'Quotation '.$quotation->quote_number.' has been approved and emailed to the customer.',
            'variant' => 'success',
        ]);
    }

    public function showAcceptForm(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $quotation,
        CustomerContact $contact,
        string $token,
        QuotationEmailActionService $tokens,
        QuotationFromEnquiryService $quotationService,
    ): View|RedirectResponse {
        $enquiry->loadMissing(['customer', 'contact', 'currentQuotation']);
        $quotation = $quotation->fresh() ?? $quotation;

        if ((string) $contact->crm_customer_id !== (string) $enquiry->crm_customer_id) {
            abort(403, 'Invalid acceptance link.');
        }

        if (! $tokens->verifyAcceptanceToken($enquiry, $quotation, (string) $contact->id, $token)) {
            abort(403, 'This acceptance link is invalid or has expired.');
        }

        if ((string) $enquiry->status === SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED) {
            return view('commercial.quotations.email-action-result', [
                'title' => 'Quotation already accepted',
                'message' => 'Quotation '.$quotation->quote_number.' has already been accepted.',
                'variant' => 'info',
            ]);
        }

        if (! $quotationService->quotationWasSentToCustomer($enquiry)) {
            return view('commercial.quotations.email-action-result', [
                'title' => 'Quotation not available',
                'message' => 'This quotation has not been sent to the customer yet.',
                'variant' => 'warning',
            ]);
        }

        if ($contact->hasSignatureImage()) {
            try {
                $quotationService->recordWalkInAcceptance($enquiry->fresh() ?? $enquiry, acceptance: [
                    'signature' => $contact->signatureDataUri(),
                    'signer_name' => trim(implode(' ', array_filter([
                        $contact->first_name ?? '',
                        $contact->middle_name ?? '',
                        $contact->last_name ?? '',
                    ]))),
                    'contact_id' => (string) $contact->id,
                    'channel' => QuotationHeader::ACCEPTANCE_CHANNEL_EMAIL,
                ]);
            } catch (RuntimeException $exception) {
                return view('commercial.quotations.email-action-result', [
                    'title' => 'Could not accept quotation',
                    'message' => $exception->getMessage(),
                    'variant' => 'danger',
                ]);
            }

            return view('commercial.quotations.email-action-result', [
                'title' => 'Quotation accepted',
                'message' => 'Thank you. Quotation '.$quotation->quote_number.' has been accepted using your stored signature.',
                'variant' => 'success',
            ]);
        }

        return view('commercial.quotations.email-accept-sign', [
            'enquiry' => $enquiry,
            'quotation' => $quotation,
            'contact' => $contact,
            'token' => $token,
            'signerName' => trim(implode(' ', array_filter([
                $contact->first_name ?? '',
                $contact->middle_name ?? '',
                $contact->last_name ?? '',
            ]))),
        ]);
    }

    public function submitAcceptance(
        Request $request,
        SampleSubmissionRequest $enquiry,
        QuotationHeader $quotation,
        CustomerContact $contact,
        string $token,
        QuotationEmailActionService $tokens,
        QuotationFromEnquiryService $quotationService,
    ): View|RedirectResponse {
        $enquiry->loadMissing(['customer', 'contact', 'currentQuotation']);

        if ((string) $contact->crm_customer_id !== (string) $enquiry->crm_customer_id) {
            abort(403, 'Invalid acceptance link.');
        }

        if (! $tokens->verifyAcceptanceToken($enquiry, $quotation, (string) $contact->id, $token)) {
            abort(403, 'This acceptance link is invalid or has expired.');
        }

        $validated = $request->validate([
            'signature' => ['required', 'string', 'starts_with:data:image/'],
            'signer_name' => ['required', 'string', 'max:255'],
        ]);

        try {
            $quotationService->recordWalkInAcceptance($enquiry->fresh() ?? $enquiry, acceptance: [
                'signature' => $validated['signature'],
                'signer_name' => $validated['signer_name'],
                'contact_id' => (string) $contact->id,
                'channel' => QuotationHeader::ACCEPTANCE_CHANNEL_EMAIL,
            ]);
        } catch (RuntimeException $exception) {
            return view('commercial.quotations.email-action-result', [
                'title' => 'Could not accept quotation',
                'message' => $exception->getMessage(),
                'variant' => 'danger',
            ]);
        }

        return view('commercial.quotations.email-action-result', [
            'title' => 'Quotation accepted',
            'message' => 'Thank you. Quotation '.$quotation->quote_number.' has been accepted.',
            'variant' => 'success',
        ]);
    }
}
