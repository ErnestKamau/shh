<?php

namespace App\Services\Sampleworkflow;

use App\StandardAnalytes;
use App\Standards;

class StandardPassFailCommentService
{
    public function commentFor(?string $standardId, ?string $analyteId, ?string $remark): string
    {
        if ($standardId === null || $standardId === '' || ! in_array($remark, ['PASS', 'FAIL'], true)) {
            return '';
        }

        $standard = Standards::query()->find($standardId);
        if ($standard !== null) {
            $comment = $remark === 'PASS'
                ? (string) ($standard->pass_comment ?? '')
                : (string) ($standard->fail_comment ?? '');
            $comment = trim($comment);
            if ($comment !== '') {
                return $comment;
            }
        }

        // Fallback for legacy data still stored on standards_analytes.
        if ($analyteId === null || $analyteId === '') {
            return '';
        }

        $row = StandardAnalytes::query()
            ->where('standard_id', $standardId)
            ->where('analyte_id', $analyteId)
            ->first();

        if ($row === null) {
            return '';
        }

        $comment = $remark === 'PASS'
            ? (string) ($row->pass_comment ?? '')
            : (string) ($row->fail_comment ?? '');

        return trim($comment);
    }
}
