<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class LabCategoryItems extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'lab_category_items';

    protected $fillable = [
        'category_id',
        'sub_category_id',
        'reagent_id',
        'inventory_sub_category_id',
        'unit_measure_id',
        'amount_used'
    ];

    /**
     * Get the subcategory for this item.
     */
    public function subCategory()
    {
        return $this->belongsTo(LabSubCategory::class, 'sub_category_id');
    }

    /**
     * Get the reagent (inventory subcategory) for this item.
     */
    public function reagent()
    {
        return $this->belongsTo(InventorySubCategories::class, 'inventory_sub_category_id');
    }

    /**
     * Get the unit of measure for this item.
     */
    public function unitMeasure()
    {
        return $this->belongsTo(ReportingUnit::class, 'unit_measure_id');
    }
}
