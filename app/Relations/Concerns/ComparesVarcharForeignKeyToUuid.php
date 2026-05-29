<?php

namespace App\Relations\Concerns;

use Illuminate\Support\Facades\DB;

trait ComparesVarcharForeignKeyToUuid
{
    protected function varcharForeignKeyMatchesUuidColumnSql(string $varcharQualifiedColumn, string $uuidQualifiedColumn): string
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            return sprintf(
                "NULLIF(TRIM(%s), '')::uuid = %s",
                $varcharQualifiedColumn,
                $uuidQualifiedColumn,
            );
        }

        return sprintf('%s = %s', $varcharQualifiedColumn, $uuidQualifiedColumn);
    }
}
