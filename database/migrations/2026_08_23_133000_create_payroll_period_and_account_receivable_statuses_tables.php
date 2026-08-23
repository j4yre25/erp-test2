<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create payroll_period_statuses lookup table
        Schema::create('payroll_period_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed default payroll period statuses
        DB::table('payroll_period_statuses')->insert([
            [
                'code' => 'open',
                'name' => 'Open',
                'description' => 'Payroll period is open for recording attendance and revisions',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'closed',
                'name' => 'Closed',
                'description' => 'Payroll period is closed and pay lines are computed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $openStatusId = DB::table('payroll_period_statuses')->where('code', 'open')->value('id');
        $closedStatusId = DB::table('payroll_period_statuses')->where('code', 'closed')->value('id');

        // Add foreign key to payroll_periods table
        Schema::table('payroll_periods', function (Blueprint $table) use ($openStatusId) {
            $table->foreignId('payroll_period_status_id')
                ->nullable()
                ->after('client_id')
                ->default($openStatusId)
                ->constrained('payroll_period_statuses')
                ->restrictOnDelete();
        });

        // Migrate existing status strings to status IDs
        if (Schema::hasColumn('payroll_periods', 'status')) {
            DB::table('payroll_periods')->where('status', 'closed')->update(['payroll_period_status_id' => $closedStatusId]);
            DB::table('payroll_periods')->where('status', '!=', 'closed')->orWhereNull('status')->update(['payroll_period_status_id' => $openStatusId]);

            Schema::table('payroll_periods', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        // 2. Create account_receivable_statuses lookup table
        Schema::create('account_receivable_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed default account receivable statuses
        DB::table('account_receivable_statuses')->insert([
            [
                'code' => 'draft',
                'name' => 'Draft',
                'description' => 'Draft Statement of Account being prepared by AR',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'submitted',
                'name' => 'Submitted',
                'description' => 'Submitted to General Manager for review and approval',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'approved',
                'name' => 'Approved',
                'description' => 'Approved by General Manager and booked as official receivable',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'rejected',
                'name' => 'Rejected',
                'description' => 'Rejected by General Manager and returned for revision',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $draftStatusId = DB::table('account_receivable_statuses')->where('code', 'draft')->value('id');
        $submittedStatusId = DB::table('account_receivable_statuses')->where('code', 'submitted')->value('id');
        $approvedStatusId = DB::table('account_receivable_statuses')->where('code', 'approved')->value('id');
        $rejectedStatusId = DB::table('account_receivable_statuses')->where('code', 'rejected')->value('id');

        // Add foreign key to account_receivables table
        Schema::table('account_receivables', function (Blueprint $table) use ($draftStatusId) {
            $table->foreignId('account_receivable_status_id')
                ->nullable()
                ->after('running_balance')
                ->default($draftStatusId)
                ->constrained('account_receivable_statuses')
                ->restrictOnDelete();
        });

        // Migrate existing status strings to status IDs
        if (Schema::hasColumn('account_receivables', 'status')) {
            DB::table('account_receivables')->where('status', 'submitted')->update(['account_receivable_status_id' => $submittedStatusId]);
            DB::table('account_receivables')->where('status', 'approved')->update(['account_receivable_status_id' => $approvedStatusId]);
            DB::table('account_receivables')->where('status', 'rejected')->update(['account_receivable_status_id' => $rejectedStatusId]);
            DB::table('account_receivables')->where('status', 'draft')->orWhereNull('status')->update(['account_receivable_status_id' => $draftStatusId]);

            Schema::table('account_receivables', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert account_receivables
        Schema::table('account_receivables', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('running_balance');
        });

        $statuses = DB::table('account_receivable_statuses')->get()->keyBy('id');
        foreach ($statuses as $id => $status) {
            DB::table('account_receivables')->where('account_receivable_status_id', $id)->update(['status' => $status->code]);
        }

        Schema::table('account_receivables', function (Blueprint $table) {
            $table->dropForeign(['account_receivable_status_id']);
            $table->dropColumn('account_receivable_status_id');
        });

        Schema::dropIfExists('account_receivable_statuses');

        // Revert payroll_periods
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->string('status')->default('open')->after('end_date');
        });

        $periodStatuses = DB::table('payroll_period_statuses')->get()->keyBy('id');
        foreach ($periodStatuses as $id => $status) {
            DB::table('payroll_periods')->where('payroll_period_status_id', $id)->update(['status' => $status->code]);
        }

        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->dropForeign(['payroll_period_status_id']);
            $table->dropColumn('payroll_period_status_id');
        });

        Schema::dropIfExists('payroll_period_statuses');
    }
};
