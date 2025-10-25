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
        Schema::table('sample_detail_staging', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn([
                'sample_detail_id',
                'sample_type_name',
                'sample_point',
                'sample_description',
                'sampling_date',
                'sampling_time',
                'test_required',
                'comments',
                'temperature',
                'ph',
                'ppm',
                'sample_header_staging_id',
                'sample_point_id',
                'test_required_ids',
                'analysis_type_id',
                'product_id',
                'status'
            ]);
            
            // Add new columns
            $table->unsignedBigInteger('sample_header_id')->after('id');
            $table->json('data_json')->after('sample_header_id');
            $table->boolean('is_processed')->default(0)->after('data_json');
            
            // Add foreign key
            $table->foreign('sample_header_id')->references('id')->on('sample_headers')->onDelete('cascade');
            
            // Add index for performance
            $table->index(['sample_header_id', 'is_processed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_detail_staging', function (Blueprint $table) {
            // Drop new structure
            $table->dropForeign(['sample_header_id']);
            $table->dropIndex(['sample_header_id', 'is_processed']);
            $table->dropColumn(['sample_header_id', 'data_json', 'is_processed']);
            
            // Restore old columns
            $table->integer('sample_detail_id')->nullable();
            $table->string('sample_type_name')->nullable();
            $table->string('sample_point')->nullable();
            $table->text('sample_description')->nullable();
            $table->date('sampling_date')->nullable();
            $table->time('sampling_time')->nullable();
            $table->text('test_required')->nullable();
            $table->text('comments')->nullable();
            $table->string('temperature')->nullable();
            $table->string('ph')->nullable();
            $table->string('ppm')->nullable();
            $table->integer('sample_header_staging_id')->nullable();
            $table->integer('sample_point_id')->nullable();
            $table->string('test_required_ids')->nullable();
            $table->integer('analysis_type_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->tinyInteger('status')->nullable();
        });
    }
};
