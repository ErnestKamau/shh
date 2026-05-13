<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('email_sents')) {
            return;
        }

        Schema::dropIfExists('email_sents_uuid_tmp');

        Schema::create('email_sents_uuid_tmp', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email');
            $table->string('subject');
            $table->text('body');
            $table->timestamps();
        });

        $rows = DB::table('email_sents')->get(['email', 'subject', 'body', 'created_at', 'updated_at']);

        foreach ($rows as $row) {
            DB::table('email_sents_uuid_tmp')->insert([
                'id' => (string) Str::uuid(),
                'email' => $row->email,
                'subject' => $row->subject,
                'body' => $row->body,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        Schema::drop('email_sents');
        Schema::rename('email_sents_uuid_tmp', 'email_sents');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('email_sents')) {
            return;
        }

        Schema::dropIfExists('email_sents_int_tmp');

        Schema::create('email_sents_int_tmp', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('email');
            $table->string('subject');
            $table->text('body');
            $table->timestamps();
        });

        $rows = DB::table('email_sents')->orderBy('created_at')->get(['email', 'subject', 'body', 'created_at', 'updated_at']);

        $counter = 1;
        foreach ($rows as $row) {
            DB::table('email_sents_int_tmp')->insert([
                'id' => $counter,
                'email' => $row->email,
                'subject' => $row->subject,
                'body' => $row->body,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);

            $counter++;
        }

        Schema::drop('email_sents');
        Schema::rename('email_sents_int_tmp', 'email_sents');
    }
};
