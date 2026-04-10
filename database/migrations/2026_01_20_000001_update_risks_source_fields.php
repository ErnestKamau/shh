<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if columns exist before adding
        $hasOtherSourceId = Schema::hasColumn('risks', 'other_source_id');
        $hasSampleId = Schema::hasColumn('risks', 'sample_id');
        $hasMethodId = Schema::hasColumn('risks', 'method_id');
        $hasSourceId = Schema::hasColumn('risks', 'source_id');
        
        // Drop existing foreign key first if source_id exists
        if ($hasSourceId) {
            try {
                Schema::table('risks', function (Blueprint $table) {
                    $table->dropForeign(['source_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
        }
        
        // Add new columns for other_source if they don't exist
        if (!$hasOtherSourceId) {
            Schema::table('risks', function (Blueprint $table) use ($hasSourceId) {
                if ($hasSourceId) {
                    $table->unsignedBigInteger('other_source_id')->nullable()->after('source_name');
                    $table->string('other_source_name')->nullable()->after('other_source_id');
                } else {
                    $table->unsignedBigInteger('other_source_id')->nullable();
                    $table->string('other_source_name')->nullable();
                }
            });
        }
        
        // Copy data from source_id to other_source_id if source_id exists
        if ($hasSourceId && !$hasOtherSourceId) {
            DB::statement('UPDATE risks SET other_source_id = source_id, other_source_name = source_name');
        }
        
        // Add source linking fields like in non-conformances if they don't exist
        if (!$hasSampleId) {
            Schema::table('risks', function (Blueprint $table) {
                // sample_headers.id is signed bigInteger in this codebase
                $table->bigInteger('sample_id')->nullable()->after('personnel_id');
                $table->string('sample_reference')->nullable()->after('sample_id');
            });
        }
        
        if (!$hasMethodId) {
            Schema::table('risks', function (Blueprint $table) {
                // analysis_methods.id is signed bigInteger in this codebase
                $table->bigInteger('method_id')->nullable()->after('sample_reference');
                $table->string('method_reference')->nullable()->after('method_id');
            });
        }
        
        // Drop old columns if they exist
        if ($hasSourceId) {
            Schema::table('risks', function (Blueprint $table) {
                $table->dropColumn(['source_id', 'source_name']);
            });
        }
        
        // Add foreign keys and indexes
        Schema::table('risks', function (Blueprint $table) use ($hasOtherSourceId, $hasSampleId, $hasMethodId) {
            // Add foreign key for other_source_id
            if (!$hasOtherSourceId) {
                try {
                    $table->foreign('other_source_id')->references('id')->on('risk_sources')->onDelete('set null');
                } catch (\Exception $e) {
                    // Foreign key might already exist
                }
            }
            
            // Add foreign keys and indexes for sample_id
            if (!$hasSampleId) {
                try {
                    $table->foreign('sample_id')->references('id')->on('sample_headers')->onDelete('set null');
                    $table->index('sample_id');
                } catch (\Exception $e) {
                    // Foreign key or index might already exist
                }
            }
            
            // Add foreign keys and indexes for method_id
            if (!$hasMethodId) {
                try {
                    $table->foreign('method_id')->references('id')->on('analysis_methods')->onDelete('set null');
                    $table->index('method_id');
                } catch (\Exception $e) {
                    // Foreign key or index might already exist
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risks', function (Blueprint $table) {
            // Drop new fields
            $table->dropForeign(['sample_id']);
            $table->dropForeign(['method_id']);
            $table->dropForeign(['other_source_id']);
            $table->dropIndex(['sample_id']);
            $table->dropIndex(['method_id']);
            $table->dropColumn(['sample_id', 'sample_reference', 'method_id', 'method_reference']);
        });
        
        Schema::table('risks', function (Blueprint $table) {
            // Add back old columns
            $table->unsignedBigInteger('source_id')->nullable()->after('category_name');
            $table->string('source_name')->nullable()->after('source_id');
        });
        
        // Copy data back
        DB::statement('UPDATE risks SET source_id = other_source_id, source_name = other_source_name');
        
        Schema::table('risks', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn(['other_source_id', 'other_source_name']);
            
            // Restore foreign key
            $table->foreign('source_id')->references('id')->on('risk_sources')->onDelete('set null');
        });
    }
};

