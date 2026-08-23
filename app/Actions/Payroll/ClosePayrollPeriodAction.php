<?php

namespace App\Actions\Payroll;

use App\Models\PayrollPeriod;
use App\Models\PayrollPeriodStatus;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ClosePayrollPeriodAction
{
    public function __construct(
        protected ComputePayrollLinesAction $computePayrollLinesAction,
    ) {}

    public function execute(PayrollPeriod $payrollPeriod): PayrollPeriod
    {
        if ($payrollPeriod->isClosed()) {
            throw new InvalidArgumentException('This payroll period has already been closed.');
        }

        return DB::transaction(function () use ($payrollPeriod): PayrollPeriod {
            // Compute and generate pay lines per guard
            $this->computePayrollLinesAction->execute($payrollPeriod);

            $closedStatus = PayrollPeriodStatus::query()->firstOrCreate(['code' => 'closed'], ['name' => 'Closed']);

            $payrollPeriod->forceFill([
                'payroll_period_status_id' => $closedStatus->id,
            ])->save();

            return $payrollPeriod->fresh(['payrollLines.employee.compensationDetail', 'payrollLines.deployment', 'client', 'status']);
        });
    }
}
