<?php

namespace App\Http\Controllers;

use App\Actions\Billing\GenerateStatementOfAccountAction;
use App\Actions\Billing\SubmitStatementOfAccountAction;
use App\Http\Requests\StoreAccountReceivableRequest;
use App\Http\Requests\SubmitAccountReceivableRequest;
use App\Http\Requests\UpdateAccountReceivableRequest;
use App\Models\AccountReceivable;
use App\Models\PayrollLine;
use App\Models\PayrollPeriod;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountReceivableController extends Controller
{
    public function __construct(
        protected GenerateStatementOfAccountAction $generateStatementOfAccountAction,
        protected SubmitStatementOfAccountAction $submitStatementOfAccountAction,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', AccountReceivable::class);

        return Inertia::render('statement-of-accounts/index', [
            'soas' => AccountReceivable::query()
                ->with(['client', 'payrollPeriod', 'status', 'submitter', 'approver', 'rejecter'])
                ->latest()
                ->get()
                ->map(fn (AccountReceivable $soa): array => $this->soaSummaryPayload($soa, $request->user()))
                ->values(),
            'can' => [
                'create_soa' => $request->user()->can('create', AccountReceivable::class),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', AccountReceivable::class);

        // Get closed payroll periods that don't already have an SOA generated
        $availablePeriods = PayrollPeriod::query()
            ->with(['client', 'status'])
            ->withSum('payrollLines', 'billable_amount')
            ->closed()
            ->whereDoesntHave('accountReceivables')
            ->latest('end_date')
            ->get()
            ->map(fn (PayrollPeriod $period): array => [
                'id' => $period->id,
                'client_name' => $period->client?->name,
                'start_date' => $period->start_date?->toDateString(),
                'end_date' => $period->end_date?->toDateString(),
                'total_billable' => (float) ($period->payroll_lines_sum_billable_amount ?? 0),
            ])
            ->values();

        return Inertia::render('statement-of-accounts/create', [
            'available_periods' => $availablePeriods,
        ]);
    }

    public function store(StoreAccountReceivableRequest $request): RedirectResponse
    {
        $soa = $this->generateStatementOfAccountAction->execute($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Statement of Account generated.')]);

        return to_route('statement-of-accounts.show', $soa);
    }

    public function show(Request $request, AccountReceivable $accountReceivable): Response
    {
        $this->authorize('view', $accountReceivable);

        $accountReceivable->load([
            'client',
            'status',
            'payrollPeriod.payrollLines.employee.compensationDetail',
            'payrollPeriod.payrollLines.deployment',
            'submitter',
            'approver',
            'rejecter',
        ]);

        return Inertia::render('statement-of-accounts/show', [
            'soa' => $this->soaDetailPayload($accountReceivable),
            'can' => [
                'update_soa' => $request->user()->can('update', $accountReceivable),
                'submit_soa' => $request->user()->can('submit', $accountReceivable),
                'approve_soa' => $request->user()->can('approve', $accountReceivable),
                'reject_soa' => $request->user()->can('reject', $accountReceivable),
            ],
        ]);
    }

    public function edit(AccountReceivable $accountReceivable): Response
    {
        $this->authorize('update', $accountReceivable);

        return Inertia::render('statement-of-accounts/edit', [
            'soa' => [
                'id' => $accountReceivable->id,
                'account_receivable_number' => $accountReceivable->account_receivable_number,
                'client_name' => $accountReceivable->client?->name,
                'account_receivable_date' => $accountReceivable->account_receivable_date?->toDateString(),
                'subtotal' => (float) $accountReceivable->subtotal,
                'tax_amount' => (float) $accountReceivable->tax_amount,
                'total_amount' => (float) $accountReceivable->total_amount,
                'status' => $accountReceivable->status?->code ?? 'draft',
                'rejection_reason' => $accountReceivable->rejection_reason,
            ],
        ]);
    }

    public function update(UpdateAccountReceivableRequest $request, AccountReceivable $accountReceivable): RedirectResponse
    {
        $subtotal = (float) $request->validated('subtotal');
        $taxAmount = (float) $request->validated('tax_amount');
        $totalAmount = round($subtotal + $taxAmount, 2);

        $accountReceivable->update([
            'account_receivable_date' => $request->validated('account_receivable_date'),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'running_balance' => $totalAmount,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Statement of Account updated.')]);

        return to_route('statement-of-accounts.show', $accountReceivable);
    }

    public function submit(SubmitAccountReceivableRequest $request, AccountReceivable $accountReceivable): RedirectResponse
    {
        $this->submitStatementOfAccountAction->execute($accountReceivable, $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Statement of Account submitted to General Manager for approval.')]);

        return to_route('statement-of-accounts.show', $accountReceivable);
    }

    /**
     * @return array<string, mixed>
     */
    protected function soaSummaryPayload(AccountReceivable $soa, User $user): array
    {
        return [
            'id' => $soa->id,
            'account_receivable_number' => $soa->account_receivable_number,
            'client_name' => $soa->client?->name,
            'account_receivable_date' => $soa->account_receivable_date?->toDateString(),
            'due_date' => $soa->due_date?->toDateString(),
            'subtotal' => (float) $soa->subtotal,
            'tax_amount' => (float) $soa->tax_amount,
            'total_amount' => (float) $soa->total_amount,
            'running_balance' => (float) $soa->running_balance,
            'status' => $soa->status?->code ?? 'draft',
            'submitted_by_name' => $soa->submitter?->name,
            'submitted_at' => $soa->submitted_at?->toDateTimeString(),
            'approved_by_name' => $soa->approver?->name,
            'approved_at' => $soa->approved_at?->toDateTimeString(),
            'rejected_by_name' => $soa->rejecter?->name,
            'rejected_at' => $soa->rejected_at?->toDateTimeString(),
            'rejection_reason' => $soa->rejection_reason,
            'can_edit' => $user->can('update', $soa),
            'can_submit' => $user->can('submit', $soa),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function soaDetailPayload(AccountReceivable $soa): array
    {
        return [
            'id' => $soa->id,
            'account_receivable_number' => $soa->account_receivable_number,
            'client' => [
                'id' => $soa->client?->id,
                'name' => $soa->client?->name,
                'billing_address' => $soa->client?->billing_address,
                'contact_person' => $soa->client?->contact_person,
            ],
            'payroll_period' => [
                'id' => $soa->payrollPeriod?->id,
                'start_date' => $soa->payrollPeriod?->start_date?->toDateString(),
                'end_date' => $soa->payrollPeriod?->end_date?->toDateString(),
            ],
            'account_receivable_date' => $soa->account_receivable_date?->toDateString(),
            'due_date' => $soa->due_date?->toDateString(),
            'subtotal' => (float) $soa->subtotal,
            'tax_amount' => (float) $soa->tax_amount,
            'total_amount' => (float) $soa->total_amount,
            'running_balance' => (float) $soa->running_balance,
            'status' => $soa->status?->code ?? 'draft',
            'submitted_by' => $soa->submitter?->name,
            'submitted_at' => $soa->submitted_at?->toDateTimeString(),
            'approved_by' => $soa->approver?->name,
            'approved_at' => $soa->approved_at?->toDateTimeString(),
            'rejected_by' => $soa->rejecter?->name,
            'rejected_at' => $soa->rejected_at?->toDateTimeString(),
            'rejection_reason' => $soa->rejection_reason,
            'lines' => ($soa->payrollPeriod?->payrollLines ?? collect())->map(fn (PayrollLine $line): array => [
                'id' => $line->id,
                'employee_number' => $line->employee?->employee_number,
                'guard_name' => trim(($line->employee?->first_name ?? '').' '.($line->employee?->last_name ?? '')),
                'post_name' => $line->deployment?->post_name ?? 'General Post',
                'regular_hours' => (float) $line->regular_hours,
                'overtime_hours' => (float) $line->overtime_hours,
                'night_diff_hours' => (float) $line->night_diff_hours,
                'billable_amount' => (float) $line->billable_amount,
            ])->values(),
        ];
    }
}
