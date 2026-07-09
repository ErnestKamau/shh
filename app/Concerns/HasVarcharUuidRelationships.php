<?php

namespace App\Concerns;

use App\Relations\UuidBelongsTo;
use App\Relations\UuidHasMany;
use App\Relations\UuidHasOne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasVarcharUuidRelationships
{
    /**
     * @param  class-string<Model>  $related
     */
    protected function uuidBelongsTo($related, ?string $foreignKey = null, ?string $ownerKey = null, ?string $relation = null): UuidBelongsTo
    {
        if ($relation === null) {
            $relation = $this->guessBelongsToRelation();
        }

        $instance = $this->newRelatedInstance($related);

        if ($foreignKey === null) {
            $foreignKey = Str::snake($relation).'_id';
        }

        $ownerKey = $ownerKey ?: $instance->getKeyName();

        return new UuidBelongsTo(
            $instance->newQuery(),
            $this,
            $foreignKey,
            $ownerKey,
            $relation,
        );
    }

    /**
     * @param  class-string<Model>  $related
     */
    protected function uuidHasMany($related, ?string $foreignKey = null, ?string $localKey = null): UuidHasMany
    {
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $instance->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        return new UuidHasMany(
            $instance->newQuery(),
            $this,
            $foreignKey,
            $localKey,
        );
    }

    /**
     * @param  class-string<Model>  $related
     */
    protected function uuidHasOne($related, ?string $foreignKey = null, ?string $localKey = null): UuidHasOne
    {
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $instance->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        return new UuidHasOne(
            $instance->newQuery(),
            $this,
            $foreignKey,
            $localKey,
        );
    }
}
