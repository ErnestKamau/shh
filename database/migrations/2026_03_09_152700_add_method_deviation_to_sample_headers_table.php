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
		Schema::table('sample_headers', function (Blueprint $table) {
			$table->boolean('has_method_deviation')
				->default(false)
				->after('status');

			$table->text('method_deviation_reason')
				->nullable()
				->after('has_method_deviation');
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		Schema::table('sample_headers', function (Blueprint $table) {
			$table->dropColumn(['has_method_deviation', 'method_deviation_reason']);
		});
    }
};
