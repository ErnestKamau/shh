<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class SafeEloquentUserProvider extends EloquentUserProvider
{
    public function retrieveById($identifier)
    {
        $table = $this->createModel()->getTable();

        if (! Schema::hasTable($table)) {
            return null;
        }

        try {
            return parent::retrieveById($identifier);
        } catch (QueryException $exception) {
            // Guard against stale/non-UUID identifiers in session storage.
            if (in_array(($exception->errorInfo[0] ?? null), ['22P02', '42P01'], true)) {
                return null;
            }

            throw $exception;
        }
    }

    public function retrieveByToken($identifier, #[\SensitiveParameter] $token)
    {
        $table = $this->createModel()->getTable();

        if (! Schema::hasTable($table)) {
            return null;
        }

        try {
            return parent::retrieveByToken($identifier, $token);
        } catch (QueryException $exception) {
            // Guard against stale/non-UUID identifiers in "remember me" cookies.
            if (in_array(($exception->errorInfo[0] ?? null), ['22P02', '42P01'], true)) {
                return null;
            }

            throw $exception;
        }
    }
}
