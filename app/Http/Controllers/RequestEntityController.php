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

		$currentLocation = getCurrentUserLocation();
		if (! $currentLocation) {
			return response()->json([
				'draw' => intval($request->draw ?? 0),
				'recordsTotal' => 0,
				'recordsFiltered' => 0,
				'data' => [],
				'error' => 'No inventory location is set for your account.',
			], 422);
		}

		$columns = [
			['db' => 'id', 'dt' => 0],
			['db' => 'request_code', 'dt' => 1],
			['db' => 'description', 'dt' => 2],
			['db' => 'item_names', 'dt' => 3],
			['db' => 'priority', 'dt' => 4],
			['db' => 'status', 'dt' => 5],
			['db' => 'approval_status', 'dt' => 6],
			['db' => 'due_date', 'dt' => 7],
			['db' => 'parent_request', 'dt' => 8],
			['db' => 'parent_request_id', 'dt' => 9],
			['db' => 'parent_request_code', 'dt' => 10],
			['db' => 'creator_name', 'dt' => 11],
			['db' => 'departmental_name', 'dt' => 12],
			['db' => 'created_at', 'dt' => 13],
			['db' => 'net_value', 'dt' => 14],
			['db' => 'approval_count', 'dt' => 15],
			['db' => 'required_approvals', 'dt' => 16],
			['db' => 'supplier_name', 'dt' => 17],
			['db' => 'created_by', 'dt' => 18],
			['db' => 'currency_name', 'dt' => 19],
		];

		$orders = \App\ViewRequestEntity::query()
			->selectRaw("view_request_entities.*, (
					SELECT currency_cfg.name
					FROM module_pre_configs AS currency_cfg
					WHERE currency_cfg.id::text = view_request_entities.currency::text
						AND currency_cfg.type = 'Currency'
					LIMIT 1
				) AS currency_name")
			->where('request_type', $stage)
			->where(function ($query) {
				$query->where('delete', 0)
					->orWhereNull('delete');
			})
			->where('inventory_location_id', $currentLocation->id);

		if (Schema::hasColumn('request_entities', 'is_supplement')) {
			$orders->where(function ($query) {
				$query->where('is_supplement', 0)
					->orWhereNull('is_supplement');
			});
		}

		if ($isSomeBody === false) {
			$departmentID = \Auth::user()->department_id;
			$orders = $orders->where('department_id', $departmentID);
		}

		if ($type === 'kit_list') {
			$orders->where('is_lab_kit', 1);

			if ($stage === 'Purchase Request') {
				$lab_department_id = getConfigByName('lab_department_id');
				$lab_department_id = count($lab_department_id) > 0 ? $lab_department_id[0]->value : 0;

				if ((string) \Auth::user()->department_id !== (string) $lab_department_id) {
					$orders->whereRaw('1 = 0');
				}
			}
		} else {
			$orders->where(function ($query) {
				$query->where('is_lab_kit', 0)
					->orWhereNull('is_lab_kit');
			});

			if ($type === 'completed_list') {
				$orders->where('status', '=', 'Completed');
			}

			if ($type === 'list') {
				$orders->where('status', '!=', 'Completed');
			}
		}

		$results = new Datatables($orders, $request, $columns);
		$results = $results->execute();

		return response()->json($results, 200);
	}
}
