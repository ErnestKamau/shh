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
    	$product->crm_company_unit_id = isset($request->unit)  ? $request->unit : 0;
		$product->active = $request->active ?? 0;

		$product->save();

    return redirect()->back()->with('success', 'Added successfully.');
	}

	public function edit(Request $request){
		$product = CompanyProduct::find($request->product_id);
		$product->name = $request->name;
		$product->crm_company_unit_id = isset($request->unit)  ? $request->unit : 0;
		$product->active = $request->active ?? 0;

		$product->save();

    return redirect()->back()->with('success', 'Edit was successful.');
	}
	public function index(){
		$products = CompanyProduct::orderBy('active','DESC')->orderBy('name','ASC')->get();
		return view('layouts.lab.sample-products.index',compact('products'));
	}
}
