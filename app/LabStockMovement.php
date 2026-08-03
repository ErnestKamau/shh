<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;


class LabStockMovement extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;
    protected $table = 'lab_stock_movement';

    protected $fillable = [
        'description',
        'lab_sub_category_id',
        'stock_type',
        'stock_in',
        'stock_out',
        'uom_id',
        'created_by',
        'preparation_id',
        'batch_number',
        'source_type',
        'source_id',
    ];

    /**
     * Get the subcategory for this stock movement.
     */
    public function subCategory()
    {
        return $this->belongsTo(LabSubCategory::class, 'lab_sub_category_id');
    }

    /**
     * Get the unit of measure for this stock movement.
     */
    public function uom()
    {
        return $this->belongsTo(ReportingUnit::class, 'uom_id');
    }

    /**
     * Get the user who created this stock movement.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function preparation()
    {
        return $this->belongsTo(\App\Models\SolutionPreparation::class, 'preparation_id');
    }
}
