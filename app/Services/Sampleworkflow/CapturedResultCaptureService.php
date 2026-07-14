<?php

namespace App\Services\Sampleworkflow;

use App\CapturedResult;

class CapturedResultCaptureService
{
    /**
     * Persist capture attributes onto a CapturedResult.
     *
     * Always assigns the acting user as operator (and analyst for TAT).
     * Links analysis_element_id and backfills blank method/unit from the element
     * without overwriting user-edited values.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function applyOnSave(CapturedResult $captured, array $attributes, ?string $actingUserId): CapturedResult
    {
        if ($attributes !== []) {
            $captured->fill($attributes);
        }

        $captured->assignOperator($actingUserId);
        $captured->assignAnalyst($actingUserId);
        $captured->ensureAnalysisElementLinked();
        $captured->applyAnalysisElementDefaults();
        $captured->save();

        return $captured;
    }
}
