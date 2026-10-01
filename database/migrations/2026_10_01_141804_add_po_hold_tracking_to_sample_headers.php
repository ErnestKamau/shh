<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Track when a job was held as Awaiting PO, released by applying a PO, or cancelled.
     */
    public function up(): void
    {
        Schema::table('sample_headers', function (Blueprint $table) {
            $table->timestamp('po_held_at')->nullable();
            $table->timestamp('po_released_at')->nullable();
            $table->uuid('po_released_by')->nullable();
            $table->timestamp('po_cancelled_at')->nullable();
            $table->uuid('po_cancelled_by')->nullable();
            $table->text('po_cancel_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_headers', function (Blueprint $table) {
            $table->dropColumn([
                'po_held_at',
                'po_released_at',
                'po_released_by',
                'po_cancelled_at',
                'po_cancelled_by',
                'po_cancel_reason',
            ]);
        });
    }
};
