<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\InventoryCategories;
use App\InventoryItem;
use App\InventorySubCategories;
use App\LabCategoryItems;
use App\LabInventoryCategory;
use App\LabSubCategory;
use App\ReportingUnit;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BufferManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function add_lab_inventory_categories(Request $request)
    {
        $category = new InventoryCategories();
        $category->name = $request->name;
        $category->description = $request->description;
        $category->is_lab = 1;
        if ($request->hasFile('image')) {
            $path = $request->image->path();
            $file = Storage::putFile('categories', new File($path));
            $file = explode('/', $file);

            $fName = '/storage/categories/'.urlencode(end($file));

            $category->image = (string) $fName;
        }

        $category->company_id = getUserCompany();
        $category->inventory_location_id = getCurrentUserLocation()->id;
        $category->save();

        return redirect()->back()->with('success', 'Inventory Category Added.');
    }

    public function add_lab_sub_category(Request $request, $id, $internal = false)
    {
        $category = InventoryCategories::find($request->category_id);
        $subcategory = new LabSubCategory();
        $subcategory->name = $request->name;
        $subcategory->description = $request->description;
        if ($request->hasFile('image')) {
            $path = $request->image->path();
            $file = Storage::putFile('subcategory', new File($path));
            $file = explode('/', $file);

            $fName = '/storage/subcategory/'.urlencode(end($file));

            $subcategory->image = (string) $fName;
        }

        $subcategory->reporting_unit = $request->reporting_unit;
        $subcategory->rate = $request->rate;
        $subcategory->category_id = $category->id;

        $subcategory->save();

        return redirect()->back()->with('success', 'Lab Sub-Category Added.');
    }

    public function delete_category($id)
    {
        $category = InventoryCategories::find($id);
        if (!isset($category->id) || $category->active == 0) {
            return redirect()->back()->with('error', 'Category does not exist or its deleted.');
        }
        $category->active = 0;
        $category->save();

        return redirect()->back()->with('success', 'Category deleted successfully!');
    }

    public function index()
    {
        $categories = LabInventoryCategory::where('active', 1)->get();

        return view('layouts.lab.buffer.index', compact('categories'));
    }

    public function stock_management_index(Request $request)
    {
        $suppliers = getSuppliers();
        $subcategory = LabSubCategory::join('lab_inventory_category as lic', 'lic.id', '=', 'lab_sub_category.category_id')->join('reporting_units as ru', 'ru.id', 'lab_sub_category.reporting_unit')
        ->selectRaw('lab_sub_category.*,lic.name as categroy_name,ru.name as reporting_name')->get();
        $categories = LabInventoryCategory::where('active', 1)->get();
        $selected = [];

        return view('layouts.lab.buffer.stock_management.index', compact('suppliers', 'subcategory', 'categories', 'selected'));
    }

    public function add_lab_category_item(Request $request)
    {
        $sub = LabSubCategory::find($request->subcategory_id);
        // $reagent = InventoryItem::find($id);
        if (!isset($sub->id)) {
            return redirect()->back()->with('error', 'Kindly provide all the data needed!');
        }
        $counter = 0;
        // return response()->json($request->reagent_id[$counter],200);
        foreach ($request->reagent_id as $reagent) {
            $item = new LabCategoryItems();
            $item->category_id = $sub->category_id;
            $item->unit_measure_id = $request->reporting_unit[$counter];
            $item->amount_used = $request->amount_used[$counter];
            $item->reagent_id = $request->reagent_id[$counter];
            $item->inventory_sub_category_id = $request->reagent_id[$counter];
            $item->sub_category_id = $sub->id;
            $item->save();
            ++$counter;
        }

        return redirect()->back()->with('success', 'Items added successfully!');
    }

    public function show_lab_sub_category($id)
    {
        $subcategory = LabSubCategory::find($id);
        $categories = InventoryCategories::where('active', 1)->get();
        $reagents = InventorySubCategories::where('active', 1)->get();
        $category_items = LabCategoryItems::where('lab_category_items.sub_category_id', $id)->join('inventory_sub_categories as isc', 'isc.id', '=', 'lab_category_items.inventory_sub_category_id')
        ->selectRaw('lab_category_items.*,isc.name as reagent_name,isc.code as reagent_code')->get();

        return view('layouts.lab.buffer.stock_management.show', compact('category_items', 'subcategory', 'reagents', 'categories'));
    }

    public function delete_show_lab_category_item(Request $request)
    {
        $item = LabCategoryItems::find($request->item_id);
        if (!isset($item->id)) {
            return redirect()->back()->with('error', 'There is no category item with the specified ID!');
        }
        $item->delete();

        return redirect()->back()->with('success', 'Category item deleted successfully!');
    }

    public function edit_lab_category_item(Request $request)
    {
        $item = LabCategoryItems::find($request->item_id);
        $item->reagent_id = $request->reagent_id;
        $item->unit_measure_id = $request->reporting_unit;
        $item->amount_used = $request->amount_used;
        $item->save();
        // return response()->json( $request->amount_used,200);
        return redirect()->back()->with('success', 'Category item edited successfully!');
    }

    public function edit_lab_sub_category(Request $request)
    {
        $sub = LabSubCategory::find($request->sub_category_id);
        $sub->name = $request->name;
        if ($request->hasFile('image')) {
            $path = $request->image->path();
            $file = Storage::putFile('subcategory', new File($path));
            $file = explode('/', $file);

            $fName = '/storage/subcategory/'.urlencode(end($file));

            $sub->image = (string) $fName;
        }
        $sub->category_id = $request->category_id;
        $sub->reporting_unit = $request->reporting_unit;
        $sub->rate = $request->rate;
        $sub->description = $request->description;
        $sub->save();

        return redirect()->back()->with('success', 'Lab sub Category edited successfully!');
    }

    public function delete_sub_category(Request $request)
    {
        $sub = LabSubCategory::find($request->sub_category_id);
        $sub->active = 0;
        $sub->save();

        return redirect()->back()->with('success', 'Sub category deleted successfully!');
    }

    public function filter_data(Request $request)
    {
        $suppliers = getSuppliers();

        // return response()->json($request->category,200);
        $subcategory = $request->category ? LabSubCategory::whereIn('category_id', $request->category)->get() : LabSubCategory::all();
        // return response()->json($subcategory,200);
        foreach ($subcategory as $sub) {
            $category = InventoryCategories::find($sub->category_id);
            $reporting = ReportingUnit::find($sub->reporting_unit);
            $sub['category_name'] = $category->name;
            $sub['reporting_name'] = $reporting->name ?? '';
        }
        $selected = $request->category ?? [];
        $categories = InventoryCategories::where('is_lab', 1)->get();

        return view('layouts.lab.buffer.stock_management.index', compact('suppliers', 'subcategory', 'categories', 'selected'));
    }

    public function clone_sub_category(Request $request)
    {
        $sub = LabSubCategory::find($request->sub_category_id);
        $items = LabCategoryItems::where('sub_category_id', $sub->id)->get();
        $new_sub = new LabSubCategory();
        $new_sub->name = $sub->name;
        $new_sub->description = $sub->description;
        $new_sub->image = $sub->image;
        $new_sub->reporting_unit = $sub->reporting_unit;
        $new_sub->rate = $sub->rate;
        $new_sub->category_id = $sub->category_id;
        $new_sub->save();
        foreach ($items as $item) {
            $new_item = new LabCategoryItems();
            $new_item->category_id = $item->category_id;
            $new_item->unit_measure_id = $item->unit_measure_id;
            $new_item->amount_used = $item->amount_used;
            $new_item->reagent_id = $item->reagent_id;
            $new_item->sub_category_id = $new_sub->id;
            $new_item->save();
        }

        return redirect()->back()->with('success', 'Cloned successfully!');
    }
}
