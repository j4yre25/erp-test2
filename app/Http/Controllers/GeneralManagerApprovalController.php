<?php

namespace App\Http\Controllers;

use App\Actions\Billing\ApproveStatementOfAccountAction;
use App\Actions\Billing\RejectStatementOfAccountAction;
use App\Http\Requests\ApproveAccountReceivableRequest;
use App\Http\Requests\RejectAccountReceivableRequest;
use App\Models\AccountReceivable;
use App\Models\PayrollLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class GeneralManagerApprovalController extends Controller
{
    public function __construct(
        protected ApproveStatementOfAccountAction $approveStatementOfAccountAction,
        protected RejectStatementOfAccountAction $rejectStatementOfAccountAction,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', AccountReceivable::class);

        $pendingSoas = AccountReceivable::query()
            ->with(['client', 'payrollPeriod', 'status', 'submitter'])
            ->submitted()
            ->latest('submitted_at')
            ->get()
            ->map(fn (AccountReceivable $soa): array => $this->soaReviewPayload($soa))
            ->values();

        $approvedSoas = AccountReceivable::query()
            ->with(['client', 'payrollPeriod', 'status', 'approver', 'submitter'])
            ->approved()
            ->latest('approved_at')
            ->get()
            ->map(fn (AccountReceivable $soa): array => $this->soaReviewPayload($soa))
            ->values();

        return Inertia::render('gm/statement-of-accounts/index', [
            'pending_soas' => $pendingSoas,
            'approved_soas' => $approvedSoas,
        ]);
    }

    public function show(AccountReceivable $accountReceivable): Response
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

        return Inertia::render('gm/statement-of-accounts/show', [
            'soa' => $this->soaDetailedReviewPayload($accountReceivable),
            'can' => [
                'approve' => Gate::allows('approve', $accountReceivable),
                'reject' => Gate::allows('reject', $accountReceivable),
            ],
        ]);
    }

    public function approve(ApproveAccountReceivableRequest $request, AccountReceivable $accountReceivable): RedirectResponse
    {
        $this->approveStatementOfAccountAction->execute(
            $accountReceivable,
            $request->user(),
            $request->validated('due_date'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Statement of Account approved and booked as official receivable.')]);

        return to_route('gm.statement-of-accounts.index');
    }

    public function reject(RejectAccountReceivableRequest $request, AccountReceivable $accountReceivable): RedirectResponse
    {
        $this->rejectStatementOfAccountAction->execute(
            $accountReceivable,
            $request->user(),
            $request->validated('rejection_reason'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Statement of Account rejected and returned to AR for revision.')]);

        return to_route('gm.statement-of-accounts.index');
    }

    /**
     * @return array<string, mixed>
     */
    protected function soaReviewPayload(AccountReceivable $soa): array
    {
        return [
            'id' => $soa->id,
            'account_receivable_number' => $soa->account_receivable_number,
            'client_name' => $soa->client?->name,
            'period_range' => "{$soa->payrollPeriod?->start_date?->format('M d, Y')} - {$soa->payrollPeriod?->end_date?->format('M d, Y')}",
            'total_amount' => (float) $soa->total_amount,
            'running_balance' => (float) $soa->running_balance,
            'due_date' => $soa->due_date?->toDateString(),
            'status' => $soa->status?->code ?? 'draft',
            'submitted_by' => $soa->submitter?->name,
            'submitted_at' => $soa->submitted_at?->format('M d, Y H:i'),
            'approved_by' => $soa->approver?->name,
            'approved_at' => $soa->approved_at?->format('M d, Y H:i'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function soaDetailedReviewPayload(AccountReceivable $soa): array
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
            'submitted_at' => $soa->submitted_at?->format('M d, Y H:i'),
            'approved_by' => $soa->approver?->name,
            'approved_at' => $soa->approved_at?->format('M d, Y H:i'),
            'rejected_by' => $soa->rejecter?->name,
            'rejected_at' => $soa->rejected_at?->format('M d, Y H:i'),
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
