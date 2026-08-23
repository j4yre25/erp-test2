<?php

namespace App\Actions\Payroll;

use App\Models\Deployment;
use App\Models\Employee;
use App\Models\PayrollLine;
use App\Models\PayrollPeriod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class ComputePayrollLinesAction
{
    /**
     * Compute and persist payroll lines for all guards deployed under the client payroll period.
     *
     * @return Collection<int, PayrollLine>
     */
    public function execute(PayrollPeriod $payrollPeriod): Collection
    {
        $payrollPeriod->loadMissing(['client', 'dailyTimeRecords.deployment.employee.compensationDetail']);

        $deployments = Deployment::query()
            ->with(['employee.compensationDetail'])
            ->where('client_id', $payrollPeriod->client_id)
            ->where('start_date', '<=', $payrollPeriod->end_date)
            ->where(function ($query) use ($payrollPeriod): void {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $payrollPeriod->start_date);
            })
            ->get();

        $computedLines = new Collection;

        foreach ($deployments as $deployment) {
            /** @var Employee|null $employee */
            $employee = $deployment->employee;

            if (! $employee) {
                continue;
            }

            $compensation = $employee->compensationDetail;
            $dailyRate = (float) ($compensation?->daily_rate ?? 0);
            $hourlyRate = $dailyRate > 0 ? $dailyRate / 8 : 0;
            $nightDiffRate = (float) ($compensation?->night_differential_rate ?? ($hourlyRate * 0.10));

            // Calculate hours from existing DTRs for this period and deployment
            $dtrs = $payrollPeriod->dailyTimeRecords
                ->where('deployment_id', $deployment->id);

            if ($dtrs->isNotEmpty()) {
                $regularHours = (float) $dtrs->sum('regular_hours');
                $overtimeHours = (float) $dtrs->sum('overtime_hours');
                $nightDiffHours = (float) $dtrs->sum(function ($dtr): float {
                    return (float) ($dtr->night_diff_hours ?? 0);
                });
            } else {
                // Fallback: estimate standard working days within the period
                $start = Carbon::parse($payrollPeriod->start_date);
                $end = Carbon::parse($payrollPeriod->end_date);
                $days = $start->diffInDays($end) + 1;

                $regularHours = (float) ($days * 8);
                $overtimeHours = 0.0;
                $nightDiffHours = 0.0;
            }

            $basePay = ($regularHours * $hourlyRate) + ($overtimeHours * $hourlyRate * 1.25);
            $nightDiffPay = $nightDiffHours * $nightDiffRate;

            // Employer statutory contributions (employer-side share)
            $employerSss = round($basePay * 0.095, 2);
            $employerPhilhealth = round($basePay * 0.025, 2);
            $employerPagibig = round(min(200.00, $basePay * 0.02), 2);

            $grossAmount = round($basePay + $nightDiffPay, 2);

            // Compute billable amount based on deployment billing rate
            $billingDailyRate = (float) ($deployment->billing_rate ?? 0);
            $billingHourlyRate = $billingDailyRate > 0 ? $billingDailyRate / 8 : $hourlyRate;
            $billableAmount = round(($regularHours * $billingHourlyRate) + ($overtimeHours * $billingHourlyRate * 1.25) + $nightDiffPay, 2);

            $line = PayrollLine::query()->updateOrCreate(
                [
                    'payroll_period_id' => $payrollPeriod->id,
                    'deployment_id' => $deployment->id,
                    'employee_id' => $employee->id,
                ],
                [
                    'regular_hours' => $regularHours,
                    'overtime_hours' => $overtimeHours,
                    'night_diff_hours' => $nightDiffHours,
                    'base_pay' => $basePay,
                    'night_diff_pay' => $nightDiffPay,
                    'employer_sss' => $employerSss,
                    'employer_philhealth' => $employerPhilhealth,
                    'employer_pagibig' => $employerPagibig,
                    'gross_amount' => $grossAmount,
                    'billable_amount' => $billableAmount,
                ],
            );

            $computedLines->push($line);
        }

        return $computedLines;
    }
}
