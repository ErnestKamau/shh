<?php

namespace Modules\QualityControl\Http\Controllers;

use App\Analyte;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\Support\Arrayable;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ComputeQcReportController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('qualitycontrol::index');
    }

    /**
     * Calculate median of the valid result data.
        *@param $validResults
     */
    public function getMedian($validResults){
        $count = count($validResults);
        $isEven = $count % 2  == 0;
        if($isEven){
            $median =  ($validResults[intval($count/2)]->result + $validResults[intval($count/2)-1]->result) / 2;
        }else{
            $median = $validResults[intval($count/2)]->result;
        }
        return $median;
    }
     /**
     * Calculate Robust median of the valid result data.
        *@param $validResults
     */
    public function getMedianIndex($validResults){
        $count = count($validResults);
        $isEven = $count % 2  == 0;
        if($isEven){
            $median =  ($validResults[intval($count/2)] + $validResults[intval($count/2)-1]) / 2;
        }else{
            $median = $validResults[intval($count/2)];
        }
        return $median;
    }

    /**
     * Show the form for creating a new resource.
     * @param 
     */

    public function computeQcReport($validResults,$onlyResultsArr){
        $statisticalPopulationSize = sizeof($validResults);
        // configuraftios
        $sstar_config =  1.483 ;
        $decimal_place = 3;
        $sstar2_const = 1.134;

        // initialize all variables
		$sum = null;
		$robust_average = null;
		$robust_std_deviation = null;
		$robust_cv = null; 
		$xstar = null;
		$median_diff = null;
		$cv_star = null;
		$sd_star = null;
		$z_score = null;
		$sstar = null;
		$delta = null;
		$ysum = null;
		$sumsq = null;
		$sumx = null;
		$oldsstar = 0;
		$oldxstar = 0;

        #processing Area
        $xstar = $this->getMedian($validResults);

        $diff = [];
        $loop =0;
        foreach($onlyResultsArr as $res){
            $diff[$loop] = abs($res-$xstar);
            ++$loop;
        }
        usort($diff,function($a,$b){
            if($a == $b){
                return 0;
            }
            return $a < $b ? -1 : 1;
        });

        $median_diff = $this->getMedianIndex($diff);
        $sstar = 1.483 * $median_diff;

        // the tolerance for convergence
        $delta = pow(10,-$decimal_place);
        while((abs($xstar - $oldxstar) > 0) || (abs($sstar - $oldsstar) > $delta)){
            $phi =1.5 * $sstar;
            $loop = 0;
            foreach($onlyResultsArr as $resdif){
                if($resdif < ($xstar - $phi)){
                    $onlyResultsArr[$loop] = $xstar-$phi;
                }else{
                    $sumXstarPhi = $xstar + $phi;
                    $onlyResultsArr[$loop] = $resdif > $sumXstarPhi ? $sumXstarPhi : $resdif;
                }
                ++$loop;
            }
            $oldsstar = $sstar;
            $oldxstar = $xstar;
            $sumx = 0;
            $sumsq = 0;

            $sumx = array_sum($onlyResultsArr);
            $xstar = $sumx / sizeof($onlyResultsArr);

            foreach($onlyResultsArr as $onlyRes){
                $xdiff = $onlyRes - $xstar;
                $sumsq = $sumsq + ($xdiff * $xdiff);
            }
            $ysum = $sumsq / (sizeof($validResults) - 1);
            $sstar =  $sstar2_const  * sqrt($ysum);
        }
        $robust_average = $xstar;
        $robust_std_deviation = $sstar;
        $robust_cv = $robust_std_deviation * 100 / $robust_average;
        $compute_res = array(
            'robust_average'         => $robust_average,
            'robust_std_deviation'         => $robust_std_deviation,
            'robust_cv'         => $robust_cv,
            'sstar'         => $sstar,
            'statisticalPopulationSize'      => $statisticalPopulationSize
        );

        return $compute_res;

    }

   private function computeNumericResultsModule($analyte_id,$rawResults){
        $analyte = Analyte::find($analyte_id);
        $rawResultsClone = clone $rawResults;
        $raw_valid_results = clone $rawResults;
        $min_value = null;
        $max_value = null;
        $onlyResultsArr = $rawResultsClone->pluck('result')->toarray();
        $validResults = $raw_valid_results->get();
        $min_value = min($onlyResultsArr);
        $max_value = max($onlyResultsArr);

        if ($min_value == $max_value) {
            $robust_average = $min_value;
            $robust_std_deviation =0.0;
            $robust_cv = 0.0; 
            $statisticalPopulationSize = sizeof($onlyResultsArr) > 0  ? sizeof($onlyResultsArr) : 0;
        } else {
            $compute_res = $this->computeQcReport($validResults,$onlyResultsArr);
            $robust_average = $compute_res['robust_average'];
            $robust_cv = $compute_res['robust_cv'];
            $robust_std_deviation = $compute_res['robust_std_deviation'];
            $statisticalPopulationSize = $compute_res['statisticalPopulationSize'];
            $cvstar = $compute_res['statisticalPopulationSize'] > 0 ?  $compute_res['robust_cv'] /  $compute_res['statisticalPopulationSize'] : 0;
            $sd_star = $cvstar * ($robust_average/100);
            foreach($validResults as $vRes){
                $z_score = $sd_star > 0 ? ($vRes->result - $robust_average) / $sd_star : 0;
                $z_score = round($z_score, 2);
                $vres['zscore'] = $z_score;
            }
        }

        $data = [
            "robust_average" =>$robust_average,
            "robust_cv"=>$robust_cv,
            "robust_std_deviation" => $robust_std_deviation,
            "statisticalPopulationSize"=>$statisticalPopulationSize,
            "cvstar"=>$cv_star ?? 0,
            "sd_star" => $sd_star ?? 0,
            "valid_res"=> $validResults
        ];

        return $data;

    
   }
}
