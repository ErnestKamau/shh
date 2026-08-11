<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('sample_points') && ! Schema::hasColumn('sample_points', 'contact_id')) {
            Schema::table('sample_points', function (Blueprint $table) {
                $table->uuid('contact_id')->nullable()->index();
            });
        }

        if (Schema::hasTable('crm_customers') && Schema::hasColumn('crm_customers', 'sample_point_configurable_name')) {
            DB::table('crm_customers')
                ->whereIn('sample_point_configurable_name', ['Sampling Points', 'Sample Points', 'Sample Point'])
                ->update(['sample_point_configurable_name' => 'Sampling Location']);
        }

        if (Schema::hasTable('language_lines')) {
            $keys = [
                'sample_points',
                'sample_collection_points',
                'sample_points_management',
                'create_new_sample_point',
                'sample_point_name',
                'sample_point_code',
                'create_sample_point',
            ];

            $lines = DB::table('language_lines')
                ->where('group', 'crm')
                ->whereIn('key', $keys)
                ->get();

            foreach ($lines as $line) {
                $text = json_decode($line->text, true);
                if (! is_array($text)) {
                    continue;
                }

                $updated = false;
                foreach ($text as $locale => $value) {
                    if (! is_string($value)) {
                        continue;
                    }

                    $replacement = str_replace(
                        ['Sampling Points', 'Sample Points', 'Sample Point'],
                        ['Sampling Location', 'Sampling Location', 'Sampling Location'],
                        $value
                    );

                    if ($replacement !== $value) {
                        $text[$locale] = $replacement;
                        $updated = true;
                    }
                }

                if ($updated) {
                    DB::table('language_lines')
                        ->where('id', $line->id)
                        ->update(['text' => json_encode($text)]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sample_points') && Schema::hasColumn('sample_points', 'contact_id')) {
            Schema::table('sample_points', function (Blueprint $table) {
                $table->dropColumn('contact_id');
            });
        }
    }
};
