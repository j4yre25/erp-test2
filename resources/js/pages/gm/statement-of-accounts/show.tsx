import { useState } from 'react';
import { Form, Head, Link, usePage } from '@inertiajs/react';
import GeneralManagerApprovalController from '@/actions/App/Http/Controllers/GeneralManagerApprovalController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/gm/statement-of-accounts';

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

type SoaDetailedReview = {
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
    soa: SoaDetailedReview;
    can: {
        approve: boolean;
        reject: boolean;
    };
};

function formatCurrency(amount: number): string {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount);
}

function formatDate(dateString: string | null): string {
    if (!dateString) return '—';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    return new Intl.DateTimeFormat('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    }).format(date);
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

export default function GeneralManagerSoaShow() {
    const { soa, can } = usePage<PageProps>().props;
    const [approveDialogOpen, setApproveDialogOpen] = useState(false);
    const [rejectDialogOpen, setRejectDialogOpen] = useState(false);

    // Default due date: 15 days from today
    const defaultDueDate = new Date(Date.now() + 15 * 24 * 60 * 60 * 1000)
        .toISOString()
        .split('T')[0];

    return (
        <>
            <Head title={`GM Review: ${soa.account_receivable_number}`} />

            <div className="space-y-6 p-4">
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <div className="flex items-center gap-2">
                            <Heading
                                title={soa.account_receivable_number}
                                description={`Executive Review for ${soa.client.name} (Cutoff: ${formatDate(soa.payroll_period.start_date)} - ${formatDate(soa.payroll_period.end_date)})`}
                            />
                            <Badge variant={statusBadgeVariant(soa.status)}>
                                {soa.status}
                            </Badge>
                        </div>
                    </div>

                    <div className="flex flex-wrap items-center gap-3">
                        <Button variant="outline" asChild>
                            <Link href={index()}>Back to Queue</Link>
                        </Button>

                        {can.approve && (
                            <Dialog open={approveDialogOpen} onOpenChange={setApproveDialogOpen}>
                                <DialogTrigger asChild>
                                    <Button className="bg-emerald-600 hover:bg-emerald-700">
                                        Approve & Book Receivable
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>Approve Statement of Account</DialogTitle>
                                        <DialogDescription>
                                            Approving this SOA will book <strong>{formatCurrency(soa.total_amount)}</strong> as an official receivable balance and set the payment due date.
                                        </DialogDescription>
                                    </DialogHeader>

                                    <Form
                                        {...GeneralManagerApprovalController.approve.form(soa.id)}
                                        className="space-y-4"
                                        onSuccess={() => setApproveDialogOpen(false)}
                                    >
                                        {({ processing, errors }) => (
                                            <>
                                                <div className="grid gap-2">
                                                    <Label htmlFor="due_date">Payment Due Date</Label>
                                                    <Input
                                                        id="due_date"
                                                        name="due_date"
                                                        type="date"
                                                        required
                                                        defaultValue={defaultDueDate}
                                                    />
                                                    <InputError message={errors.due_date} />
                                                </div>

                                                <DialogFooter>
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        onClick={() => setApproveDialogOpen(false)}
                                                    >
                                                        Cancel
                                                    </Button>
                                                    <Button disabled={processing} className="bg-emerald-600 hover:bg-emerald-700">
                                                        Confirm Approval
                                                    </Button>
                                                </DialogFooter>
                                            </>
                                        )}
                                    </Form>
                                </DialogContent>
                            </Dialog>
                        )}

                        {can.reject && (
                            <Dialog open={rejectDialogOpen} onOpenChange={setRejectDialogOpen}>
                                <DialogTrigger asChild>
                                    <Button variant="destructive">
                                        Reject & Request Revision
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>Reject Statement of Account</DialogTitle>
                                        <DialogDescription>
                                            Please provide detailed reasons for rejection. The SOA will be reverted to draft and returned to AR for corrections.
                                        </DialogDescription>
                                    </DialogHeader>

                                    <Form
                                        {...GeneralManagerApprovalController.reject.form(soa.id)}
                                        className="space-y-4"
                                        onSuccess={() => setRejectDialogOpen(false)}
                                    >
                                        {({ processing, errors }) => (
                                            <>
                                                <div className="grid gap-2">
                                                    <Label htmlFor="rejection_reason">Rejection Reason / Revision Notes</Label>
                                                    <textarea
                                                        id="rejection_reason"
                                                        name="rejection_reason"
                                                        required
                                                        rows={4}
                                                        placeholder="Explain why this SOA was rejected (e.g. rate discrepancies, missing guard hours)..."
                                                        className="border-input flex w-full rounded-md border bg-transparent p-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                                                    />
                                                    <InputError message={errors.rejection_reason} />
                                                </div>

                                                <DialogFooter>
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        onClick={() => setRejectDialogOpen(false)}
                                                    >
                                                        Cancel
                                                    </Button>
                                                    <Button variant="destructive" disabled={processing}>
                                                        Confirm Rejection
                                                    </Button>
                                                </DialogFooter>
                                            </>
                                        )}
                                    </Form>
                                </DialogContent>
                            </Dialog>
                        )}
                    </div>
                </div>

                {soa.status === 'approved' && (
                    <Alert className="border-emerald-500 bg-emerald-50 text-emerald-950 dark:bg-emerald-950/50 dark:text-emerald-200">
                        <AlertTitle>Official Booked Receivable</AlertTitle>
                        <AlertDescription>
                            Approved by {soa.approved_by} on {formatDate(soa.approved_at)}. Due date is set to{' '}
                            <strong>{formatDate(soa.due_date)}.</strong>
                        </AlertDescription>
                    </Alert>
                )}

                {soa.rejection_reason && (
                    <Alert variant="destructive">
                        <AlertTitle>Previous Rejection Reason</AlertTitle>
                        <AlertDescription>{soa.rejection_reason}</AlertDescription>
                    </Alert>
                )}

                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Subtotal
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-xl font-bold">{formatCurrency(soa.subtotal)}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Tax / VAT Amount
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-xl font-bold">{formatCurrency(soa.tax_amount)}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Total SOA Amount
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-xl font-bold text-primary">{formatCurrency(soa.total_amount)}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Receivable Balance
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-xl font-bold">{formatCurrency(soa.running_balance)}</div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 md:grid-cols-3">
                    <div className="space-y-4 md:col-span-2">
                        <h3 className="text-lg font-semibold">Guard Billable Verification</h3>

                        <div className="overflow-x-auto rounded-xl border">
                            <table className="w-full border-collapse text-left text-sm">
                                <thead>
                                    <tr className="border-b bg-muted/50 text-xs uppercase tracking-wide text-muted-foreground">
                                        <th className="px-4 py-2 font-semibold">Guard / Emp #</th>
                                        <th className="px-4 py-2 font-semibold">Post</th>
                                        <th className="px-4 py-2 font-semibold">Hours (Reg / OT / Night)</th>
                                        <th className="px-4 py-2 font-semibold">Billable Subtotal</th>
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
                                                No guard line details found.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="space-y-4">
                        <h3 className="text-lg font-semibold">Audit Trail & Details</h3>

                        <Card>
                            <CardContent className="space-y-3 pt-6 text-sm">
                                <div>
                                    <span className="text-xs font-semibold uppercase text-muted-foreground">Client Name</span>
                                    <p className="font-medium">{soa.client.name}</p>
                                </div>

                                <div>
                                    <span className="text-xs font-semibold uppercase text-muted-foreground">Billing Address</span>
                                    <p className="font-medium">{soa.client.billing_address || '—'}</p>
                                </div>

                                <div>
                                    <span className="text-xs font-semibold uppercase text-muted-foreground">Contact Person</span>
                                    <p className="font-medium">{soa.client.contact_person || '—'}</p>
                                </div>

                                <div className="border-t pt-3 space-y-2">
                                    <span className="text-xs font-semibold uppercase text-muted-foreground">Workflow Timeline</span>

                                    {soa.submitted_by && (
                                        <div className="text-xs bg-blue-50 text-blue-900 rounded p-2 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-200 dark:border-blue-800">
                                            <div className="font-medium">Submitted by {soa.submitted_by}</div>
                                            <div className="text-muted-foreground">{formatDate(soa.submitted_at)}</div>
                                        </div>
                                    )}

                                    {soa.approved_by && (
                                        <div className="text-xs bg-emerald-50 text-emerald-900 rounded p-2 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-200 dark:border-emerald-800">
                                            <div className="font-medium">Approved by {soa.approved_by}</div>
                                            <div className="text-muted-foreground">{formatDate(soa.approved_at)}</div>
                                        </div>
                                    )}

                                    {soa.rejected_by && (
                                        <div className="text-xs bg-rose-50 text-rose-900 rounded p-2 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-200 dark:border-rose-800">
                                            <div className="font-medium">Rejected by {soa.rejected_by}</div>
                                            <div className="text-muted-foreground">{formatDate(soa.rejected_at)}</div>
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

GeneralManagerSoaShow.layout = {
    breadcrumbs: [
        {
            title: 'Management Reviews',
            href: index(),
        },
        {
            title: 'SOA Review',
            href: index(),
        },
    ],
};
