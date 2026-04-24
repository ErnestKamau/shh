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


# ALTER USER api_user WITH PASSWORD 'ZOtAg/2HhI4xMU0BsHY9S';

# pg_dump -U api_user -h host -d gcla_api > gcla_api_dump.sql




# CREATE DATABASE gcla_lims;

# CREATE USER 'gcla_imara'@'localhost' IDENTIFIED BY 'ZOtAgHhI4xMU0BsHY9S!';

# GRANT ALL PRIVILEGES ON gcla_lims.* TO 'gcla_imara'@'localhost';

# FLUSH PRIVILEGES;

# Username : gcla_imara
# Password : ZOtAgHhI4xMU0BsHY9S!
# Database : gcla_lims

# psql -U postgres -h 127.0.0.1 -d gcla_lims < gcla_api.sql

# CREATE USER 'gcla_imara'@'%' IDENTIFIED BY 'ZOtAgHhI4xMU0BsHY9S!';

git config --global user.email "nuvemiteprojects@gmail.com"
  git config --global user.name "Nyagah"
