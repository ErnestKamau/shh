<?php

namespace App\Http\Controllers;

use App\SupplierContact;
use Illuminate\Http\Request;

class SupplierContactController extends Controller
{
	public function update(Request $request, $supplier_id, $contact_id=false){



		$contact = $contact_id ? SupplierContact::where('supplier_id', $supplier_id)->where('id', $contact_id)->first() : new SupplierContact;
		$contact->name = $request->name;
		$contact->email = $request->email;
		$contact->phone = $request->phone;
		$contact->pin = $request->pin;
		$contact->id_number = $request->id_number;
		$contact->type = $request->type;
		$contact->supplier_id = $supplier_id;

		$contact->save();

		return \redirect()->back()->with('success', 'Supplier Contact Updated');
	}

	public function destroy($contact_id){
		$contact = SupplierContact::find($contact_id);

		if(!isset($contact->email)){
			return \redirect()->back()->with('error', 'Contact not found');
		}

		$contact->delete();
		return \redirect()->back()->with('success', 'Supplier Contact Deleted');
	}
}
