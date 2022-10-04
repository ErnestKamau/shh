<?php

namespace App\Http\Controllers\Suppliers;

use App\Http\Controllers\Controller;
use App\InventoryCategories;
use App\RequestEntity;
use App\SupplierQuote;
use App\RequestEntityItem;
use App\Supplier;
use Illuminate\Http\Request;

class SupplierDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(){
      $supplier = Supplier::find(auth()->user()->supplier_id);

      $rfqs = RequestEntity::where('supplier_id',$supplier->id)->get();
      $rfqs_items = array();
      foreach($rfqs as $rfq){
        if(!isset($rfqs_items[$rfq->id])){
          $rfqs_items[$rfq->id] = array();
        }
        $items = RequestEntityItem::where('request_id',$rfq->id)->get();
        $rfqs_items[$rfq->id] = $items;
        
      }
      $lpos = SupplierQuote::where('supplier_id',$supplier->id)->get();
      $lpos_awarded = SupplierQuote::where('supplier_id',$supplier->id)->where('is_awarded',1)->get();
     
      $goods_receipt = RequestEntity::where('request_type','Goods Receipt')->where('supplier_id',$supplier->id)->get();
     
      // return response()->json($supplier->inventory_items, 200);
      
      
      
    $stores = \App\InventoryStore::where('inventory_location_id', getCurrentUserLocation()->id)
    ->orderBy('name', 'asc')->get();

		$exclude_lab_storage = "is_lab_samples";

		$cats = InventoryCategories::orderBy('name', 'asc')->where('inventory_location_id', getCurrentUserLocation()->id)
			->where('category_type', '!=', $exclude_lab_storage)->get();

		return view('layouts.inventory.suppliers.dashboard.index', compact('supplier', 'cats', 'stores','rfqs','rfqs_items','lpos','lpos_awarded','goods_receipt'));
    }
}
