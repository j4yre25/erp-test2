<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CLIENT_INDEX = 'deployments_client_id_index';

    private const CLIENT_EMPLOYEE_UNIQUE = 'deployments_client_employee_unique';

    private const CLIENT_USER_UNIQUE = 'deployments_client_user_unique';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deployments', function (Blueprint $table) {
            $table->index('client_id', self::CLIENT_INDEX);
        });

        Schema::table('deployments', function (Blueprint $table) {
            if (Schema::hasColumn('deployments', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropUnique(['client_id', 'user_id']);
                $table->dropColumn('user_id');
            }
        });

        Schema::table('deployments', function (Blueprint $table) {
            if (! Schema::hasColumn('deployments', 'employee_id')) {
                $table->foreignId('employee_id')->after('client_id')->constrained()->restrictOnDelete();
            }
        });

        Schema::table('deployments', function (Blueprint $table) {
            $table->unique(['client_id', 'employee_id'], self::CLIENT_EMPLOYEE_UNIQUE);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deployments', function (Blueprint $table) {
            if (Schema::hasColumn('deployments', 'employee_id')) {
                $table->dropForeign(['employee_id']);
                $table->dropUnique(self::CLIENT_EMPLOYEE_UNIQUE);
                $table->dropColumn('employee_id');
            }
        });

        Schema::table('deployments', function (Blueprint $table) {
            if (! Schema::hasColumn('deployments', 'user_id')) {
                $table->foreignId('user_id')->after('client_id')->constrained()->restrictOnDelete();
            }
        });

        Schema::table('deployments', function (Blueprint $table) {
            $table->unique(['client_id', 'user_id'], self::CLIENT_USER_UNIQUE);
            $table->dropIndex(self::CLIENT_INDEX);
        });
    }
};
