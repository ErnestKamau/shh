<?php

namespace App\Http\Controllers;

use App\RatingCriteria;
use Illuminate\Http\Request;

class RatingCriteriaController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
  }

	public function update(Request $request, $id=false){
		$criteria = $id ? RatingCriteria::find($id) : new RatingCriteria;

		$criteria->title = $request->title;
		$criteria->max_score = $request->max_score;

		if($id){
			$criteria->active = $request->has('active') ? 1 : 0;
		}

		$criteria->save();

		return redirect()->back()->with('success', 'Rating criteria updated.');
	}
}
