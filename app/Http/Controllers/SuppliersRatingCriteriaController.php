<?php

namespace App\Http\Controllers;

use App\SuppliersRatingCriteria;
use Illuminate\Http\Request;

class SuppliersRatingCriteriaController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

	public function update(Request $request, $id){
		$criteria = $request->criteria;

		// return json_encode($criteria);

		foreach($criteria as $cid=>$score){
			$cScore = SuppliersRatingCriteria::where('supplier_id', $id)
				->where('criteria_id', $cid)->where('is_current', 1)->first();

			if(!isset($cScore->score) || $cScore->score != $score){
				$newScore = new SuppliersRatingCriteria;
				$newScore->criteria_id = $cid;
				$newScore->supplier_id = $id;
				$newScore->score = $score;
				$newScore->rating_by = \Auth::user()->id;
				$newScore->is_current = 1;
				$newScore->save();

				if(isset($cScore->score)){
					$cScore->is_current = 0;
					$cScore->save();
				}
			}
		}

		return redirect()->back()->with('success', 'Supplier criteria score updated.');
	}
}
