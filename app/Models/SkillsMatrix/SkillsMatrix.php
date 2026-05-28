<?php

namespace App\Models\SkillsMatrix;

use App\InventoryDepartment;
use App\ModulePreConfigs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;

class SkillsMatrix extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
    protected $table = "skillsmatrices";

    protected $fillable = [
        'name',
        'department_id',
        'matrix_role_ids',
        'status',
    ];

    protected $appends = ['jobdescription'];

    public function roles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SkillMarixRole::class, 'skills_matrix_id');
    }

    public function inventoryDepartment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InventoryDepartment::class, 'department_id');
    }

    /**
     * Join department name when inventory_departments.id (uuid) and department_id types differ.
     */
    public function scopeWithDepartmentName(Builder $query): Builder
    {
        return $query
            ->leftJoin('inventory_departments as c', function ($join): void {
                $join->on(DB::raw('c.id::text'), '=', DB::raw('skillsmatrices.department_id::text'));
            })
            ->selectRaw('skillsmatrices.*, c.name as department');
    }

    public function getJobDescriptionAttribute(): array
    {
        $descriptionIDS = SkillMarixRole::where('skills_matrix_id',$this->id)->pluck('job_description_id')->toArray();
        $descriptionNames = ModulePreConfigs::whereIn('id',$descriptionIDS)->pluck('name')->toArray();
        return ['ids'=>$descriptionIDS,"names"=>$descriptionNames];
    }
    
}
