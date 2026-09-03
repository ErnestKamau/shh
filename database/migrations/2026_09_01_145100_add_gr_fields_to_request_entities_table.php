<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('request_entities')) {
            return;
        }

        Schema::table('request_entities', function (Blueprint $table) {
            if (! Schema::hasColumn('request_entities', 'remarks')) {
                $table->text('remarks')->nullable();
            }
            if (! Schema::hasColumn('request_entities', 'moisture_contents')) {
                $table->string('moisture_contents', 100)->nullable();
            }
            if (! Schema::hasColumn('request_entities', 'delivery_note_number')) {
                $table->string('delivery_note_number', 100)->nullable();
            }
            if (! Schema::hasColumn('request_entities', 'supplier_invoice_number')) {
                $table->string('supplier_invoice_number', 100)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('request_entities')) {
            return;
        }

        Schema::table('request_entities', function (Blueprint $table) {
            $columns = ['remarks', 'moisture_contents', 'delivery_note_number', 'supplier_invoice_number'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('request_entities', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
