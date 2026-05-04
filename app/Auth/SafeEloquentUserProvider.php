<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Database\QueryException;

class SafeEloquentUserProvider extends EloquentUserProvider
{
    public function retrieveById($identifier)
    {
        try {
            return parent::retrieveById($identifier);
        } catch (QueryException $exception) {
            // Guard against stale/non-UUID identifiers in session storage.
            if (($exception->errorInfo[0] ?? null) === '22P02') {
                return null;
            }

            throw $exception;
        }
    }
}
