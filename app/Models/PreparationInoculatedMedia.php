<?php

namespace App\Models;

use App\LabCategoryItems;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class PreparationInoculatedMedia extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'preparation_inoculated_media';

    protected $fillable = [
        'preparation_id',
        'media_id',
        'lab_category_item_id',
        'result',
        'notes',
    ];

    public function preparation(): BelongsTo
    {
        return $this->belongsTo(SolutionPreparation::class, 'preparation_id');
    }

    public function labCategoryItem(): BelongsTo
    {
        return $this->belongsTo(LabCategoryItems::class, 'lab_category_item_id');
    }
}
