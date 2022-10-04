<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InventoryCategories extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  public function subcategories(){
    return $this->hasMany('App\InventorySubCategories', 'inventory_category_id');
  }

  public function activity(){
    $id = $this->id;
    return \App\InventoryItem::join('inventory_categories as ic', 'ic.id', '=', 'inventory_items.inventory_category_id')
      ->join('inventory_sub_categories as isc', 'isc.id', '=', 'inventory_items.inventory_sub_category_id')
      ->leftJoin('inventory_departments as d', 'd.id', '=', 'inventory_items.inventory_department_id')
      ->join('users as u', 'u.id', '=', 'inventory_items.created_by')
      ->where('ic.id', '=', $id)
			->selectRaw('inventory_items.created_at,inventory_items.stock_in, inventory_items.stock_out, d.name as department, ic.name as category, isc.unit_type, isc.image as sub_category_image, isc.name as sub_category, isc.manufacturer, u.name as creator, inventory_items.status as curr_status, u.email as creator_email')->get();
  }

  public function available(){
    $id = $this->id;
    $stockDetails = $this->stock();

    return array(
      "available" => floatval($stockDetails['stock_in']['total']) - floatval($stockDetails['stock_out']['total']),
      "pending" => floatval($stockDetails['pending']['total'])
    );
  }

  public function stock(){
    $id = $this->id;

    $inventoryItems = \App\InventoryItem::join('inventory_sub_categories as isc', 'isc.id', '=', 'inventory_items.inventory_sub_category_id')
    ->selectRaw('status, isc.name as sub_category, SUM(stock_in) as stock_in, SUM(stock_out) as stock_out')
    ->where('inventory_items.inventory_category_id', $id)->groupBy('stock_in', 'stock_out', 'isc.name', 'status')->get();

    $inventoryItemsArr = array(
      "stock_in"=>array(
        "subcategories"=>array(), "total"=>0
      ),
      "stock_out"=>array(
        "subcategories"=>array(), "total"=>0
      ),
      "pending"=>array(
        "subcategories"=>array(), "total"=>0
      ),
    );

    foreach($inventoryItems as $iItem){
      if(!isset($inventoryItemsArr['stock_in']['subcategories'][$iItem->sub_category])){
        $inventoryItemsArr['stock_in']['subcategories'][$iItem->sub_category] = 0;
        $inventoryItemsArr['stock_out']['subcategories'][$iItem->sub_category] = 0;
        $inventoryItemsArr['pending']['subcategories'][$iItem->sub_category] = 0;
      }

      if($iItem->status == "pending"){
        $inventoryItemsArr['pending']['subcategories'][$iItem->sub_category] += floatval($iItem->stock_in);
        $inventoryItemsArr['pending']['total'] += floatval($iItem->stock_in);
      }else{
        $inventoryItemsArr['stock_in']['subcategories'][$iItem->sub_category] += floatval($iItem->stock_in);
        $inventoryItemsArr['stock_in']['total'] += floatval($iItem->stock_in);
        $inventoryItemsArr['stock_out']['subcategories'][$iItem->sub_category] += floatval($iItem->stock_out);
        $inventoryItemsArr['stock_out']['total'] += floatval($iItem->stock_out);
      }
    }

    return $inventoryItemsArr;
  }
}
