<?php

namespace App\Http\Controllers\CRM;

use App\Models\CRM\CompanyProduct;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
class CompanyProductController extends Controller
{
	public function __construct()
	{
	  $this->middleware('auth');
	}

	public function add(Request $request){
		$product = new CompanyProduct;
		$product->name = $request->name;
    $product->crm_company_unit_id = $request->unit;
		$product->active = $request->active ?? 0;

		$product->save();

    return redirect()->back()->with('success', 'Added successfully.');
	}

	public function edit(Request $request, $id){
		$product = CompanyProduct::find($id);
		$product->name = $request->name;
    $product->crm_company_unit_id = $request->unit;
		$product->active = $request->active ?? 0;

		$product->save();

    return redirect()->back()->with('success', 'Edit was successful.');
	}
}
