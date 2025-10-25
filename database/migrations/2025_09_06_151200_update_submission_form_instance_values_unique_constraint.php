<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSubmissionFormInstanceValuesUniqueConstraint extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submission_form_instance_values', function (Blueprint $table) {
            // Drop the old unique constraint
            $table->dropUnique('sf_values_unique_instance_element');
            
            // Add the new unique constraint with array_index
            $table->unique(['submission_form_instance_id', 'submission_form_element_id', 'array_index'], 'sf_values_unique_instance_element');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('submission_form_instance_values', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('sf_values_unique_instance_element');
            
            // Restore the old unique constraint without array_index
            $table->unique(['submission_form_instance_id', 'submission_form_element_id'], 'sf_values_unique_instance_element');
        });
    }
}
