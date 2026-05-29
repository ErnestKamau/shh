<?php

namespace App\Relations;

use App\Relations\Concerns\ComparesVarcharForeignKeyToUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UuidHasMany extends HasMany
{
    use ComparesVarcharForeignKeyToUuid;

    /** @inheritDoc */
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        if ($query->getQuery()->from === $parentQuery->getQuery()->from) {
            return $this->getRelationExistenceQueryForSelfRelation($query, $parentQuery, $columns);
        }

        return $query->select($columns)->whereRaw(
            $this->varcharForeignKeyMatchesUuidColumnSql(
                $this->getExistenceCompareKey(),
                $this->getQualifiedParentKeyName(),
            )
        );
    }

    /** @inheritDoc */
    public function getRelationExistenceQueryForSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query->from($query->getModel()->getTable().' as '.$hash = $this->getRelationCountHash());

        $query->getModel()->setTable($hash);

        return $query->select($columns)->whereRaw(
            $this->varcharForeignKeyMatchesUuidColumnSql(
                $hash.'.'.$this->getForeignKeyName(),
                $this->getQualifiedParentKeyName(),
            )
        );
    }
}
