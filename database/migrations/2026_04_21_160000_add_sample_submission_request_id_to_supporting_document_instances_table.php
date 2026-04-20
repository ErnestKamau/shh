<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supporting_document_instances', function (Blueprint $table) {
            $table->unsignedBigInteger('sample_submission_request_id')
                ->nullable()
                ->after('template_version')
                ->index();

            $table->foreign('sample_submission_request_id', 'sdoc_instances_submission_request_fk')
                ->references('id')
                ->on('sample_submission_requests')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('supporting_document_instances', function (Blueprint $table) {
            $table->dropForeign('sdoc_instances_submission_request_fk');
            $table->dropColumn('sample_submission_request_id');
        });
    }
};
