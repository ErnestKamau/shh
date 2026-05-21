<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql_ai';

    public function up(): void
    {
        Schema::connection($this->connection)->create('manifest_intents', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('domain', 50);
            $table->string('group_type', 10)->default('A'); // 'A' or 'B'
            $table->text('sql_query');
            $table->text('description');
            $table->string('output_format', 30)->default('table'); // 'count' or 'table'
            $table->integer('ttl_seconds')->default(120);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('manifest_intents');
    }
};
