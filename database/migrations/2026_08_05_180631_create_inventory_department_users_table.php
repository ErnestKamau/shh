<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventory_department_users')) {
            Schema::create('inventory_department_users', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('user_id')->index();
                $table->uuid('inventory_department_id')->index();
                $table->timestamps();

                $table->unique(['user_id', 'inventory_department_id'], 'inventory_department_users_user_dept_unique');
            });
        }

        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'department_id')) {
            return;
        }

        $now = now();

        DB::table('users')
            ->whereNotNull('department_id')
            ->where('department_id', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($now): void {
                $rows = [];

                foreach ($users as $user) {
                    $departmentId = (string) $user->department_id;
                    if ($departmentId === '') {
                        continue;
                    }

                    $exists = DB::table('inventory_department_users')
                        ->where('user_id', $user->id)
                        ->where('inventory_department_id', $departmentId)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $rows[] = [
                        'id' => (string) Str::uuid(),
                        'user_id' => $user->id,
                        'inventory_department_id' => $departmentId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($rows !== []) {
                    DB::table('inventory_department_users')->insert($rows);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_department_users');
    }
};
