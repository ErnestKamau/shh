<?php

namespace App\Services\AuditModule;

use App\Models\AuditModule\NonConformance;
use App\Models\AuditModule\CorrectiveAction;
use App\Models\AuditModule\RootCauseAnalysis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditStatisticsService
{
    /**
     * Get repeated NCs (similar descriptions)
     */
    public function getRepeatedNCs(?int $companyId = null, int $threshold = 2): array
    {
        $query = NonConformance::whereNotNull('description');
        
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        
        $ncs = $query->get();
        
        $repeated = [];
        $processed = [];
        
        foreach ($ncs as $nc) {
            if (in_array($nc->id, $processed)) {
                continue;
            }
            
            $similar = $this->findSimilarNCs($nc, $ncs, $processed);
            
            if (count($similar) >= $threshold) {
                $repeated[] = [
                    'description' => Str::limit($nc->description, 100),
                    'count' => count($similar),
                    'nc_numbers' => collect($similar)->pluck('nc_number')->toArray(),
                    'first_occurrence' => collect($similar)->min('date_identified'),
                    'last_occurrence' => collect($similar)->max('date_identified'),
                ];
            }
        }
        
        // Sort by count descending
        usort($repeated, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        
        return $repeated;
    }
    
    /**
     * Find similar NCs using fuzzy matching
     */
    private function findSimilarNCs($nc, $allNCs, &$processed): array
    {
        $similar = [$nc];
        $processed[] = $nc->id;
        
        $ncWords = $this->extractKeywords($nc->description);
        
        foreach ($allNCs as $otherNC) {
            if ($otherNC->id === $nc->id || in_array($otherNC->id, $processed)) {
                continue;
            }
            
            $otherWords = $this->extractKeywords($otherNC->description);
            $similarity = $this->calculateSimilarity($ncWords, $otherWords);
            
            if ($similarity >= 0.6) { // 60% similarity threshold
                $similar[] = $otherNC;
                $processed[] = $otherNC->id;
            }
        }
        
        return $similar;
    }
    
    /**
     * Extract keywords from description
     */
    private function extractKeywords(string $text): array
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        $words = explode(' ', $text);
        $words = array_filter($words, function($word) {
            return strlen($word) > 3; // Filter out short words
        });
        return array_unique($words);
    }
    
    /**
     * Calculate similarity between two word arrays
     */
    private function calculateSimilarity(array $words1, array $words2): float
    {
        if (empty($words1) || empty($words2)) {
            return 0;
        }
        
        $intersection = count(array_intersect($words1, $words2));
        $union = count(array_unique(array_merge($words1, $words2)));
        
        return $union > 0 ? $intersection / $union : 0;
    }
    
    /**
     * Get root cause trends (detailed)
     */
    public function getRootCauseTrends(?int $companyId = null, int $months = 12): array
    {
        $startDate = now()->subMonths($months);
        
        $query = RootCauseAnalysis::query();
        
        if ($companyId) {
            $query->whereHas('nonConformance', function($q) use ($companyId, $startDate) {
                $q->where('company_id', $companyId)
                  ->where('date_identified', '>=', $startDate);
            });
        } else {
            $query->where('created_at', '>=', $startDate);
        }
        
        $trends = $query
            ->selectRaw(\auditSqlMonthExpression('created_at') . ' as month, root_cause_description, count(*) as count')
            ->groupBy('month', 'root_cause_description')
            ->orderBy('month')
            ->orderBy('count', 'desc')
            ->get()
            ->groupBy('month')
            ->map(function($monthData) {
                return $monthData->take(5)->pluck('root_cause_description', 'count')->toArray();
            })
            ->toArray();
        
        return $trends;
    }

    /**
     * Get simple monthly count of root cause analyses (for chart)
     */
    public function getRootCauseMonthlyCount(?int $companyId = null, int $months = 12): array
    {
        $startDate = now()->subMonths($months);
        
        $query = RootCauseAnalysis::where('created_at', '>=', $startDate);
        
        if ($companyId) {
            $query->whereHas('nonConformance', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }
        
        $monthlyCounts = $query
            ->selectRaw(\auditSqlMonthExpression('created_at') . ' as month, count(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();
        
        return $monthlyCounts;
    }
    
    /**
     * Get NC closure trends by origin/department
     */
    public function getNCClosureTrends(?int $companyId = null): array
    {
        $query = NonConformance::whereNotNull('actual_closure_date')
            ->whereNotNull('date_identified');
        
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        
        $closedNCs = $query->get();
        
        $byOrigin = [];
        $byDepartment = [];
        
        foreach ($closedNCs as $nc) {
            $daysToClose = \Carbon\Carbon::parse($nc->date_identified)
                ->diffInDays(\Carbon\Carbon::parse($nc->actual_closure_date));
            
            // By origin
            if ($nc->origin_name) {
                if (!isset($byOrigin[$nc->origin_name])) {
                    $byOrigin[$nc->origin_name] = ['total' => 0, 'days' => 0];
                }
                $byOrigin[$nc->origin_name]['total']++;
                $byOrigin[$nc->origin_name]['days'] += $daysToClose;
            }
            
            // By department
            if ($nc->department) {
                if (!isset($byDepartment[$nc->department])) {
                    $byDepartment[$nc->department] = ['total' => 0, 'days' => 0];
                }
                $byDepartment[$nc->department]['total']++;
                $byDepartment[$nc->department]['days'] += $daysToClose;
            }
        }
        
        // Calculate averages
        foreach ($byOrigin as $origin => &$data) {
            $data['avg_days'] = $data['total'] > 0 ? round($data['days'] / $data['total'], 1) : 0;
        }
        
        foreach ($byDepartment as $dept => &$data) {
            $data['avg_days'] = $data['total'] > 0 ? round($data['days'] / $data['total'], 1) : 0;
        }
        
        return [
            'by_origin' => $byOrigin,
            'by_department' => $byDepartment,
        ];
    }
    
    /**
     * Get CAPA effectiveness trends
     */
    public function getCAPAEffectivenessTrends(?int $companyId = null): array
    {
        $query = CorrectiveAction::whereHas('latestVerification')
            ->with('latestVerification');
        
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        
        $verifiedCAPAs = $query->get();
        
        $trends = [];
        
        foreach ($verifiedCAPAs as $capa) {
            if (!$capa->latestVerification || !$capa->latestVerification->verification_date) {
                continue;
            }
            
            $month = \Carbon\Carbon::parse($capa->latestVerification->verification_date)
                ->format('Y-m');
            
            if (!isset($trends[$month])) {
                $trends[$month] = ['total' => 0, 'effective' => 0];
            }
            
            $trends[$month]['total']++;
            
            if ($capa->latestVerification->result_name === 'Effective') {
                $trends[$month]['effective']++;
            }
        }
        
        // Calculate effectiveness rate
        foreach ($trends as $month => &$data) {
            $data['effectiveness_rate'] = $data['total'] > 0 
                ? round(($data['effective'] / $data['total']) * 100, 1) 
                : 0;
        }
        
        ksort($trends);
        
        return $trends;
    }
    
    /**
     * Get top root cause methods
     */
    public function getTopRootCauseMethods(?int $companyId = null): array
    {
        $query = RootCauseAnalysis::query();
        
        if ($companyId) {
            $query->whereHas('nonConformance', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }
        
        return $query
            ->whereNotNull('method_name')
            ->selectRaw('method_name, count(*) as count')
            ->groupBy('method_name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->pluck('count', 'method_name')
            ->toArray();
    }
}






