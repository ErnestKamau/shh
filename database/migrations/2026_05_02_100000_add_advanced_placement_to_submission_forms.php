<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdvancedPlacementToSubmissionForms extends Migration
{
    public function up()
    {
        Schema::table('submission_forms', function (Blueprint $table) {
            // How the form is displayed once placed: always visible or hidden until requested
            $table->string('display_mode', 20)->default('expanded')->after('placement_mode');

            // Per-page slot config: {"route-name": {"slot_id": "after_breadcrumb"}}
            $table->json('placement_slot')->nullable()->after('display_mode');

            // Per-page button bindings: {"route-name": ["selector-1", "selector-2"]}
            $table->json('trigger_button_ids')->nullable()->after('placement_slot');
        });
    }

    public function down()
    {
        Schema::table('submission_forms', function (Blueprint $table) {
            $table->dropColumn(['display_mode', 'placement_slot', 'trigger_button_ids']);
        });
    }
}
