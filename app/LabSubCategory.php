<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class LabSubCategory extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'lab_sub_category';

    protected $fillable = [
        'name',
        'image',
        'category_id',
        'reporting_unit',
        'rate',
        'description',
        'active',
        'stock',
        'current_batch_number',
        'batch_prepared_date',
        'batch_expiry_date',
        'stability_notes',
        'batch_status'
    ];

    /**
     * Get the category that owns this subcategory.
     */
    public function category()
    {
        return $this->belongsTo(LabInventoryCategory::class, 'category_id');
    }

    /**
     * Get the reporting unit for this subcategory.
     */
    public function reportingUnit()
    {
        return $this->belongsTo(ReportingUnit::class, 'reporting_unit');
    }

    /**
     * Get the category items associated with this subcategory.
     */
    public function categoryItems()
    {
        return $this->hasMany(LabCategoryItems::class, 'sub_category_id');
    }

    /**
     * Get the stock movements for this subcategory.
     */
    public function stockMovements()
    {
        return $this->hasMany(LabStockMovement::class, 'lab_sub_category_id')->orderBy('created_at', 'desc');
    }
}
