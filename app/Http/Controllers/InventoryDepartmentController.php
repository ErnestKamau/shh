<?php

namespace App\Http\Controllers;

use Auth;
use App\InventoryDepartment;
use Illuminate\Http\Request;

class InventoryDepartmentController extends Controller
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
		$user = Auth::user();
		$module = "inventory";
		$departments = InventoryDepartment::where('company_id', getUserCompany())
			->where('module', $module)
			->where('location_id', getCurrentUserLocation()->id)
			->orderBy('name', 'asc')->get();

		return view('layouts.inventory.departments.index', compact('departments'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function add(Request $request, $module="inventory")
	{
		$department = new InventoryDepartment();
		$department->name = $request->name;
		$department->module = $module;
		$department->company_id = getUserCompany();
		$department->location_id = getCurrentUserLocation()->id;
		$department->department_head_id =  $request->proccess_owner;
		$department->save();
		
		

		return redirect()->back()->with('success', 'Department Added');
	}

	public function show($id){
		$department = InventoryDepartment::find($id);

		// $items = $department->inventory_items;
		// $item = $items[0];
		// return json_encode($item->category);

		return view('layouts.inventory.departments.show', compact('department'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Request $request, $id)
	{
		
		$department = InventoryDepartment::find($id);
		$department->name = $request->name;
		$department->active = $request->active ?? 0;
		
		$department->department_head_id =  $request->proccess_owner;
		$department->save();
		// return response()->json($department);

		return redirect()->back()->with('success', 'Department Edited.');
	}
}
