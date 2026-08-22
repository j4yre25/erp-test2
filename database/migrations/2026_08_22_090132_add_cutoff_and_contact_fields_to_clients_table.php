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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('contact_person')->nullable()->after('company_address');
            $table->string('cutoff_type')->nullable()->after('payroll_period')->index();
            $table->unsignedTinyInteger('first_cutoff_day')->nullable()->after('cutoff_type');
            $table->unsignedTinyInteger('second_cutoff_day')->nullable()->after('first_cutoff_day');
            $table->string('payroll_frequency')->nullable()->after('second_cutoff_day')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'contact_person',
                'cutoff_type',
                'first_cutoff_day',
                'second_cutoff_day',
                'payroll_frequency',
            ]);
        });
    }
};
