<?php

namespace App\Concerns;

trait DefaultsActiveOnCreate
{
    public function initializeDefaultsActiveOnCreate(): void
    {
        $column = $this->activeDefaultColumn();

        if (! array_key_exists($column, $this->attributes)) {
            $this->attributes[$column] = true;
        }
    }

    public static function bootDefaultsActiveOnCreate(): void
    {
        static::creating(function (self $model): void {
            $column = $model->activeDefaultColumn();

            if ($model->getAttribute($column) === null) {
                $model->setAttribute($column, true);
            }
        });
    }

    protected function activeDefaultColumn(): string
    {
        return 'active';
    }
}
