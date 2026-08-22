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
        Schema::create('daily_time_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained()->restrictOnDelete();
            $table->date('work_date')->index();
            $table->dateTime('time_in')->nullable();
            $table->dateTime('time_out')->nullable();
            $table->decimal('regular_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->string('status')->default('pending')->index();
            $table->timestamps();

            $table->unique(['payroll_period_id', 'work_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_time_records');
    }
};
