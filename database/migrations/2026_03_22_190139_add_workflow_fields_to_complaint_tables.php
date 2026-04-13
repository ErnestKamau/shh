<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            if (!Schema::hasColumn('complaints', 'intake_approved_at')) {
                if (Schema::hasColumn('complaints', 'intake_approved_by')) {
                    $table->timestamp('intake_approved_at')->nullable()->after('intake_approved_by');
                } else {
                    $table->timestamp('intake_approved_at')->nullable();
                }
            }
        });

        Schema::table('complaintsresolutions', function (Blueprint $table) {
            if (!Schema::hasColumn('complaintsresolutions', 'ncr_required')) {
                if (Schema::hasColumn('complaintsresolutions', 'car_required')) {
                    $table->boolean('ncr_required')->default(false)->after('car_required');
                } else {
                    $table->boolean('ncr_required')->default(false);
                }
            }

            if (!Schema::hasColumn('complaintsresolutions', 'capa_approved_by')) {
                if (Schema::hasColumn('complaintsresolutions', 'car_type')) {
                    $table->unsignedBigInteger('capa_approved_by')->nullable()->after('car_type');
                } elseif (Schema::hasColumn('complaintsresolutions', 'car_required')) {
                    $table->unsignedBigInteger('capa_approved_by')->nullable()->after('car_required');
                } else {
                    $table->unsignedBigInteger('capa_approved_by')->nullable();
                }
            }

            if (!Schema::hasColumn('complaintsresolutions', 'capa_approved_at')) {
                if (Schema::hasColumn('complaintsresolutions', 'capa_approved_by')) {
                    $table->timestamp('capa_approved_at')->nullable()->after('capa_approved_by');
                } else {
                    $table->timestamp('capa_approved_at')->nullable();
                }
            }

            if (!Schema::hasColumn('complaintsresolutions', 'send_to_customer')) {
                if (Schema::hasColumn('complaintsresolutions', 'client_remarks')) {
                    $table->boolean('send_to_customer')->default(false)->after('client_remarks');
                } else {
                    $table->boolean('send_to_customer')->default(false);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            if (Schema::hasColumn('complaints', 'intake_approved_at')) {
                $table->dropColumn('intake_approved_at');
            }
        });

        Schema::table('complaintsresolutions', function (Blueprint $table) {
            $columns = ['ncr_required', 'capa_approved_by', 'capa_approved_at', 'send_to_customer'];
            $existingColumns = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('complaintsresolutions', $column)));

            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
