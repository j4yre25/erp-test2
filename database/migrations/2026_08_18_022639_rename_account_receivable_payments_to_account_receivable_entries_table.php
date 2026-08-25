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
        if (Schema::hasTable('account_receivable_payments') && ! Schema::hasTable('account_receivable_entries')) {
            if (DB::getDriverName() !== 'sqlite') {
                Schema::table('account_receivable_payments', function (Blueprint $table) {
                    $table->dropForeign('account_receivable_payments_account_receivable_id_foreign');
                    $table->dropForeign('account_receivable_payments_payment_id_foreign');
                    $table->renameIndex('ar_payments_ar_payment_unique', 'ar_entries_ar_payment_unique');
                    $table->renameIndex('account_receivable_payments_payment_id_foreign', 'account_receivable_entries_payment_id_foreign');
                });
            }

            Schema::rename('account_receivable_payments', 'account_receivable_entries');

            if (DB::getDriverName() !== 'sqlite') {
                Schema::table('account_receivable_entries', function (Blueprint $table) {
                    $table->foreign('account_receivable_id', 'account_receivable_entries_account_receivable_id_foreign')
                        ->references('id')
                        ->on('account_receivables')
                        ->restrictOnDelete();
                    $table->foreign('payment_id', 'account_receivable_entries_payment_id_foreign')
                        ->references('id')
                        ->on('payments')
                        ->restrictOnDelete();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('account_receivable_entries') && ! Schema::hasTable('account_receivable_payments')) {
            if (DB::getDriverName() !== 'sqlite') {
                Schema::table('account_receivable_entries', function (Blueprint $table) {
                    $table->dropForeign('account_receivable_entries_account_receivable_id_foreign');
                    $table->dropForeign('account_receivable_entries_payment_id_foreign');
                    $table->renameIndex('ar_entries_ar_payment_unique', 'ar_payments_ar_payment_unique');
                    $table->renameIndex('account_receivable_entries_payment_id_foreign', 'account_receivable_payments_payment_id_foreign');
                });
            }

            Schema::rename('account_receivable_entries', 'account_receivable_payments');

            if (DB::getDriverName() !== 'sqlite') {
                Schema::table('account_receivable_payments', function (Blueprint $table) {
                    $table->foreign('account_receivable_id', 'account_receivable_payments_account_receivable_id_foreign')
                        ->references('id')
                        ->on('account_receivables')
                        ->restrictOnDelete();
                    $table->foreign('payment_id', 'account_receivable_payments_payment_id_foreign')
                        ->references('id')
                        ->on('payments')
                        ->restrictOnDelete();
                });
            }
        }
    }
};
