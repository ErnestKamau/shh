<?php

namespace App\Relations\Concerns;

use App\Support\VarcharUuidSql;

trait ComparesVarcharForeignKeyToUuid
{
    protected function varcharForeignKeyMatchesUuidColumnSql(string $varcharQualifiedColumn, string $uuidQualifiedColumn): string
    {
        return VarcharUuidSql::equals($varcharQualifiedColumn, $uuidQualifiedColumn);
    }
}
