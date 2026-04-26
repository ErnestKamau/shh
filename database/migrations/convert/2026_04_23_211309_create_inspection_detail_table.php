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
        Schema::create('inspection_detail', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('created_by');
            $table->string('approved_vgm_no');
            $table->string('authorized_vgm_contact');
            $table->string('inspector_phone_no')->nullable();
            $table->string('inspector_email')->nullable();
            $table->string('carrier_booking_number')->nullable();
            $table->string('shipper_invoice_no')->nullable();
            $table->string('shipper_po_number')->nullable();
            $table->string('name_booked_vessel')->nullable();
            $table->string('voyage_number')->nullable();
            $table->string('etd_eta')->nullable();
            $table->string('container_number')->nullable();
            $table->string('seal_number')->nullable();
            $table->string('size_of_container')->nullable();
            $table->text('description_goods')->nullable();
            $table->text('vgm_evaluation_method')->nullable();
            $table->dateTime('submission_date')->nullable();
            $table->string('shipper_company_name')->nullable();
            $table->string('shipper_adress')->nullable();
            $table->string('shipper_authorized_contact_name')->nullable();
            $table->dateTime('shipper_authorized_contact_date')->nullable();
            $table->text('shipper_authorized_contact_sign')->nullable();
            $table->string('place_of_receipt')->nullable();
            $table->string('port_of_depature')->nullable();
            $table->string('port_of_discharge')->nullable();
            $table->text('final_destination')->nullable();
            $table->string('container_mac_gross')->nullable();
            $table->string('cargo_weight')->nullable();
            $table->string('empty_container_weight')->nullable();
            $table->string('packaging_material_weight')->nullable();
            $table->string('dunnage_weight')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->text('pdf_url')->nullable();
            $table->text('pdf_url_external')->nullable();
            $table->string('cert_no');
            $table->string('serial_no');
            $table->string('total_verified_gross_mass')->nullable();
            $table->integer('first_surveyor_id')->nullable();
            $table->string('sec_surveyor_id', 100)->nullable();
            $table->date('first_surveyor_date')->nullable();
            $table->date('sec_surveyor_date')->nullable();
            $table->text('delete_reason')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_detail');
    }
};
