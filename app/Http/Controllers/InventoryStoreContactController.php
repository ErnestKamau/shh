<?php

namespace App\Http\Controllers;

use App\InventoryStoreContact;
use Illuminate\Http\Request;

class InventoryStoreContactController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
	}

	public function add(Request $request, $id)
	{
		$contact = new InventoryStoreContact;
		$contact->user_id = $request->user_id;
		$contact->store = $id;
		$contact->save();

		return redirect()->back()->with('success', 'Store Contact Updated.');
	}

	public function delete(Request $request){
		InventoryStoreContact::find($request->store_contact_id)->delete();
		return redirect()->back()->with('success', 'Store Contact Removed.');
	}
}
