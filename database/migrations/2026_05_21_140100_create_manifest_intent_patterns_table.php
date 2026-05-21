<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql_ai';

    public function up(): void
    {
        Schema::connection($this->connection)->create('manifest_intent_patterns', function (Blueprint $table) {
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
