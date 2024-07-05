<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\InventoryCategories;
use App\LabCategoryItems;
use App\LabInventoryCategory;
use App\LabStockMovement;
use App\LabSubCategory;
use App\ReportingUnit;
use App\UnitOfMeasureConversion;
use Illuminate\Http\Request;

class BufferStockMovementControllerbackup extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $sub_category = LabSubCategory::where('active', 1)->get();
        foreach ($sub_category as $sub) {
            $sub->category_name = LabInventoryCategory::find($sub->category_id)->name;
        }

        return view('layouts.lab.buffer.stock_movement.index', compact('sub_category'));
    }

    public function show($id)
    {
        $categories = InventoryCategories::where('is_lab', 1)->get();
        $category = LabSubCategory::where('lab_sub_category.id', $id)
        ->join('reporting_units as ru', 'ru.id', '=', 'lab_sub_category.reporting_unit')->selectRaw('lab_sub_category.*,ru.name as reporting_name')->first();
        if (!isset($category->id)) {
            return redirect()->back()->with('error', 'There is no record of lab sub category item with specified ID! ');
        }
        $stock_movement = LabStockMovement::where('lab_sub_category_id', $category->id)->orderBy('id', 'desc')->get();
        $uoms = ReportingUnit::all();
        $report = ReportingUnit::find($category->reporting_unit);
        // return response()->json($report,200);
        return view('layouts.lab.buffer.stock_movement.show', compact('category', 'categories', 'uoms', 'stock_movement', 'report'));
    }

    public function add(Request $request)
    {
        $category = LabSubCategory::find($request->category_id);
        if (!isset($category->id)) {
            return redirect()->back()->with('error', 'There is no record of lab sub category item with specified ID! ');
        }
        $stock = new LabStockMovement();
        $stock->description = $request->description;
        $stock->lab_sub_category_id = $request->category_id;
        $stock->stock_type = $request->stock_type;
        if ($request->stock_type == 'stock_in') {
            $items = LabCategoryItems::where('sub_category_id', $category->id)->get();
            $inventoryC = new \App\Http\Controllers\InventoryItemController();
            $items_store = new \App\Http\Controllers\InventoryStoreController();
            foreach ($items as $item) {
                $store = $items_store->store_slots_by_item($item->reagent_id);

                $theUser = \Auth::user();

                $req = new Request();
                $req->category_id = $item->inventory_category_id;
                $req->sub_category_id = $item->inventory_sub_category_id;
                $req->quantity = $request->amount * $item->amount_used;

                $req->slot = $store->slot_id ?? 0;
                $req->store = $store->id ?? 0;
                $req->transfer_to = $theUser->department_id;
                $req->issued_to = $theUser->id;
                $req->item_brand_id = $item->item_brand_id ?? 0;

                $issueOut = $inventoryC->transfer($req, true);
                // return response()->json($issueOut, 200);

                $item->inventory_item_id = $issueOut->id;
            }

            // return response()->json($items, 200);
            $stock->stock_in = $request->amount;
            if ($category->reporting_unit == $request->uom_id) {
                $total = (int) $category->stock + (int) $request->amount;
                $category->stock = $total;
            } else {
                $uom_conv = UnitOfMeasureConversion::where('uom1', $category->reporting_unit)->where('uom2', $request->uom_id)->first();
                $uom_conv_2 = UnitOfMeasureConversion::where('uom1', $request->uom_id)->where('uom2', $category->reporting_unit)->first();
                if (isset($uom_conv->id)) {
                    $total_uom = $request->amount / $uom_conv->conversion;
                    $category->stock = $category->stock + $total_uom;
                } elseif (isset($uom_conv_2->id) && !isset($uom_conv->id)) {
                    $t_uom = $request->amount * $uom_conv->conversion;
                    $category->stock = $category->stock + $t_uom;
                } else {
                    $r = getReportingUnitsByID($category->reporting_unit);
                    $o = getReportingUnitsByID($request->uom_id);

                    return redirect()->back()->with('error', 'Kindly set the UOM conversion units between '.$r->name.' and '.$o->name);
                }
            }
        } elseif ($request->stock_type == 'stock_out') {
            $stock->stock_out = $request->amount;
            if ($category->reporting_unit == $request->uom_id) {
                $total = (int) $category->stock - (int) $request->amount;
                $category->stock = $total;
            } else {
                $uom_conv = UnitOfMeasureConversion::where('uom1', $category->reporting_unit)->where('uom2', $request->uom_id)->first();
                $uom_conv_2 = UnitOfMeasureConversion::where('uom1', $request->uom_id)->where('uom2', $category->reporting_unit)->first();
                // return response()->json($request->uom_id,200);
                if (isset($uom_conv->id)) {
                    $total_uom = $request->amount / $uom_conv->conversion;
                    $category->stock = $category->stock - $total_uom;
                } elseif (isset($uom_conv_2->id) && !isset($uom_conv->id)) {
                    $t_uom = $request->amount * $uom_conv->conversion;
                    $category->stock = $category->stock - $t_uom;
                } else {
                    $r = getReportingUnitsByID($category->reporting_unit);
                    $o = getReportingUnitsByID($request->uom_id);

                    return redirect()->back()->with('error', 'Kindly set the UOM conversion units between '.$r->name.' and '.$o->name);
                }
            }
        }
        if ($category->stock < 0) {
            return redirect()->back()->with('error', 'Your stock out amount is higher than the available quantity');
        }
        $category->save();
        $stock->uom_id = $request->uom_id;
        $stock->created_by = auth()->user()->id;
        $stock->save();

        return redirect()->back()->with('success', 'Stock Movement Added Successfully!');
    }
}
