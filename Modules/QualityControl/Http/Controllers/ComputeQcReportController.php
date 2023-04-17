<?php

namespace Modules\QualityControl\Http\Controllers;

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


    }
   
}
