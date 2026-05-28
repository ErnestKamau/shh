<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('case_file_review_forms', 'batch_id')) {
            // Drop the incorrect bigint column and re-add it as UUID
            Schema::table('case_file_review_forms', function (Blueprint $table) {
                $table->dropColumn('batch_id');
            });
            
            Schema::table('case_file_review_forms', function (Blueprint $table) {
                $table->uuid('batch_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('case_file_review_forms', function (Blueprint $table) {
            $table->dropColumn('batch_id');
        });
        
        Schema::table('case_file_review_forms', function (Blueprint $table) {
            $table->unsignedBigInteger('batch_id')->nullable();
        });
    }
};
