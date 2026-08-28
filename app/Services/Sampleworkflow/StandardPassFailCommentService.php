<?php

namespace App\Services\Sampleworkflow;

use App\StandardAnalytes;

class StandardPassFailCommentService
{
    public function commentFor(?string $standardId, ?string $analyteId, ?string $remark): string
    {
        if ($standardId === null || $standardId === '' || $analyteId === null || $analyteId === '') {
            return '';
        }

        if (! in_array($remark, ['PASS', 'FAIL'], true)) {
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
