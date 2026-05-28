<?php

namespace App\Auditing\Drivers;

use OwenIt\Auditing\Contracts\Audit;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Drivers\Database;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SafeDatabaseDriver extends Database
{
    /**
     * Perform the audit with transaction savepoint isolation.
     *
     * @param \OwenIt\Auditing\Contracts\Auditable $model
     * @return \OwenIt\Auditing\Contracts\Audit|null
     */
    public function audit(Auditable $model): ?Audit
    {
        $savepoint = 'audit_' . substr(md5(uniqid()), 0, 8);
        $savepointCreated = false;

        try {
            DB::statement("SAVEPOINT {$savepoint}");
            $savepointCreated = true;
            
            $audit = parent::audit($model);

            DB::statement("RELEASE SAVEPOINT {$savepoint}");

            return $audit;
        } catch (Throwable $e) {
            $modelClass = get_class($model);
            if ($savepointCreated) {
                try {
                    DB::statement("ROLLBACK TO SAVEPOINT {$savepoint}");
                } catch (Throwable $rollbackException) {
                    throw new \RuntimeException("SafeDatabaseDriver [{$modelClass}]: Savepoint rollback failed after error: " . $e->getMessage() . " | Rollback error: " . $rollbackException->getMessage(), 0, $e);
                }
            } else {
                throw new \RuntimeException("SafeDatabaseDriver [{$modelClass}]: Savepoint creation failed. Transaction already poisoned? Original error: " . $e->getMessage(), 0, $e);
            }

            Log::error('SafeDatabaseDriver: Failed to insert audit log.', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
