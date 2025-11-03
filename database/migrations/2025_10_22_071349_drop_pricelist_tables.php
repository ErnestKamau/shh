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
        Schema::dropIfExists('pricelist_customers');
        Schema::dropIfExists('pricelist_items');
        Schema::dropIfExists('pricelists');
        Schema::dropIfExists('zoho_items_pricelist');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not reversible - tables will need to be recreated from backups if needed
        // This is acceptable since user confirmed they're truncating the tables
    }
};
