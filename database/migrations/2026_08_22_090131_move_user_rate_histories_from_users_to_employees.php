<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const EMPLOYEE_RATE_EFFECTIVE_UNIQUE = 'urh_employee_rate_effective_unique';

    private const USER_RATE_EFFECTIVE_UNIQUE = 'urh_user_rate_effective_unique';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_rate_histories', function (Blueprint $table) {
            if (Schema::hasColumn('user_rate_histories', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropUnique(['user_id', 'rate_type_id', 'effective_from']);
                $table->dropColumn('user_id');
            }
        });

        Schema::table('user_rate_histories', function (Blueprint $table) {
            if (! Schema::hasColumn('user_rate_histories', 'employee_id')) {
                $table->foreignId('employee_id')->after('id')->constrained()->cascadeOnDelete();
            }
        });

        Schema::table('user_rate_histories', function (Blueprint $table) {
            $table->unique(['employee_id', 'rate_type_id', 'effective_from'], self::EMPLOYEE_RATE_EFFECTIVE_UNIQUE);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_rate_histories', function (Blueprint $table) {
            if (Schema::hasColumn('user_rate_histories', 'employee_id')) {
                $table->dropForeign(['employee_id']);
                $table->dropUnique(self::EMPLOYEE_RATE_EFFECTIVE_UNIQUE);
                $table->dropColumn('employee_id');
            }
        });

        Schema::table('user_rate_histories', function (Blueprint $table) {
            if (! Schema::hasColumn('user_rate_histories', 'user_id')) {
                $table->foreignId('user_id')->after('id')->constrained()->cascadeOnDelete();
            }
        });

        Schema::table('user_rate_histories', function (Blueprint $table) {
            $table->unique(['user_id', 'rate_type_id', 'effective_from'], self::USER_RATE_EFFECTIVE_UNIQUE);
        });
    }
};
