<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\InventoryDepartment;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use App\Datatables\Datatables;

class AuditController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
	public function __construct()
  {
    $this->middleware('auth');
  }

  public function index()
  {
		return view('livewire.layout.personnel-app', [
			'componentType' => 'audit-logs',
			'pageTitle' => 'Audit Logs',
		]);
	}

	public function server_side_details(Request $request, $id){
		$detail = Audit::find($id);

		// return gettype($detail->new_values);

		$newVals = $detail->new_values ?? [];
		$oldVals = $detail->old_values ?? [];

		$columns = array_merge(array_keys($newVals), array_keys($oldVals));

		$columns = array_unique($columns);

		return json_encode(
			[
				"columns"=>$columns,
				"new"=>$newVals,
				"old"=>$oldVals,
			]
		);
	}

	public function server_side(Request $request, $userID=false){
		$columns = array(
			array( 'db' => 'id',  'dt' => -1 ),
			array( 'db' => 'event',  'dt' => 0 ),
			array( 'db' => 'entity',  'dt' => 1 ),
			array( 'db' => 'entity_id', 'dt' => 2 ),
			array( 'db' => 'ip_address',     'dt' => 3 ),
			array( 'db' => 'created_at',     'dt' => 4 ),
			array( 'db' => 'url',     'dt' => 5 ),
			array( 'db' => 'name',     'dt' => 6 ),
			array( 'db' => 'email',     'dt' => 7 ),
		);

		$logs = Audit::join('users as u', 'u.id', '=', 'audits.user_id')
			->selectRaw('audits.id, ip_address, url, audits.created_at, audits.event, audits.auditable_type as entity, audits.auditable_id as entity_id, u.name as name, u.email as email');	

		if($userID){
			$logs = $logs->where('u.id', $userID);
		}
		else{
			$userIDs = User::all()->pluck('id')->toArray();
			$logs = $logs->whereIn('u.id', $userIDs);
		}

		$results = new Datatables($logs, $request, $columns);
		$results = $results->execute();

		return response()->json($results, 200);
	}
}
