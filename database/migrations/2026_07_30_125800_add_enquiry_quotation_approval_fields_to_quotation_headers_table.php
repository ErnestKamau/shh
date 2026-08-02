<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_headers', function (Blueprint $table): void {
            if (! Schema::hasColumn('quotation_headers', 'approval_requested_at')) {
                $table->timestamp('approval_requested_at')->nullable()->after('approved_by');
            }
            if (! Schema::hasColumn('quotation_headers', 'approval_requested_by')) {
                $table->string('approval_requested_by', 36)->nullable()->after('approval_requested_at');
            }
            if (! Schema::hasColumn('quotation_headers', 'approval_decision_at')) {
                $table->timestamp('approval_decision_at')->nullable()->after('approval_requested_by');
            }
            if (! Schema::hasColumn('quotation_headers', 'approval_comments')) {
                $table->text('approval_comments')->nullable()->after('approval_decision_at');
            }
        });

        if (! Schema::hasTable('quotation_approval_logs')) {
            Schema::create('quotation_approval_logs', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('quotation_header_id')->index();
                $table->uuid('sample_submission_request_id')->nullable()->index();
                $table->string('actor_user_id', 36)->nullable()->index();
                $table->string('assignee_user_id', 36)->nullable()->index();
                $table->string('action', 32);
                $table->text('comments')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_approval_logs');

        Schema::table('quotation_headers', function (Blueprint $table): void {
            foreach (['approval_requested_at', 'approval_requested_by', 'approval_decision_at', 'approval_comments'] as $column) {
                if (Schema::hasColumn('quotation_headers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
