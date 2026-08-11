<?php

namespace App\Services\SubmissionForm;

use App\Exceptions\Api\Portal\PortalApiException;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\SampleType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PortalSubmissionFormAccess
{
    public function customerIdFromRequest(Request $request): ?string
    {
        $customerId = $request->header('X-CRM-Customer-Id')
            ?? $request->input('crm_customer_id');

        if ($customerId === null || $customerId === '') {
            return null;
        }

        return (string) $customerId;
    }

    public function portalAccountIdFromRequest(Request $request): ?string
    {
        $accountId = $request->header('X-Portal-Account-Id')
            ?? $request->input('portal_account_id');

        if ($accountId === null || $accountId === '') {
            return null;
        }

        return (string) $accountId;
    }

    /**
     * LIMS users.id for the authenticated portal account (gateway maps portal_accounts.lims_user_id).
     */
    public function limsUserIdFromRequest(Request $request): ?string
    {
        $userId = $request->header('X-Lims-User-Id')
            ?? $request->input('lims_user_id');

        if ($userId === null || $userId === '') {
            return null;
        }

        return (string) $userId;
    }

    /**
     * Portal submission instances for a customer (all accounts under that customer).
     */
    public function portalInstancesQuery(string $crmCustomerId): Builder
    {
        return SubmissionFormInstance::query()
            ->where('crm_customer_id', $crmCustomerId)
            ->whereHas('submissionForm', function (Builder $formQuery): void {
                $formQuery->where('is_customer_portal_form', true);
            });
    }

    public function portalFormsQuery(?string $crmCustomerId = null): Builder
    {
        $query = SubmissionForm::query()
            ->where('is_customer_portal_form', true)
            ->where('is_published', true)
            ->where('is_active', true)
            ->where(function (Builder $builder): void {
                $builder->whereNull('placement_slot')
                    ->orWhereJsonContains('placement_slot', 'customer_portal');
            });

        if ($crmCustomerId !== null && Schema::hasTable('submission_form_customers')) {
            $query->where(function (Builder $builder) use ($crmCustomerId): void {
                $builder->whereDoesntHave('customers')
                    ->orWhereHas('customers', function (Builder $customerQuery) use ($crmCustomerId): void {
                        $customerQuery->where('crm_customers.id', $crmCustomerId);
                    });
            });
        }

        return $query;
    }

    public function findPortalForm(string $formId, ?string $crmCustomerId = null): SubmissionForm
    {
        $form = SubmissionForm::query()->where('id', $formId)->first();

        if (! $form) {
            throw PortalApiException::formNotFound();
        }

        if (! $form->canSubmitFromCustomerPortal()) {
            if (! $form->is_customer_portal_form || ! $form->hasPlacementSlot('customer_portal')) {
                throw PortalApiException::formNotPortal();
            }

            if (! $form->is_active) {
                throw PortalApiException::formNotActive();
            }

            if (! $form->is_published) {
                throw PortalApiException::formNotPublished();
            }

            throw PortalApiException::formNotPortal();
        }

        if ($crmCustomerId !== null && Schema::hasTable('submission_form_customers')) {
            $hasCustomerRestrictions = $form->customers()->exists();

            if ($hasCustomerRestrictions && ! $form->customers()->where('crm_customers.id', $crmCustomerId)->exists()) {
                throw PortalApiException::formCustomerNotAllowed();
            }
        }

        return $form;
    }

    public function latestCustomerRequestForm(?string $crmCustomerId = null): ?SubmissionForm
    {
        return $this->portalFormsQuery($crmCustomerId)
            ->where('is_customer_request_form', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
    }

    public function testRequestTemplatesQuery(?string $crmCustomerId = null): Builder
    {
        return $this->portalFormsQuery($crmCustomerId)
            ->where('document_code', 'like', 'TRF-%')
            ->orderBy('name');
    }

    /**
     * Resolve the canonical TRF submission template for a sample type (portal, walk-in, schedule).
     */
    public function testRequestFormForSampleType(string $sampleTypeId, ?string $crmCustomerId = null): ?SubmissionForm
    {
        $sampleTypeId = trim($sampleTypeId);
        if ($sampleTypeId === '') {
            return null;
        }

        return $this->findTestRequestFormForSampleType($sampleTypeId, $crmCustomerId);
    }

    /**
     * Resolve the canonical TRF for a sample type category (by integer category id).
     */
    public function testRequestFormForSampleTypeCategory(int $categoryId, ?string $crmCustomerId = null): ?SubmissionForm
    {
        if (! Schema::hasTable('submission_form_sample_type_categories')) {
            return null;
        }

        return $this->testRequestTemplatesQuery($crmCustomerId)
            ->whereHas('sampleTypeCategories', function (Builder $q) use ($categoryId): void {
                $q->where('sample_type_categories.id', $categoryId);
            })
            ->first();
    }

    /**
     * Resolve a TRF by a raw sample_type_category_id string (from a query param).
     * Returns null when the string is not numeric or the category has no bound TRF.
     */
    public function testRequestFormForSampleTypeCategoryId(string $categoryId, ?string $crmCustomerId = null): ?SubmissionForm
    {
        $categoryId = trim($categoryId);
        if ($categoryId === '' || ! is_numeric($categoryId)) {
            return null;
        }

        return $this->testRequestFormForSampleTypeCategory((int) $categoryId, $crmCustomerId);
    }

    private function findTestRequestFormForSampleType(string $sampleTypeId, ?string $crmCustomerId = null): ?SubmissionForm
    {
        $byDocumentCode = $this->findTestRequestFormBySampleTypeDocumentCode($sampleTypeId);
        if ($byDocumentCode !== null) {
            return $byDocumentCode;
        }

        // Try lookup via the sample type's category pivot (preferred over direct sample-type pivot).
        if (Schema::hasTable('submission_form_sample_type_categories')) {
            $sampleType = SampleType::query()->find($sampleTypeId);
            if ($sampleType && filled($sampleType->sample_type_category)) {
                $byCategoryPivot = $this->testRequestTemplatesQuery($crmCustomerId)
                    ->whereHas('sampleTypeCategories', function (Builder $q) use ($sampleType): void {
                        $q->where('sample_type_categories.id', (int) $sampleType->sample_type_category);
                    })
                    ->first();

                if ($byCategoryPivot !== null) {
                    return $byCategoryPivot;
                }
            }
        }

        $scopedToSampleType = fn (Builder $query) => $query->whereHas(
            'sampleTypes',
            fn (Builder $sampleTypeQuery) => $sampleTypeQuery->where('sample_types.id', $sampleTypeId)
        );

        $trfMatch = $this->testRequestTemplatesQuery($crmCustomerId)
            ->where($scopedToSampleType)
            ->first();

        if ($trfMatch !== null) {
            return $trfMatch;
        }

        $templateMatch = SubmissionForm::query()
            ->where('is_active', true)
            ->where('form_type', 'template')
            ->where(function (Builder $builder): void {
                $builder->where('document_code', 'like', 'TRF-%')
                    ->orWhere('document_code', 'LSR-001')
                    ->orWhereRaw('lower(name) like ?', ['%test request form%']);
            })
            ->where($scopedToSampleType)
            ->orderByRaw("case when document_code like 'TRF-%' then 0 when document_code = 'LSR-001' then 1 else 2 end")
            ->first();

        if ($templateMatch !== null) {
            return $templateMatch;
        }

        return null;
    }

    private function findTestRequestFormBySampleTypeDocumentCode(string $sampleTypeId): ?SubmissionForm
    {
        $sampleType = SampleType::query()->find($sampleTypeId);
        $documentCode = app(TrfDocumentCodeForSampleType::class)->resolve($sampleType);

        if ($documentCode === null) {
            return null;
        }

        return SubmissionForm::query()
            ->where('document_code', $documentCode)
            ->where('is_active', true)
            ->where('form_type', 'template')
            ->first();
    }

    public function assertInstanceBelongsToPortalContext(
        SubmissionFormInstance $instance,
        ?string $crmCustomerId,
        ?string $portalAccountId
    ): void {
        if ($crmCustomerId !== null && (string) $instance->crm_customer_id !== (string) $crmCustomerId) {
            throw PortalApiException::instanceCustomerMismatch();
        }

        // Portal account header is not enforced — any account under the same CRM customer may access/delete.
        // if (
        //     $portalAccountId !== null
        //     && $instance->portal_account_id !== null
        //     && (string) $instance->portal_account_id !== (string) $portalAccountId
        // ) {
        //     throw PortalApiException::instancePortalAccountMismatch();
        // }

        if ($crmCustomerId === null) {
            throw PortalApiException::portalContextHeadersRequired();
        }
    }
}
