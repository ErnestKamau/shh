<?php

namespace App\Services\Sampleworkflow;

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\SampleHeader;
use App\User;
use App\Zone;
use Illuminate\Support\Str;

class AcceptanceFormBatchCodeService
{
    public function resolveBatchCode(SubmissionFormInstance $instance): string
    {
        $instance->loadMissing('batches');

        if ($instance->batches->isNotEmpty()) {
            return (string) $instance->batches->first()->batch_code;
        }

        $zoneCode = $this->resolveZoneCodeForPreview($instance) ?? 'XX';

        return $this->generateZoneYearPreviewCode($zoneCode);
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

        return $prefix . sprintf('%05d', $maxSeq + 1);
    }
}
