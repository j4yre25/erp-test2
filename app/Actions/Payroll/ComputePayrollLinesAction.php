<?php

namespace App\Actions\Payroll;

use App\Models\DailyTimeRecord;
use App\Models\Deployment;
use App\Models\Employee;
use App\Models\PayrollLine;
use App\Models\PayrollPeriod;
use Carbon\CarbonInterface;
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

        $periodStartDate = Carbon::parse($payrollPeriod->start_date)->toDateString();
        $periodEndDate = Carbon::parse($payrollPeriod->end_date)->toDateString();

        $deployments = Deployment::query()
            ->with(['employee.compensationDetail'])
            ->where('client_id', $payrollPeriod->client_id)
            ->where('status', 'active')
            ->whereHas('employee', function ($query): void {
                $query->where('employment_status', 'active');
            })
            ->whereDate('start_date', '<=', $periodEndDate)
            ->where(function ($query) use ($periodStartDate): void {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $periodStartDate);
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
                $nightDiffHours = (float) $dtrs->sum(function (DailyTimeRecord $dtr): float {
                    if ((float) $dtr->night_diff_hours > 0) {
                        return (float) $dtr->night_diff_hours;
                    }

                    if ($dtr->time_in && $dtr->time_out) {
                        return $this->deriveNightDiffHours($dtr->time_in, $dtr->time_out);
                    }

                    return 0.0;
                });
            } else {
                // Fallback: estimate standard working days within the period intersection with deployment active dates
                $periodStart = Carbon::parse($payrollPeriod->start_date);
                $periodEnd = Carbon::parse($payrollPeriod->end_date);
                $deployStart = Carbon::parse($deployment->start_date);
                $deployEnd = $deployment->end_date ? Carbon::parse($deployment->end_date) : null;

                $effectiveStart = $deployStart->greaterThan($periodStart) ? $deployStart : $periodStart;
                $effectiveEnd = $deployEnd && $deployEnd->lessThan($periodEnd) ? $deployEnd : $periodEnd;

                $days = $effectiveStart->lessThanOrEqualTo($effectiveEnd)
                    ? $effectiveStart->diffInDays($effectiveEnd) + 1
                    : 0;

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

    /**
     * Derive night differential hours between 22:00 and 06:00 from time_in and time_out.
     */
    public function deriveNightDiffHours(CarbonInterface|\DateTimeInterface $timeIn, CarbonInterface|\DateTimeInterface $timeOut): float
    {
        $start = Carbon::parse($timeIn);
        $end = Carbon::parse($timeOut);

        if ($start->greaterThanOrEqualTo($end)) {
            return 0.0;
        }

        $totalNightSeconds = 0;

        // Iterate through each calendar day covered by the time span
        $currentDay = $start->copy()->startOfDay()->subDay();
        $endDay = $end->copy()->startOfDay()->addDay();

        while ($currentDay->lessThanOrEqualTo($endDay)) {
            // Night diff window for current day: 22:00 to 06:00 next day
            $windowStart = $currentDay->copy()->setTime(22, 0, 0);
            $windowEnd = $currentDay->copy()->addDay()->setTime(6, 0, 0);

            $overlapStart = max($start->timestamp, $windowStart->timestamp);
            $overlapEnd = min($end->timestamp, $windowEnd->timestamp);

            if ($overlapEnd > $overlapStart) {
                $totalNightSeconds += ($overlapEnd - $overlapStart);
            }

            $currentDay->addDay();
        }

        return round($totalNightSeconds / 3600, 2);
    }
}
