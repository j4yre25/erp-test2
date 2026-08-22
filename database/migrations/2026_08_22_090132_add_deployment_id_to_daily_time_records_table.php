<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PAYROLL_PERIOD_INDEX = 'dtr_payroll_period_id_index';

    private const PERIOD_DEPLOYMENT_WORK_DATE_UNIQUE = 'dtr_period_deployment_work_date_unique';

    private const PERIOD_WORK_DATE_UNIQUE = 'dtr_period_work_date_unique';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_time_records', function (Blueprint $table) {
            $table->index('payroll_period_id', self::PAYROLL_PERIOD_INDEX);
        });

        Schema::table('daily_time_records', function (Blueprint $table) {
            $table->dropUnique(['payroll_period_id', 'work_date']);
        });

        Schema::table('daily_time_records', function (Blueprint $table) {
            if (! Schema::hasColumn('daily_time_records', 'deployment_id')) {
                $table->foreignId('deployment_id')->after('payroll_period_id')->constrained()->restrictOnDelete();
            }
        });

        Schema::table('daily_time_records', function (Blueprint $table) {
            $table->unique(['payroll_period_id', 'deployment_id', 'work_date'], self::PERIOD_DEPLOYMENT_WORK_DATE_UNIQUE);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_time_records', function (Blueprint $table) {
            if (Schema::hasColumn('daily_time_records', 'deployment_id')) {
                $table->dropUnique(self::PERIOD_DEPLOYMENT_WORK_DATE_UNIQUE);
                $table->dropForeign(['deployment_id']);
                $table->dropColumn('deployment_id');
            }
        });

        Schema::table('daily_time_records', function (Blueprint $table) {
            $table->unique(['payroll_period_id', 'work_date'], self::PERIOD_WORK_DATE_UNIQUE);
            $table->dropIndex(self::PAYROLL_PERIOD_INDEX);
        });
    }
};
