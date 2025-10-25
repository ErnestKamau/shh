<?php

namespace App\Http\Controllers;

use App\RatingCriteria;
use App\SupplierRatingCriteriaGuide;
use Illuminate\Http\Request;

class RatingCriteriaController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
  }

	public function update(Request $request, $id=false){
		// return json_encode($request->all());

		$criteria = $id ? RatingCriteria::find($id) : new RatingCriteria;

		$criteria->title = $request->title;
		$criteria->max_score = $request->max_score;

		if($id){
			$criteria->active = $request->has('active') ? 1 : 0;
		}

		$criteria->save();

		$criteria->guides()->delete();

		$guides = array_values($request->guide);

		foreach($guides as $guide){
			$guide = (array) $guide;
			$guideO = isset($guide['id']) ? SupplierRatingCriteriaGuide::where($guide['id']) : new SupplierRatingCriteriaGuide();
			$guide['criteria_id'] = $criteria->id;

			$guideO->criteria_id = $guide['criteria_id'];
			$guideO->title = $guide['title'];
			$guideO->lower_value = $guide['lower_value'];
			$guideO->upper_value = $guide['upper_value'];
			$guideO->save();
		}

		return redirect()->back()->with('success', 'Rating criteria updated.');
	}
}
