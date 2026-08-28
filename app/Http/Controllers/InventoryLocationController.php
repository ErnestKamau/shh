<?php

namespace App\Http\Controllers;

use App\InventoryLocation;
use App\InventoryLocationUser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

	public function show($showId)
	{
		$location = InventoryLocation::find($showId);

		return view('layouts.inventory.locations.show', compact('location'));
	}

	public function add(Request $request)
	{
		[$parentId, $parentLevel] = $this->parseParentLocation($request->parent_location);

		$location = new InventoryLocation;
		$location->name = $request->name;
		$location->level = $parentLevel + 1;
		$location->inventory_location_id = $parentId;
		$location->save();

		return redirect()->back()->with('success', 'New Location Added.');
	}

	public function add_user(Request $request, $id)
	{
		foreach ($request->users as $userId) {
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
		[$parentId, $parentLevel] = $this->parseParentLocation($request->parent_location);

		$location = InventoryLocation::find($id);
		$location->name = $request->name;
		$location->level = $parentLevel + 1;
		$location->inventory_location_id = $parentId;
		$location->save();

		return redirect()->back()->with('success', 'Location Updated.');
	}

	public function destroy(Request $request, $id)
	{
		InventoryLocation::find($id)->delete();

		return redirect()->back()->with('success', 'Location Deleted.');
	}

	public function set_user_location($id)
	{
		$location = InventoryLocation::find($id);
		\Session::put('current_user_location', $location);

		return redirect()->route('inventory-home')->with('success', 'User Location Set.');
	}

	/**
	 * Parent select values are "{id} {level}" (e.g. "0 0" for root, or "{uuid} {level}").
	 *
	 * @return array{0: ?string, 1: int}
	 */
	private function parseParentLocation(?string $parentLocation): array
	{
		$parts = preg_split('/\s+/', trim((string) $parentLocation), 2) ?: [];
		$parentId = $parts[0] ?? null;
		$parentLevel = isset($parts[1]) && is_numeric($parts[1]) ? (int) $parts[1] : 0;

		if ($parentId === null || $parentId === '' || $parentId === '0' || ! Str::isUuid($parentId)) {
			return [null, $parentLevel];
		}

		return [$parentId, $parentLevel];
	}
}
