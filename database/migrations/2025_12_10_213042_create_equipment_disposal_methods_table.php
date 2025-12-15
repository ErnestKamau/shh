<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentDisposalMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('equipment_disposal_methods', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('method')->unique(); // scrap, donation, auction, recycling, destruction, hazardous_disposal
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->json('applicable_categories'); // e-waste, hazardous, mechanical, chemical, radioactive
            $table->json('regulatory_requirements')->nullable(); // permits, certifications, approvals
            $table->json('required_documentation')->nullable();
            $table->json('approved_vendors')->nullable();
            $table->text('safety_requirements')->nullable();
            $table->text('environmental_compliance')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('method');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_disposal_methods');
    }
}
