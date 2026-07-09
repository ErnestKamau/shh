<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql_ai';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if (! $schema->hasTable('manifest_intents')) {
            $schema->create('manifest_intents', function (Blueprint $table) {
                $table->string('id', 100)->primary();
                $table->string('domain', 50);
                $table->string('group_type', 10)->default('A');
                $table->text('sql_query');
                $table->text('description');
                $table->string('output_format', 30)->default('table');
                $table->integer('ttl_seconds')->default(120);
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if ($schema->hasTable('manifest_intent_patterns')) {
            return;
        }

        $schema->create('manifest_intent_patterns', function (Blueprint $table) {
            $table->id();
            $table->string('intent_id', 100);
            $table->string('pattern', 255);
            $table->timestamps();

            $table->foreign('intent_id')
                  ->references('id')
                  ->on('manifest_intents')
                  ->onDelete('cascade');

            $table->unique(['intent_id', 'pattern']);
            $table->index('pattern');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('manifest_intent_patterns');
    }
};
