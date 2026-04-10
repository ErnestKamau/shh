<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('risk_business_processes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Insert default processes
        $processes = [
            ['name' => 'Production', 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Quality Control', 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Human Resources', 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Finance', 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'IT & Security', 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Supply Chain', 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sales & Marketing', 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Customer Service', 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Maintenance', 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Administration', 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
        ];
        
        \DB::table('risk_business_processes')->insert($processes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_business_processes');
    }
};
