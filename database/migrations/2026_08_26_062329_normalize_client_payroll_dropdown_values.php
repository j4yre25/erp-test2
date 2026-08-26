<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Maps legacy free-text values to the canonical dropdown values now
     * enforced by StoreClientRequest / UpdateClientRequest.
     *
     * @var array<string, array<string, string>>
     */
    private array $map = [
        'payroll_period' => [
            'weekly' => 'weekly',
            'semi-monthly' => 'semi_monthly',
            'semi monthly' => 'semi_monthly',
            'semimonthly' => 'semi_monthly',
            'monthly' => 'monthly',
        ],
        'payroll_frequency' => [
            'weekly' => 'weekly',
            'bi-weekly' => 'bi_weekly',
            'biweekly' => 'bi_weekly',
            'twice a month' => 'semi_monthly',
            'semi-monthly' => 'semi_monthly',
            'semi monthly' => 'semi_monthly',
            'semimonthly' => 'semi_monthly',
            'monthly' => 'monthly',
        ],
        'cutoff_type' => [
            'fixed' => 'fixed_days',
            'fixed_days' => 'fixed_days',
            'standard' => 'fixed_days',
            'end of month' => 'end_of_month',
            'end_of_month' => 'end_of_month',
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->map as $column => $valuesByLowercase) {
            $rows = DB::table('clients')->whereNotNull($column)->select('id', $column)->get();

            foreach ($rows as $row) {
                $normalized = $valuesByLowercase[strtolower(trim($row->{$column}))] ?? null;

                if ($normalized !== null && $normalized !== $row->{$column}) {
                    DB::table('clients')->where('id', $row->id)->update([$column => $normalized]);
                } elseif ($normalized === null) {
                    // Unmappable legacy value: clear it so the column only ever holds
                    // one of the canonical dropdown values going forward.
                    DB::table('clients')->where('id', $row->id)->update([$column => null]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Legacy free-text values are not preserved; nothing to revert to.
    }
};
