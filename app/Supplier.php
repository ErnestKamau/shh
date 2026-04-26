<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class Supplier extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
	public function categories(){
		return SupplierCategory::join('inventory_sub_categories as isc', 'isc.id', 'supplier_categories.inventory_sub_category_id')
		->leftJoin('item_brands as ib', 'ib.id','supplier_categories.inventory_item_brand_id')
		->where('supplier_categories.supplier_id', $this->id)->where('supplier_categories.status', 1)
		->selectRaw('COALESCE(ib.image, "/images/no-logo.png") as image, COALESCE(ib.name, "NB") as brand, supplier_categories.id, isc.name as item, isc.code')->get();
	}

	public function itemIDs(){
		return SupplierCategory::join('inventory_sub_categories as isc', 'isc.id', 'supplier_categories.inventory_sub_category_id')
		->leftJoin('item_brands as ib', 'ib.id','supplier_categories.inventory_item_brand_id')
		->where('supplier_categories.supplier_id', $this->id)->where('supplier_categories.status', 1)
		->selectRaw('isc.id')->groupBy('isc.id')->pluck('id')->toArray();
	}

	public function contracts(){
		$contracts = SupplierContract::leftJoin('supplier_contract_items as sci', 'sci.contract_id', 'supplier_contracts.id')
			->leftJoin('inventory_sub_categories as isc', 'isc.id', 'sci.item_id')->where('supplier_id', $this->id)
			->selectRaw('supplier_contracts.*, isc.name, sci.item_id')->orderBy('end', 'desc')->get()->toArray();

		$cArr = [];

		foreach($contracts as $c){
			if(!isset($cArr[$c['id']])){
				$cArr[$c['id']] = $c;
				$cArr[$c['id']]['items'] = [];
			}
			$cArr[$c['id']]['items'][$c['item_id']] = $c['name'];
		}

		return $cArr;
	}

	public function inventory_items(){
	  return $this->hasMany('App\InventoryItem');
	}

	public function contacts(){
	  return $this->hasMany('App\SupplierContact', 'supplier_id');
	}

	public function purchase_orders(){
		return $this->hasMany('App\RequestEntity')->where('request_type', 'Purchase Orders')->orderBy('created_at', 'desc');
	}

	public function orders(){
		return $this->hasMany('App\InventoryOrder')->orderBy('status', 'desc');
	}

	public function ratings(){
		return $this->hasMany('App\InventorySupplierRating')->orderBy('created_at', 'desc');
	}

	public function average_rating(){
		$rating = \App\RatingCriteria::leftJoin('suppliers_rating_criterias as src', function($join){
			$join->on('src.criteria_id', '=', 'rating_criterias.id');
			$join->where('src.supplier_id', '=', $this->id);
		})->selectRaw('SUM(src.score) as score, SUM(rating_criterias.max_score) as max_score')
		->where('src.is_current', 1)->where('rating_criterias.active', 1)->first();


		return $rating->max_score == 0 ? 0 : number_format(floatval($rating->score)/floatval($rating->max_score)*100, 0);
	}
}
