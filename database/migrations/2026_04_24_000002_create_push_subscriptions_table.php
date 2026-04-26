<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('push_subscriptions')) {
            Schema::create('push_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('user_id');
                $table->string('endpoint', 500);
                $table->string('p256dh', 255);
                $table->string('auth', 255);
                $table->string('browser_name', 50)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('push_subscriptions', 'browser_name')) {
            Schema::table('push_subscriptions', function (Blueprint $table) {
                $table->string('browser_name', 50)->nullable()->after('auth');
            });
        }

        try {
            Schema::table('push_subscriptions', function (Blueprint $table) {
                $table->unique('endpoint');
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('push_subscriptions', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
