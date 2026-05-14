<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('equipment_disposal_approval_workflow_steps', function (Blueprint $table) {
            // Drop old column if exists and add new uuid column
            $table->dropColumn('assignee_id');
        });
        Schema::table('equipment_disposal_approval_workflow_steps', function (Blueprint $table) {
            $table->uuid('assignee_id')->after('assignee_type');
        });
    }

    public function down()
    {
        Schema::table('equipment_disposal_approval_workflow_steps', function (Blueprint $table) {
            $table->dropColumn('assignee_id');
            $table->bigInteger('assignee_id')->after('assignee_type');
        });
    }
};
