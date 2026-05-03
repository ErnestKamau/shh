<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_access_requests', function (Blueprint $table): void {
            $table->bigIncrements('id');

            $table->text('full_name_or_organisation_enc');
            $table->text('address_enc');
            $table->string('zone');

            $table->text('tin_number_enc');
            $table->text('email_enc');
            $table->text('phone_number_enc');

            $table->string('postal_code', 50);
            $table->text('request_ip_enc')->nullable();
            $table->text('user_agent_enc')->nullable();

            $table->string('status', 30)->default('pending')->index();
            $table->text('review_notes')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_access_requests');
    }
};
