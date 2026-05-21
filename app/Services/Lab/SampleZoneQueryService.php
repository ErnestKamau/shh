<?php

namespace App\Services\Lab;

use App\SampleDetails;
use Illuminate\Database\Eloquent\Builder;

class SampleZoneQueryService
{
    /**
     * @param  array<int, string>  $zoneIds
     */
    public function sampleDetailsInZonesQuery(array $zoneIds): Builder
    {
        if ($zoneIds === []) {
            return SampleDetails::query()->whereRaw('1 = 0');
        }

        return SampleDetails::query()
            ->select('sample_details.*')
            ->join('sample_headers', 'sample_headers.id', '=', 'sample_details.sample_header_id')
            ->leftJoin('labs', 'labs.id', '=', 'sample_details.lab_id')
            ->where(function (Builder $q) use ($zoneIds): void {
                $q->whereIn('sample_headers.zone_id', $zoneIds)
                    ->orWhereIn('sample_headers.processing_zone_id', $zoneIds)
                    ->orWhereIn('sample_details.processing_zone_id', $zoneIds)
                    ->orWhereIn('labs.zone_id', $zoneIds);
            });
    }

    public function sampleDetailInZones(string $sampleDetailId, array $zoneIds): bool
    {
        return $this->sampleDetailsInZonesQuery($zoneIds)
            ->where('sample_details.id', $sampleDetailId)
            ->exists();
    }
}
