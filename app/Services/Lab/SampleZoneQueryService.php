<?php

namespace App\Services\Lab;

use App\SampleDetails;
use Illuminate\Database\Eloquent\Builder;

/**
 * Zone filtering removed — returns unrestricted sample detail queries.
 */
class SampleZoneQueryService
{
    /**
     * @param  array<int, string>  $zoneIds
     */
    public function sampleDetailsInZonesQuery(array $zoneIds): Builder
    {
        return SampleDetails::query()
            ->select('sample_details.*')
            ->join('sample_headers', 'sample_headers.id', '=', 'sample_details.sample_header_id');
    }

    public function sampleDetailInZones(string $sampleDetailId, array $zoneIds): bool
    {
        return SampleDetails::query()->whereKey($sampleDetailId)->exists();
    }
}
