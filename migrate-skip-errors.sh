#!/bin/bash
set -euo pipefail


MIGRATIONS_DIR="database/migrations"
LOG_FILE="/tmp/migrate.log"

# Get pending migrations (column 1 is the migration name in artisan output)
mapfile -t PENDING < <(php artisan migrate:status | awk '/Pending/{print $1}')

if [ ${#PENDING[@]} -eq 0 ]; then
    echo "No pending migrations."
    exit 0
fi

echo "Found ${#PENDING[@]} pending migration(s)."
echo "---"

# Get the next batch number directly from the DB via tinker (once, increment for each migration)
BATCH=$(php artisan tinker --execute="echo DB::table('migrations')->max('batch') + 1;" 2>/dev/null | tail -1)
BATCH=${BATCH:-1}

for migration in "${PENDING[@]}"; do
    [ -z "$migration" ] && continue
    echo "Running: $migration"

    if php artisan migrate --path="${MIGRATIONS_DIR}/${migration}.php" 2>&1 | tee "$LOG_FILE"; then
        echo "✓ Success: $migration"
    else
        echo "✗ Migration failed: $migration — marking as done."
        php artisan tinker --execute="\
            if (!DB::table('migrations')->where('migration', '${migration}')->exists()) {\
                DB::table('migrations')->insert([\
                    'migration' => '${migration}',\
                    'batch'     => ${BATCH},\
                ]);\
                echo 'Inserted migration record for ${migration} in batch ${BATCH}';\
            } else {\
                echo 'Migration ${migration} already marked as run.';\
            }\
        " 2>&1
        echo "✓ Marked as run: $migration (batch $BATCH)"
    fi
    BATCH=$((BATCH + 1))
    echo "---"
done

echo "Done."
