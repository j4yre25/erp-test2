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
        Schema::create('employee_compensation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('daily_rate', 12, 2)->default(0);
            $table->decimal('night_differential_rate', 12, 2)->default(0);
            $table->string('sss_number')->nullable();
            $table->string('philhealth_number')->nullable();
            $table->string('pagibig_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_compensation_details');
    }
};
