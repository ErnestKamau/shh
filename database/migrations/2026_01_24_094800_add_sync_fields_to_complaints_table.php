<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            if (!Schema::hasColumn('complaints', 'developer_ticket_id')) {
                if (Schema::hasColumn('complaints', 'ticket_no')) {
                    $table->unsignedBigInteger('developer_ticket_id')->nullable()->after('ticket_no');
                } else {
                    $table->unsignedBigInteger('developer_ticket_id')->nullable();
                }
            }

            if (!Schema::hasColumn('complaints', 'developer_ticket_no')) {
                if (Schema::hasColumn('complaints', 'developer_ticket_id')) {
                    $table->string('developer_ticket_no')->nullable()->after('developer_ticket_id');
                } elseif (Schema::hasColumn('complaints', 'ticket_no')) {
                    $table->string('developer_ticket_no')->nullable()->after('ticket_no');
                } else {
                    $table->string('developer_ticket_no')->nullable();
                }
            }

            if (!Schema::hasColumn('complaints', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable()->after('updated_at');
            }

            if (!Schema::hasColumn('complaints', 'sync_failed')) {
                $table->boolean('sync_failed')->default(false)->after('last_synced_at');
            }

            if (!Schema::hasColumn('complaints', 'sync_error')) {
                $table->text('sync_error')->nullable()->after('sync_failed');
            }
        });

        foreach (['developer_ticket_id', 'last_synced_at', 'sync_failed'] as $column) {
            try {
                Schema::table('complaints', function (Blueprint $table) use ($column) {
                    $table->index($column);
                });
            } catch (QueryException $e) {
                $message = $e->getMessage();
                if (!str_contains($message, 'Duplicate key name') && !str_contains($message, 'already exists')) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['developer_ticket_id', 'last_synced_at', 'sync_failed'] as $column) {
            try {
                Schema::table('complaints', function (Blueprint $table) use ($column) {
                    $table->dropIndex([$column]);
                });
            } catch (QueryException $e) {
                if (!str_contains($e->getMessage(), "doesn't exist") && !str_contains($e->getMessage(), 'check that column/key exists')) {
                    throw $e;
                }
            }
        }

        $columns = array_filter([
            'developer_ticket_id',
            'developer_ticket_no',
            'last_synced_at',
            'sync_failed',
            'sync_error',
        ], fn (string $col) => Schema::hasColumn('complaints', $col));

        if ($columns !== []) {
            Schema::table('complaints', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
