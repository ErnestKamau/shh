<?php

namespace App\Models\SkillsMatrix;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InductionChecklistItem extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'skills_induction_checklist_items';

    protected $fillable = ['name', 'sort_order', 'active', 'inventory_location_id'];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
