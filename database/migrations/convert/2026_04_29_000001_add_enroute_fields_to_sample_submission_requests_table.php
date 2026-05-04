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
        Schema::table('sample_submission_requests', function (Blueprint $table) {
            $table->date('submission_date')->nullable()->after('received_by_time');
            $table->string('group_of_samples')->nullable()->after('submission_date');
            $table->unsignedInteger('number_of_samples')->nullable()->after('group_of_samples');
            $table->text('description_of_samples')->nullable()->after('number_of_samples');
            $table->string('gcla_file_reference_number')->nullable()->after('description_of_samples');
            $table->boolean('is_police_sample')->default(false)->after('gcla_file_reference_number');
            $table->string('ir_number')->nullable()->after('is_police_sample');
            $table->string('booking_date_status')->nullable()->after('ir_number');
            $table->dateTime('booking_date_reviewed_at')->nullable()->after('booking_date_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_submission_requests', function (Blueprint $table) {
            $table->dropColumn([
                'submission_date',
                'group_of_samples',
                'number_of_samples',
                'description_of_samples',
                'gcla_file_reference_number',
                'is_police_sample',
                'ir_number',
                'booking_date_status',
                'booking_date_reviewed_at',
            ]);
        });
    }
};
