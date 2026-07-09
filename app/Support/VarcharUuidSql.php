<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class VarcharUuidSql
{
    public static function equals(string $leftQualifiedColumn, string $rightQualifiedColumn): string
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            return sprintf(
                "NULLIF(TRIM(%s::text), '') = NULLIF(TRIM(%s::text), '')",
                $leftQualifiedColumn,
                $rightQualifiedColumn,
            );
        }

        return sprintf('%s = %s', $leftQualifiedColumn, $rightQualifiedColumn);
    }
}
