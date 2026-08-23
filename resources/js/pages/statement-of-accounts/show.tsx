import { Form, Head, Link, usePage } from '@inertiajs/react';
import AccountReceivableController from '@/actions/App/Http/Controllers/AccountReceivableController';
import Heading from '@/components/heading';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { edit, index } from '@/routes/statement-of-accounts';

type SoaLine = {
    id: number;
    employee_number: string;
    guard_name: string;
    post_name: string;
    regular_hours: number;
    overtime_hours: number;
    night_diff_hours: number;
    billable_amount: number;
};

type SoaDetail = {
    id: number;
    account_receivable_number: string;
    client: {
        id: number;
        name: string;
        billing_address: string | null;
        contact_person: string | null;
    };
    payroll_period: {
        id: number;
        start_date: string;
        end_date: string;
    };
    account_receivable_date: string;
    due_date: string | null;
    subtotal: number;
    tax_amount: number;
    total_amount: number;
    running_balance: number;
    status: 'draft' | 'submitted' | 'approved' | 'rejected';
    submitted_by: string | null;
    submitted_at: string | null;
    approved_by: string | null;
    approved_at: string | null;
    rejected_by: string | null;
    rejected_at: string | null;
    rejection_reason: string | null;
    lines: SoaLine[];
};

type PageProps = {
    soa: SoaDetail;
    can: {
        update_soa: boolean;
        submit_soa: boolean;
        approve_soa: boolean;
        reject_soa: boolean;
    };
};

function formatCurrency(amount: number): string {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount);
}

function statusBadgeVariant(status: string): 'default' | 'secondary' | 'outline' | 'destructive' {
    switch (status) {
        case 'approved':
            return 'default';
        case 'submitted':
            return 'secondary';
        case 'rejected':
            return 'destructive';
        default:
            return 'outline';
    }
}

export default function StatementOfAccountsShow() {
    const { soa, can } = usePage<PageProps>().props;

    return (
        <>
            <Head title={`SOA: ${soa.account_receivable_number}`} />

            <div className="space-y-6 p-4">
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <div className="flex items-center gap-2">
                            <Heading
                                title={soa.account_receivable_number}
                                description={`Client: ${soa.client.name} | Period: ${soa.payroll_period.start_date} to ${soa.payroll_period.end_date}`}
                            />
                            <Badge variant={statusBadgeVariant(soa.status)}>
                                {soa.status}
                            </Badge>
                        </div>
                    </div>

                    <div className="flex flex-wrap items-center gap-3">
                        <Button variant="outline" asChild>
                            <Link href={index()}>Back to List</Link>
                        </Button>

                        {can.update_soa && (
                            <Button variant="secondary" asChild>
                                <Link href={edit(soa.id)}>Edit Draft</Link>
                            </Button>
                        )}

                        {can.submit_soa && (
                            <Form {...AccountReceivableController.submit.form(soa.id)}>
                                {({ processing }) => (
                                    <Button disabled={processing} className="bg-blue-600 hover:bg-blue-700">
                                        Submit to GM for Approval
                                    </Button>
                                )}
                            </Form>
                        )}
                    </div>
                </div>

                {soa.rejection_reason && (
                    <Alert variant="destructive">
                        <AlertTitle>SOA Returned / Rejected by General Manager</AlertTitle>
                        <AlertDescription className="space-y-1">
                            <p className="font-medium">Reason: {soa.rejection_reason}</p>
                            <p className="text-xs">
                                Evaluated by {soa.rejected_by} on {soa.rejected_at}. You may edit and resubmit.
                            </p>
                        </AlertDescription>
                    </Alert>
                )}

                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Subtotal (Billable)
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-xl font-bold">{formatCurrency(soa.subtotal)}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Tax / VAT
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-xl font-bold">{formatCurrency(soa.tax_amount)}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Total Invoiced Amount
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-xl font-bold text-primary">{formatCurrency(soa.total_amount)}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Outstanding Balance
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-xl font-bold">{formatCurrency(soa.running_balance)}</div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 md:grid-cols-3">
                    <div className="space-y-4 md:col-span-2">
                        <h3 className="text-lg font-semibold">Billable Guard Services</h3>

                        <div className="overflow-x-auto rounded-xl border">
                            <table className="w-full border-collapse text-left text-sm">
                                <thead>
                                    <tr className="border-b bg-muted/50 text-xs uppercase tracking-wide text-muted-foreground">
                                        <th className="px-4 py-2 font-semibold">Guard / Emp #</th>
                                        <th className="px-4 py-2 font-semibold">Post</th>
                                        <th className="px-4 py-2 font-semibold">Hours (Reg / OT / Night)</th>
                                        <th className="px-4 py-2 font-semibold">Billable Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {soa.lines.map((line) => (
                                        <tr key={line.id} className="border-b last:border-b-0">
                                            <td className="px-4 py-2 font-medium">
                                                <div>{line.guard_name}</div>
                                                <div className="text-xs text-muted-foreground">{line.employee_number}</div>
                                            </td>
                                            <td className="px-4 py-2">{line.post_name}</td>
                                            <td className="px-4 py-2 text-xs">
                                                Reg: {line.regular_hours} | OT: {line.overtime_hours} | Night: {line.night_diff_hours}
                                            </td>
                                            <td className="px-4 py-2 font-semibold text-primary">
                                                {formatCurrency(line.billable_amount)}
                                            </td>
                                        </tr>
                                    ))}

                                    {soa.lines.length === 0 && (
                                        <tr>
                                            <td colSpan={4} className="px-4 py-6 text-center text-muted-foreground">
                                                No guard lines recorded for this billing cycle.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="space-y-4">
                        <h3 className="text-lg font-semibold">Billing Details & Audit Trail</h3>

                        <Card>
                            <CardContent className="space-y-3 pt-6 text-sm">
                                <div>
                                    <span className="text-xs font-semibold uppercase text-muted-foreground">Client Billing Address</span>
                                    <p className="font-medium">{soa.client.billing_address || '—'}</p>
                                </div>

                                <div>
                                    <span className="text-xs font-semibold uppercase text-muted-foreground">Contact Person</span>
                                    <p className="font-medium">{soa.client.contact_person || '—'}</p>
                                </div>

                                <div className="border-t pt-3">
                                    <span className="text-xs font-semibold uppercase text-muted-foreground">Statement Date</span>
                                    <p className="font-medium">{soa.account_receivable_date}</p>
                                </div>

                                <div>
                                    <span className="text-xs font-semibold uppercase text-muted-foreground">Payment Due Date</span>
                                    <p className="font-medium">{soa.due_date ?? 'Pending GM approval'}</p>
                                </div>

                                <div className="border-t pt-3 space-y-2">
                                    <span className="text-xs font-semibold uppercase text-muted-foreground">Audit Log</span>

                                    {soa.submitted_by && (
                                        <div className="text-xs bg-muted/40 rounded p-2">
                                            <div className="font-medium">Submitted by {soa.submitted_by}</div>
                                            <div className="text-muted-foreground">{soa.submitted_at}</div>
                                        </div>
                                    )}

                                    {soa.approved_by && (
                                        <div className="text-xs bg-emerald-50 text-emerald-900 rounded p-2 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-200 dark:border-emerald-800">
                                            <div className="font-medium">Approved by {soa.approved_by}</div>
                                            <div className="text-muted-foreground">{soa.approved_at}</div>
                                        </div>
                                    )}

                                    {soa.rejected_by && (
                                        <div className="text-xs bg-rose-50 text-rose-900 rounded p-2 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-200 dark:border-rose-800">
                                            <div className="font-medium">Rejected by {soa.rejected_by}</div>
                                            <div className="text-muted-foreground">{soa.rejected_at}</div>
                                        </div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}

StatementOfAccountsShow.layout = {
    breadcrumbs: [
        {
            title: 'Statements of Account',
            href: index(),
        },
        {
            title: 'SOA Details',
            href: index(),
        },
    ],
};
