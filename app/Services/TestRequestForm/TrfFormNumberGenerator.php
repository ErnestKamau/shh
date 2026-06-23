<?php

namespace App\Services\TestRequestForm;

use App\Models\SubmissionForm;
use App\Models\TestRequestForm;
use App\Models\TestRequestFormInstance;

final class TrfFormNumberGenerator
{
    /**
     * @return array{format: string, sequence_no: int}
     */
    public static function generate(TestRequestForm $template, ?SubmissionForm $portalForm = null): array
    {
        $prefix = $portalForm?->naming_convention_prefix
            ?? strtoupper(str_replace('-', '', (string) ($template->code ?: 'TRF')));

        $sequenceNumber = TestRequestFormInstance::query()
            ->where('test_request_form_id', $template->id)
            ->max('sequence_number');

        if (! $sequenceNumber || $sequenceNumber <= 0) {
            $sequenceNumber = (int) ($portalForm?->start_submission_number ?: 1);
        } else {
            $sequenceNumber = (int) $sequenceNumber + 1;
        }

        $year = date('y');
        $format = self::format($prefix, $sequenceNumber, $year);

        while (TestRequestFormInstance::query()->where('form_number', $format)->exists()) {
            $sequenceNumber++;
            $format = self::format($prefix, $sequenceNumber, $year);
        }

        return [
            'format' => $format,
            'sequence_no' => $sequenceNumber,
        ];
    }

    private static function format(string $prefix, int $sequenceNumber, string $year): string
    {
        return $prefix.sprintf('%03d', $sequenceNumber).'/'.$year;
    }
}
