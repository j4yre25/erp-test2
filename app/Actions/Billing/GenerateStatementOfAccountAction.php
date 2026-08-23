<?php

namespace App\Actions\Billing;

use App\Models\AccountReceivable;
use App\Models\AccountReceivableStatus;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class GenerateStatementOfAccountAction
{
    /**
     * Generate a new draft SOA from a closed payroll period.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): AccountReceivable
    {
        /** @var PayrollPeriod $period */
        $period = PayrollPeriod::query()
            ->with(['payrollLines', 'client'])
            ->findOrFail($data['payroll_period_id']);

        if (! $period->isClosed()) {
            throw new InvalidArgumentException('Only closed payroll periods can be converted into a Statement of Account.');
        }

        // Check if an SOA already exists for this client and payroll period
        $existing = AccountReceivable::query()
            ->where('client_id', $period->client_id)
            ->where('payroll_period_id', $period->id)
            ->first();

        if ($existing) {
            throw new InvalidArgumentException('A Statement of Account has already been generated for this payroll period.');
        }

        $subtotal = (float) ($data['subtotal'] ?? $period->payrollLines->sum('billable_amount'));
        $taxAmount = (float) ($data['tax_amount'] ?? 0);
        $totalAmount = round($subtotal + $taxAmount, 2);

        $date = $data['account_receivable_date'] ?? now()->toDateString();
        $soaNumber = $data['account_receivable_number'] ?? $this->generateSoaNumber();
        $draftStatus = AccountReceivableStatus::query()->firstOrCreate(['code' => 'draft'], ['name' => 'Draft']);

        return DB::transaction(function () use ($period, $soaNumber, $date, $subtotal, $taxAmount, $totalAmount, $draftStatus): AccountReceivable {
            return AccountReceivable::query()->create([
                'client_id' => $period->client_id,
                'payroll_period_id' => $period->id,
                'account_receivable_status_id' => $draftStatus->id,
                'account_receivable_number' => $soaNumber,
                'account_receivable_date' => $date,
                'due_date' => null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'running_balance' => $totalAmount,
            ]);
        });
    }

    protected function generateSoaNumber(): string
    {
        $prefix = 'SOA-'.now()->format('Ym');
        $random = strtoupper(Str::random(4));
        $count = AccountReceivable::query()->count() + 1;

        return sprintf('%s-%04d-%s', $prefix, $count, $random);
    }
}
