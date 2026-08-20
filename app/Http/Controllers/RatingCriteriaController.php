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

	public function update(Request $request, $id = false)
	{
		$criteria = $id ? RatingCriteria::find($id) : new RatingCriteria;

		$criteria->title = $request->title;
		$criteria->max_score = $request->max_score;

		if ($id) {
			$criteria->active = $request->has('active') ? 1 : 0;
		}

		$criteria->save();

		$criteria->guides()->delete();

		$guides = array_values($request->guide ?? []);

		foreach ($guides as $guide) {
			$guide = (array) $guide;

			$guideO = ! empty($guide['id'])
				? SupplierRatingCriteriaGuide::find($guide['id'])
				: null;

			if (! $guideO) {
				$guideO = new SupplierRatingCriteriaGuide;
			}

			$guideO->criteria_id = $criteria->id;
			$guideO->title = $guide['title'] ?? '';
			$guideO->lower_value = $guide['lower_value'] ?? 0;
			$guideO->upper_value = $guide['upper_value'] ?? 0;
			$guideO->save();
		}

		return redirect()->back()->with('success', 'Rating criteria updated.');
	}
}
