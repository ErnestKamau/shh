<?php

namespace App\Relations;

use App\Relations\Concerns\ComparesVarcharForeignKeyToUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UuidBelongsTo extends BelongsTo
{
    use ComparesVarcharForeignKeyToUuid;

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
     * @param  array<int, \Illuminate\Database\Eloquent\Model>  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $key = $this->getRelated()->getTable().'.'.$this->getOwnerKeyName();
        $keys = $this->getEagerModelKeys($models);

        $validKeys = array_filter($keys, function ($k) {
            return is_string($k) && Str::isUuid($k);
        });

        if ($validKeys === []) {
            $this->query->whereRaw('1=0');
        } else {
            $this->query->whereIn($key, $validKeys);
        }
    }

    /** @inheritDoc */
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        if ($parentQuery->getQuery()->from === $query->getQuery()->from) {
            return $this->getRelationExistenceQueryForSelfRelation($query, $parentQuery, $columns);
        }

        return $query->select($columns)->whereRaw(
            $this->varcharForeignKeyMatchesUuidColumnSql(
                $this->getQualifiedForeignKeyName(),
                $query->qualifyColumn($this->ownerKey),
            )
        );
    }

    /** @inheritDoc */
    public function getRelationExistenceQueryForSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query->select($columns)->from(
            $query->getModel()->getTable().' as '.$hash = $this->getRelationCountHash()
        );

        $query->getModel()->setTable($hash);

        return $query->whereRaw(
            $this->varcharForeignKeyMatchesUuidColumnSql(
                $hash.'.'.$this->ownerKey,
                $this->getQualifiedForeignKeyName(),
            )
        );
    }
}
