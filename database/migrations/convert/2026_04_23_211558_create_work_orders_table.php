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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('client_id');
            $table->string('ticket_no')->nullable();
            $table->enum('routine', ['Yes', 'No'])->default('No');
            $table->string('site')->nullable();
            $table->enum('demand_type', ['Demand', 'PM'])->default('Demand');
            $table->enum('priority', ['Low', 'Medium', 'High'])->default('Low');
            $table->string('reminder', 32)->nullable();
            $table->integer('assigned_resource_id')->nullable();
            $table->string('reason_for_edit')->nullable();
            $table->enum('current_status', ['REQUEST', 'PENDING', 'REQUEST_REJECTION', 'ACCEPTED', 'ASSIGNED', 'COMPLETED', 'REJECTED']);
            $table->uuid('service_id')->nullable()->index('idx_work_orders_service_id_32f29f23');
            $table->date('due_date')->nullable();
            $table->date('start_date')->nullable();
            $table->longText('description')->nullable();
            $table->string('lso_lpo', 128)->nullable();
            $table->string('created_by');
            $table->string('created_by_id');
            $table->integer('created_from')->nullable();
            $table->timestamps();
            $table->integer('external_resource_id')->default(0);
            $table->boolean('approval_started')->default(false);
            $table->foreign(['service_id'], 'fk_work_orders_service_id_5fb9f0ea')->references(['id'])->on('services')->onUpdate('no action')->onDelete('set null');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
