<?php

namespace App\Services\Dashboards;

use App\Services\Dashboards\Concerns\DashboardHelpers;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LabGeneralDashboardService
{
    use DashboardHelpers;

    /**
     * Get geographical distribution of samples for mapping.
     */
    public function getLabGeographicData($year = null): array
    {
        $year = $year ?? date('Y');
        $results = [];
        
        $samples = DB::table('sample_headers')
            ->where('isactive', 1)
            ->whereYear('created_at', $year)
            ->where('status', '!=', 'Completed')
            ->get();

        foreach ($samples as $sample) {
            $details = DB::table('sample_details')
                ->where('sample_header_id', $sample->id)
                ->get();
                
            foreach ($details as $detail) {
                $point = DB::table('sample_points')->find($detail->sample_point_id);
                $gps = null;
                
                if ($point && $point->gps) {
                    $gps = $point->gps;
                } else {
                    $unit = DB::table('crm_company_units')->where('name', $sample->crm_unit_name)->first();
                    if ($unit) {
                        $unitPoint = DB::table('sample_points')->where('crm_company_unit_id', $unit->id)->first();
                        if ($unitPoint && $unitPoint->gps) {
                            $gps = $unitPoint->gps;
                        }
                    }
                }

                if ($gps) {
                    if (!isset($results[$gps])) {
                        $results[$gps] = 0;
                    }
                    $results[$gps]++;
                }
            }
        }

        // Format for Leaflet/Heatchart: [[lat, lng, intensity], ...]
        $formatted = [];
        foreach ($results as $gpsStr => $count) {
            $parts = explode(',', $gpsStr);
            if (count($parts) === 2) {
                $formatted[] = [
                    'lat' => (float) trim($parts[0]),
                    'lng' => (float) trim($parts[1]),
                    'intensity' => $count
                ];
            }
        }

        return $formatted;
    }

    /**
     * Get monthly registration trends (Historical Throughput).
     */
    public function getLabMonthlyTrends($year = null): array
    {
        $year = $year ?? date('Y');
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $counts = [];

        for ($i = 1; $i <= 12; $i++) {
            $counts[] = DB::table('sample_headers')
                ->where('isactive', 1)
                ->whereYear('receipt_date', $year)
                ->whereMonth('receipt_date', $i)
                ->count();
        }

        return [
            'labels' => $months,
            'data' => $counts
        ];
    }

    /**
     * Get top clients by sample volume.
     */
    public function getTopClientsData(int $limit = 10): Collection
    {
        return DB::table('crm_customers')
            ->join('sample_headers', 'sample_headers.crm_customer_id', '=', 'crm_customers.id')
            ->select('crm_customers.name', DB::raw('count(sample_headers.id) as total'))
            ->where('sample_headers.isactive', 1)
            ->groupBy('crm_customers.id', 'crm_customers.name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    /**
     * Get Sunburst Testing Matrix data (Sample Type -> Lab Section).
     */
    public function getTestingMatrixData($year = null): array
    {
        $year = $year ?? date('Y');
        $samples = DB::table('sample_headers')
            ->where('isactive', 1)
            ->whereYear('created_at', $year)
            ->where('status', '!=', 'Completed')
            ->get();

        $sections = DB::table('sample_analysis_stages')->where('active', 1)->get()->keyBy('id');
        $types = DB::table('sample_types')->where('active', 1)->get()->keyBy('id');

        $matrix = [];
        foreach ($samples as $sample) {
            $typeName = isset($types[$sample->sample_type_id]) ? $types[$sample->sample_type_id]->name : 'Unknown';
            if (!isset($matrix[$typeName])) $matrix[$typeName] = [];

            if ($sample->lab_section_ids) {
                $secIds = explode(',', $sample->lab_section_ids);
                foreach ($secIds as $sid) {
                    if (isset($sections[$sid])) {
                        $sname = $sections[$sid]->name;
                        $matrix[$typeName][$sname] = ($matrix[$typeName][$sname] ?? 0) + 1;
                    }
                }
            }
        }

        $formatted = [];
        foreach ($matrix as $type => $secArray) {
            $children = [];
            foreach ($secArray as $sec => $count) {
                $children[] = ['name' => $sec, 'value' => $count];
            }
            $formatted[] = ['name' => $type, 'children' => $children];
        }

        return $formatted;
    }
}
