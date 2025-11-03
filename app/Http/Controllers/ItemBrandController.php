<?php

namespace App\Http\Controllers;

use App\ItemBrand;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemBrandController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function add(Request $request, $subcategory){
		$brand = new ItemBrand;

		$brand->name = $request->name;
		$brand->inventory_sub_category_id = $subcategory;
		if ($request->hasFile('image')){
      $path = $request->image->path();
      $file = Storage::putFile('brands', new File($path));
      $file = explode('/', $file);

      $fName = '/storage/brands/'.urlencode(end($file));

      $brand->image = (String) $fName;
		}
		else{
			return redirect()->back()->with('error', 'Brand image is required!');
		}

		$brand->save();
		return redirect()->back()->with('success', 'Item brand saved!');
	}

	public function edit(Request $request, $id){
		$brand = ItemBrand::find($id);

		if(!isset($brand->name)){
			return redirect()->back()->with('error', 'Item brand was not found!');
		}

		$brand->name = $request->name;
		// $brand->inventory_sub_category_id = $subcategory;
		if ($request->hasFile('image')){
      $path = $request->image->path();
      $file = Storage::putFile('brands', new File($path));
      $file = explode('/', $file);

      $fName = '/storage/brands/'.urlencode(end($file));

      $brand->image = (String) $fName;
		}

		$brand->save();
		return redirect()->back()->with('success', 'Item brand details saved!');
	}

	public function delete(Request $request, $id){
		$brand = ItemBrand::findOrFail($id);

		$brand->status = 0;

		$brand->save();

		return redirect()->back()->with('success', 'Item brand deleted!');
	}
}
