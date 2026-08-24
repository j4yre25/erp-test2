<?php

namespace App\Http\Controllers;

use App\Actions\Payroll\ClosePayrollPeriodAction;
use App\Http\Requests\ClosePayrollPeriodRequest;
use App\Http\Requests\StorePayrollPeriodRequest;
use App\Models\Client;
use App\Models\PayrollLine;
use App\Models\PayrollPeriod;
use App\Models\PayrollPeriodStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PayrollPeriodController extends Controller
{
    public function __construct(
        protected ClosePayrollPeriodAction $closePayrollPeriodAction,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', PayrollPeriod::class);

        return Inertia::render('payroll-periods/index', [
            'periods' => PayrollPeriod::query()
                ->with(['client', 'status'])
                ->withCount('payrollLines')
                ->withSum('payrollLines', 'gross_amount')
                ->withSum('payrollLines', 'billable_amount')
                ->latest()
                ->get()
                ->map(fn (PayrollPeriod $period): array => $this->periodSummaryPayload($period))
                ->values(),
            'can' => [
                'create_period' => $request->user()->can('create', PayrollPeriod::class),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', PayrollPeriod::class);

        return Inertia::render('payroll-periods/create', [
            'clients' => Client::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StorePayrollPeriodRequest $request): RedirectResponse
    {
        $openStatus = PayrollPeriodStatus::query()->firstOrCreate(['code' => 'open'], ['name' => 'Open']);

        $period = PayrollPeriod::query()->create([
            ...$request->validated(),
            'payroll_period_status_id' => $openStatus->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Payroll period opened.')]);

        return to_route('payroll-periods.show', $period);
    }

    public function show(PayrollPeriod $payrollPeriod): Response
    {
        $this->authorize('view', $payrollPeriod);

        $payrollPeriod->load([
            'client',
            'status',
            'payrollLines.employee.compensationDetail',
            'payrollLines.deployment',
        ]);

        return Inertia::render('payroll-periods/show', [
            'period' => $this->periodDetailPayload($payrollPeriod),
            'can' => [
                'close_period' => request()->user()?->can('close', $payrollPeriod) ?? false,
            ],
        ]);
    }

    public function close(ClosePayrollPeriodRequest $request, PayrollPeriod $payrollPeriod): RedirectResponse
    {
        try {
            $this->closePayrollPeriodAction->execute($payrollPeriod);
            Inertia::flash('toast', ['type' => 'success', 'message' => __('Payroll period closed and pay lines computed successfully.')]);
        } catch (\InvalidArgumentException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }

        return to_route('payroll-periods.show', $payrollPeriod);
    }

    /**
     * @return array<string, mixed>
     */
    protected function periodSummaryPayload(PayrollPeriod $period): array
    {
        return [
            'id' => $period->id,
            'client_id' => $period->client_id,
            'client_name' => $period->client?->name,
            'start_date' => $period->start_date?->toDateString(),
            'end_date' => $period->end_date?->toDateString(),
            'status' => $period->status?->code ?? 'open',
            'lines_count' => (int) ($period->payroll_lines_count ?? 0),
            'total_gross_pay' => (float) ($period->payroll_lines_sum_gross_amount ?? 0),
            'total_billable_amount' => (float) ($period->payroll_lines_sum_billable_amount ?? 0),
            'can_close' => request()->user()?->can('close', $period) ?? false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function periodDetailPayload(PayrollPeriod $period): array
    {
        return [
            'id' => $period->id,
            'client_id' => $period->client_id,
            'client_name' => $period->client?->name,
            'start_date' => $period->start_date?->toDateString(),
            'end_date' => $period->end_date?->toDateString(),
            'status' => $period->status?->code ?? 'open',
            'lines' => $period->payrollLines->map(fn (PayrollLine $line): array => [
                'id' => $line->id,
                'employee_number' => $line->employee?->employee_number,
                'guard_name' => trim(($line->employee?->first_name ?? '').' '.($line->employee?->last_name ?? '')),
                'post_name' => $line->deployment?->post_name ?? 'General Post',
                'regular_hours' => (float) $line->regular_hours,
                'overtime_hours' => (float) $line->overtime_hours,
                'night_diff_hours' => (float) $line->night_diff_hours,
                'base_pay' => (float) $line->base_pay,
                'night_diff_pay' => (float) $line->night_diff_pay,
                'employer_sss' => (float) $line->employer_sss,
                'employer_philhealth' => (float) $line->employer_philhealth,
                'employer_pagibig' => (float) $line->employer_pagibig,
                'gross_amount' => (float) $line->gross_amount,
                'billable_amount' => (float) $line->billable_amount,
                'sss_number' => $line->employee?->compensationDetail?->sss_number,
                'philhealth_number' => $line->employee?->compensationDetail?->philhealth_number,
                'pagibig_number' => $line->employee?->compensationDetail?->pagibig_number,
            ])->values(),
        ];
    }
}
