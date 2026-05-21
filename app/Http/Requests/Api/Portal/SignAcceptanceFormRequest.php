<?php

namespace App\Http\Requests\Api\Portal;

use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Services\Sampleworkflow\SampleReceivingDisclaimerService;
use App\Services\Sampleworkflow\SampleReceiptNotificationService;
use Illuminate\Foundation\Http\FormRequest;

class SignAcceptanceFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = array_merge([
            'customer_signer_name' => ['required', 'string', 'max:255'],
            'customer_signature' => ['required', 'string'],
            'customer_signed_at' => ['nullable', 'date'],
        ], app(SampleReceiptNotificationService::class)->portalPartialValidationRules());

        $acceptanceForm = $this->route('acceptanceForm');
        if ($acceptanceForm instanceof AnalysisAcceptanceForm && $acceptanceForm->raises_sample_disclaimer) {
            $payload = is_array($acceptanceForm->sample_disclaimer_payload)
                ? $acceptanceForm->sample_disclaimer_payload
                : [];
            if (app(SampleReceivingDisclaimerService::class)->claimantSignatureMissing($payload)) {
                $rules = array_merge($rules, [
                    'sample_disclaimer.claimant_name' => ['required', 'string', 'max:255'],
                    'sample_disclaimer.claimant_signature' => ['required', 'string'],
                    'sample_disclaimer.claimant_signed_at' => ['nullable', 'date'],
                ]);
            } else {
                $rules = array_merge($rules, app(SampleReceivingDisclaimerService::class)->portalClaimantValidationRules());
            }
        }

        return $rules;
    }
}
