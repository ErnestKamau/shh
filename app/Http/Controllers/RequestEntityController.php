<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Datatables\DatatablesWhere as Datatables;

class RequestEntityController extends Controller
{
	public function get_entities_server_side(Request $request, $stage, $type)
	{
		$isSomeBody = isUserSomebody(\Auth::user());

		$columns = array(
			array( 'db' => 'id',  'dt' => 0),
			array( 'db' => 'request_code',  'dt' => 1 ),
			array( 'db' => 'description', 'dt' => 2 ),
			array( 'db' => 'item_names', 'dt' => 3 ),
			array( 'db' => 'priority',  'dt' => 4 ),
			array( 'db' => 'status',   'dt' => 5 ),
			array( 'db' => 'due_date',     'dt' => 6 ),
			array( 'db' => 'parent_request',     'dt' => 7 ),
			array( 'db' => 'parent_request_id',     'dt' => 8 ),
			array( 'db' => 'parent_request_code',     'dt' => 9 ),
			array( 'db' => 'creator_name',     'dt' => 10 ),
			array( 'db' => 'departmental_name',     'dt' => 11 ),
			array( 'db' => 'created_at',     'dt' => 12 ),
			array( 'db' => 'net_value',     'dt' => 13 ),
			array( 'db' => 'approval_count',     'dt' => 14 ),
			array( 'db' => 'required_approvals',     'dt' => 15 ),
			array( 'db' => 'supplier_name','dt' => 16 ),
			array( 'db' => 'created_by','dt' => 16 )
		);

		$orders = \App\ViewRequestEntity::query()
			->where('request_type', $stage)
			->where('is_lab_kit', 0)
			->where(function ($query) {
				$query->where('delete', 0)->orWhereNull('delete');
			})
			->where('inventory_location_id', getCurrentUserLocation()->id);

		if (Schema::hasColumn('request_entities', 'is_supplement')) {
			$orders->where(function ($query) {
				$query->where('is_supplement', 0)->orWhereNull('is_supplement');
			});
		}

		if ($isSomeBody === false) {
			$departmentID = \Auth::user()->department_id;
			$orders = $orders->where('department_id', $departmentID);
		}

		if ($type == "completed_list") {
			$orders = $orders->where('status', '=', 'Completed');
		}

		if ($type == "list") {
			$orders = $orders->where('status', '!=', 'Completed');
		}

		if ($type == "kit_list") {
			$lab_department_id = getConfigByName('lab_department_id');
			$lab_department_id = count($lab_department_id) > 0 ? $lab_department_id[0]->value : 0;

			if (\Auth::user()->department_id == $lab_department_id && $stage == "Purchase Request") {
				$orders = $orders->where('is_lab_kit', 1);
			}

			if ($stage == "Request to Store") {
				$orders = $orders->where('is_lab_kit', 1);
			}
		}

		$results = new Datatables($orders, $request, $columns);
		$results = $results->execute();

		return response()->json($results, 200);
	}
}
