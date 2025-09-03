<?php

namespace App\Http\Controllers\CRM;

use App\Models\CRM\CRMCompanyUnit;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
class CRMCompanyUnitController extends Controller
{
	
	public function __construct()
	{
	  $this->middleware('auth');
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function add(Request $request, $cust_id)
	{
		$unit = new CRMCompanyUnit;
		$unit->name = $request->name;
    $unit->company_id = getUserCompany();
    $unit->crm_customer_id = $cust_id;
		$unit->active = $request->active ?? 0;
		
		$unit->save();

    return redirect()->back()->with('success', 'Company unit added.');
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Request $request, $id, $cust_id)
	{
		$unit = CRMCompanyUnit::find($id);
		$unit->name = $request->name;
		$unit->company_id = getUserCompany();
		$unit->crm_customer_id = $cust_id;
			$unit->active = $request->active ?? 0;
			
			$unit->save();

		return redirect()->back()->with('success', 'Company unit added.');
	}
}
