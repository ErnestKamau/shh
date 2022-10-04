<?php

namespace App\Http\Controllers;

use App\InventoryLocation;
use App\InventoryLocationUser;
use Illuminate\Http\Request;

class InventoryLocationController extends Controller
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
	public function index()
	{
		$locations = InventoryLocation::orderBy('level', 'asc')->get();

		return view('layouts.inventory.locations.index', compact('locations'));
	}

	public function show($showId){
		$location = InventoryLocation::find($showId);
		return view('layouts.inventory.locations.show', compact('location'));
	}

	public function add(Request $request)
	{
		$parent = explode(" ", $request->parent_location);
		$location = new InventoryLocation;
		$location->name = $request->name;
		$location->level = $parent[1]+1;
		$location->inventory_location_id = $parent[0];
		$location->save();

		return redirect()->back()->with('success', 'New Location Added.');
	}

	public function add_user(Request $request, $id)
	{

		foreach($request->users as $userId){
			$user = new InventoryLocationUser;
			$user->user_id = $userId;
			$user->inventory_location_id = $id;
			$user->save();
		}

		return redirect()->back()->with('success', 'Location Users Added.');
	}

	public function remove_user_access(Request $request, $id, $user)
	{
		InventoryLocationUser::where('inventory_location_id', $id)->where('user_id', $user)->delete();

		return redirect()->back()->with('success', 'Location Users Added.');
	}

	public function edit(Request $request, $id)
	{
		$parent = explode(" ", $request->parent_location);
		$location = InventoryLocation::find($id);
		$location->name = $request->name;
		$location->level = $parent[1]+1;
		$location->inventory_location_id = $parent[0];
		$location->save();

		return redirect()->back()->with('success', 'New Location Added.');
	}

	public function destroy(Request $request, $id)
	{

		InventoryLocation::find($id)->delete();

		return redirect()->back()->with('success', 'Location Deleted.');
	}

	public function set_user_location($id){
		$location = InventoryLocation::find($id);
		\Session::put('current_user_location', $location);

		return redirect()->route('inventory-home')->with('success', 'User Location Set.');
	}
}
