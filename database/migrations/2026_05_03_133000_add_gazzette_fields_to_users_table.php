<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'analyst_is_gazzetted')) {
                $table->boolean('analyst_is_gazzetted')->default(false)->after('employment_date');
            }

            if (!Schema::hasColumn('users', 'date_of_gazzette')) {
                $table->date('date_of_gazzette')->nullable()->after('analyst_is_gazzetted');
            }

            if (!Schema::hasColumn('users', 'start_of_career')) {
                $table->dateTime('start_of_career')->nullable()->after('date_of_gazzette');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'start_of_career')) {
                $table->dropColumn('start_of_career');
            }

            if (Schema::hasColumn('users', 'date_of_gazzette')) {
                $table->dropColumn('date_of_gazzette');
            }

            if (Schema::hasColumn('users', 'analyst_is_gazzetted')) {
                $table->dropColumn('analyst_is_gazzetted');
            }
        });
    }
};
