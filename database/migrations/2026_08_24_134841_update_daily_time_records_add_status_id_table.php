<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_time_records', function (Blueprint $table) {
            $table->foreignId('daily_time_record_status_id')->nullable()->constrained('daily_time_record_statuses');
            
            if (DB::getDriverName() !== 'sqlite') {
                // Drop string-based column
                $table->dropColumn('status');
            }
        });
        
        // SQLite workaround for dropping columns
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('daily_time_records', function (Blueprint $table) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_time_records', function (Blueprint $table) {
            $table->string('status')->default('pending');
            $table->dropForeign(['daily_time_record_status_id']);
            $table->dropColumn('daily_time_record_status_id');
        });
    }
};
