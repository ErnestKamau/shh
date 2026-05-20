<?php

namespace App\Relations;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UuidBelongsTo extends BelongsTo
{
    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            $table = $this->related->getTable();
            $foreignKeyValue = $this->child->{$this->foreignKey};

            if (is_string($foreignKeyValue) && Str::isUuid($foreignKeyValue)) {
                $this->query->where($table.'.'.$this->ownerKey, '=', $foreignKeyValue);
            } else {
                $this->query->whereRaw('1=0');
            }
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $key = $this->getRelated()->getTable().'.'.$this->getOwnerKeyName();
        $keys = $this->getEagerModelKeys($models);

        $validKeys = array_filter($keys, function ($k) {
            return is_string($k) && Str::isUuid($k);
        });

        if (empty($validKeys)) {
            $this->query->whereRaw('1=0');
        } else {
            $this->query->whereIn($key, $validKeys);
        }
    }
}
