<?php

namespace App\Http\Controllers;

use App\SuppliersRatingCriteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SuppliersRatingCriteriaController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function update(Request $request, $id)
	{
		$criteria = $request->input('criteria', []);
		$reasons = $request->input('reason', []);

		if (! is_array($criteria) || $criteria === []) {
			return redirect()->back()->with('error', 'No rating criteria scores were submitted.');
		}

		$hasReasonColumn = Schema::hasColumn('suppliers_rating_criterias', 'reason');
		$hasRequestIdColumn = Schema::hasColumn('suppliers_rating_criterias', 'request_id');

		foreach ($criteria as $cid => $score) {
			$cScore = SuppliersRatingCriteria::where('supplier_id', $id)
				->where('criteria_id', $cid)
				->where('is_current', 1)
				->first();

			$reason = is_array($reasons) ? ($reasons[$cid] ?? null) : null;
			$existingReason = $cScore->reason ?? null;

			if (! isset($cScore->score) || (string) $cScore->score !== (string) $score || (string) $reason !== (string) $existingReason) {
				$newScore = new SuppliersRatingCriteria;
				$newScore->criteria_id = $cid;
				$newScore->supplier_id = $id;
				$newScore->score = $score;
				$newScore->rating_by = Auth::id();
				$newScore->is_current = 1;

				if ($hasReasonColumn) {
					$newScore->reason = $reason;
				}

				if ($hasRequestIdColumn) {
					$requestId = $request->input('request_id');
					$newScore->request_id = filled($requestId) && $requestId !== '0' ? $requestId : null;
				}

				$newScore->save();

				if (isset($cScore->score)) {
					$cScore->is_current = 0;
					$cScore->save();
				}
			}
		}

		return redirect()->back()->with('success', 'Items issued out and supplier criteria score updated.');
	}
}
