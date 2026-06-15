<?php

namespace App\Services\Sampleworkflow;

use App\Models\SampleSubmissionRequest;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\SubmissionFormInstance;
use App\SampleHeader;
use App\User;
use App\Zone;
use Illuminate\Support\Str;

/**
 * Generates zone-year batch codes for the portal acceptance pipeline.
 *
 * AmSpec target convention (YYMMDD + sequential with Micro/Legionella/Chemistry prefixes)
 * is pending stakeholder sign-off — keep zone-year format until confirmed.
 */
class AcceptanceFormBatchCodeService
{
    public function resolveBatchCodeForAcceptanceForm(
        AnalysisAcceptanceForm $form,
        ?string $preferredZoneId = null,
    ): string {
        $instance = $form->submission_form_instance_id
            ? SubmissionFormInstance::query()->with('batches')->find($form->submission_form_instance_id)
            : null;

        if ($instance?->batches?->isNotEmpty()) {
            return (string) $instance->batches->first()->batch_code;
        }

        $zoneCode = $preferredZoneId !== null && $preferredZoneId !== ''
            ? $this->resolveZoneCodeFromZoneId($preferredZoneId)
            : null;

        if ($zoneCode === null && $instance !== null) {
            $zoneCode = $this->resolveZoneCodeFromZoneId($instance->processing_zone_id)
                ?? $this->resolveZoneCodeFromZoneId($instance->zone_id)
                ?? $this->resolveZoneCodeForPreview($instance);
        }

        if ($zoneCode === null) {
            $configPayload = is_array($form->sample_configuration_payload)
                ? $form->sample_configuration_payload
                : [];
            $zoneCode = $this->resolveZoneCodeFromConfigPayload($configPayload);
        }

        return $this->generateZoneYearPreviewCode($zoneCode ?? 'XX');
    }

    public function resolveBatchCode(SubmissionFormInstance $instance): string
    {
        $instance->loadMissing('batches');

        if ($instance->batches->isNotEmpty()) {
            return (string) $instance->batches->first()->batch_code;
        }

        $zoneCode = $this->resolveZoneCodeFromZoneId($instance->processing_zone_id)
            ?? $this->resolveZoneCodeFromZoneId($instance->zone_id)
            ?? $this->resolveZoneCodeForPreview($instance)
            ?? 'XX';

        return $this->generateZoneYearPreviewCode($zoneCode);
    }

    /**
     * @param  list<array<string, mixed>>  $configPayload
     */
    public function resolveZoneCodeFromConfigPayload(array $configPayload): ?string
    {
        foreach ($configPayload as $config) {
            if (! is_array($config)) {
                continue;
            }

            $zoneId = $config['zone_id'] ?? $config['lab_id'] ?? null;
            $zoneCode = $this->resolveZoneCodeFromZoneId($zoneId);

            if ($zoneCode !== null) {
                return $zoneCode;
            }
        }

        return null;
    }

    private function resolveZoneCodeForPreview(SubmissionFormInstance $instance): ?string
    {
        $linkedRequest = $this->resolveLinkedSubmissionRequestFromFormInstance($instance);
        if ($linkedRequest) {
            $zoneCode = $this->normalizeZoneCode($linkedRequest->getAttribute('zone_code'))
                ?? $this->resolveZoneCodeFromZoneId($linkedRequest->getAttribute('zone_id'))
                ?? $this->normalizeZoneCode($linkedRequest->getAttribute('zone'));

            if ($zoneCode !== null) {
                return $zoneCode;
            }
        }

        return $this->normalizeZoneCode($instance->getAttribute('zone_code'))
            ?? $this->resolveZoneCodeFromZoneId($instance->getAttribute('zone_id'))
            ?? $this->resolveZoneCodeFromSubmittingUser($instance);
    }

    private function resolveLinkedSubmissionRequestFromFormInstance(SubmissionFormInstance $instance): ?SampleSubmissionRequest
    {
        foreach ([$instance->portal_request_id, $instance->target_record_id] as $candidateId) {
            if (empty($candidateId)) {
                continue;
            }

            $submissionRequest = SampleSubmissionRequest::query()->where('id', $candidateId)->first();
            if ($submissionRequest) {
                return $submissionRequest;
            }
        }

        return null;
    }

    private function resolveZoneCodeFromSubmittingUser(SubmissionFormInstance $instance): ?string
    {
        if (empty($instance->submitted_by)) {
            return null;
        }

        $user = User::query()->select('id', 'zone_id')->find((string) $instance->submitted_by);

        return $user ? $this->resolveZoneCodeFromZoneId($user->zone_id) : null;
    }

    private function resolveZoneCodeFromZoneId($zoneId): ?string
    {
        if (empty($zoneId)) {
            return null;
        }

        $zone = Zone::query()->select('id', 'key')->find((string) $zoneId);

        return $zone ? $this->normalizeZoneCode($zone->key) : null;
    }

    private function normalizeZoneCode($zone): ?string
    {
        $value = strtoupper(trim((string) $zone));
        if ($value === '') {
            return null;
        }

        $normalized = preg_replace('/[^A-Z0-9]/', '', $value);

        return $normalized !== '' ? $normalized : null;
    }

    private function generateZoneYearPreviewCode(string $zoneCode): string
    {
        $yy = date('y');
        $prefix = strtoupper($zoneCode) . $yy . '-';

        $existingCodes = SampleHeader::query()
            ->where('batch_code', 'like', $prefix . '%')
            ->pluck('batch_code');

        $maxSeq = 0;
        foreach ($existingCodes as $code) {
            $code = (string) $code;
            if (!str_starts_with($code, $prefix)) {
                continue;
            }

            $suffix = substr($code, strlen($prefix));
            if (ctype_digit($suffix)) {
                $maxSeq = max($maxSeq, (int) $suffix);
            }
        }

        return $prefix . sprintf('%04d', $maxSeq + 1);
    }
}
