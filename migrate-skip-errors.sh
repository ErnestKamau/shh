#!/bin/bash
php artisan migrate:status | grep "Pending" | awk '{print $1}' | while read migration; do
    [ -z "$migration" ] && continue
    echo "Running: $migration"
    php artisan migrate --path="database/migrations/$migration.php" 2>&1 | tee /tmp/migrate.log
    if [ ${PIPESTATUS[0]} -ne 0 ]; then
        if grep -q "already exists" /tmp/migrate.log; then
            echo "Table exists - marking as run"
            BATCH=$(php artisan migrate:status | grep "\[.*\] Ran" | tail -1 | sed 's/.*\[\(.*\)\].*/\1/')
            [ -z "$BATCH" ] && BATCH=1 || BATCH=$((BATCH + 1))
            php artisan tinker --execute="DB::table('migrations')->insert(['migration' => '$migration', 'batch' => $BATCH]);" 2>/dev/null
        fi
    fi
    echo "---"
done