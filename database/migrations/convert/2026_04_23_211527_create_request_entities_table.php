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
        Schema::create('request_entities', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('priority')->default('Normal');
            $table->string('currency')->nullable();
            $table->string('request_code');
            $table->string('status');
            $table->string('parent_request')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('parent_request_id')->nullable();
            $table->string('request_type');
            $table->integer('approval_count')->default(0);
            $table->integer('required_approvals')->default(0);
            $table->integer('created_by')->default(0);
            $table->string('description', 1024)->nullable();
            $table->string('nature_of_purchase', 100)->nullable();
            $table->timestamps();
            $table->decimal('net_value', 18, 0)->nullable();
            $table->decimal('is_lab_kit', 10)->nullable()->default(0);
            $table->decimal('quote_net_value', 10)->nullable()->default(0);
            $table->dateTime('submission_deadline')->nullable();
            $table->uuid('supplier_id')->nullable()->index('idx_request_entities_supplier_id_f9551c54');
            $table->tinyInteger('delete')->nullable();
            $table->integer('client_unit_id')->nullable()->index('client_unit_id');
            $table->integer('request_initiator')->nullable();
            $table->uuid('inventory_location_id')->nullable()->index('idx_request_entities_inventory_location_id_faaafb8b');
            $table->integer('parent_material_requisition')->nullable();
            $table->string('approval_status')->nullable();
            $table->integer('ammendment')->default(1);
            $table->boolean('in_ammendment')->default(false);
            $table->boolean('quotes_reminder_sent')->default(false);
            $table->string('gate_pass', 100)->nullable();
            $table->string('time_out', 100)->nullable();
            $table->string('vehicle_no', 100)->nullable();
            $table->integer('issue_to')->nullable();
            $table->string('usage', 100)->nullable();
            $table->string('note_bearer', 100)->nullable();
            $table->string('destination')->nullable();
            $table->boolean('bank_notified')->default(false);
            $table->boolean('supplier_bank_notification')->default(false);
            $table->string('cost_center', 200)->nullable();
            $table->string('downloadable_link')->nullable();
            $table->string('catalog_number')->nullable();
            $table->decimal('kit_total_price', 10)->nullable()->default(0);
            $table->foreign(['inventory_location_id'], 'fk_request_entities_inventory_location_id_baac4ff6')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['supplier_id'], 'fk_request_entities_supplier_id_326a86c6')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('set null');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_entities');
    }
};
