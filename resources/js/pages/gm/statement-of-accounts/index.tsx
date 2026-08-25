import { Head, Link, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { index, show } from '@/routes/gm/statement-of-accounts';

type SoaReviewItem = {
    id: number;
    account_receivable_number: string;
    client_name: string;
    period_range: string;
    total_amount: number;
    running_balance: number;
    due_date: string | null;
    status: string;
    submitted_by: string | null;
    submitted_at: string | null;
    approved_by: string | null;
    approved_at: string | null;
};

type PageProps = {
    pending_soas: SoaReviewItem[];
    approved_soas: SoaReviewItem[];
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

export default function GeneralManagerSoaIndex() {
    const { pending_soas, approved_soas } = usePage<PageProps>().props;

    return (
        <>
            <Head title="GM SOA Approvals & Archive" />

            <div className="space-y-8 p-4">
                <Heading
                    title="GM SOA Reviews & Archive"
                    description="Review submitted Statements of Account, approve to book official receivables, or reject with revision instructions"
                />

                <div className="space-y-4">
                    <div className="flex items-center justify-between">
                        <div>
                            <h3 className="text-lg font-semibold flex items-center gap-2">
                                Pending Executive Review
                                <Badge variant={pending_soas.length > 0 ? 'default' : 'outline'}>
                                    {pending_soas.length} awaiting
                                </Badge>
                            </h3>
                            <p className="text-sm text-muted-foreground">
                                Statements of Account submitted by the AR department requiring management sign-off
                            </p>
                        </div>
                    </div>

                    <div className="overflow-x-auto rounded-xl border bg-card">
                        <table className="w-full border-collapse text-left text-sm">
                            <thead>
                                <tr className="border-b bg-muted/50 text-xs uppercase tracking-wide text-muted-foreground">
                                    <th className="px-4 py-2 font-semibold">SOA Number</th>
                                    <th className="px-4 py-2 font-semibold">Client</th>
                                    <th className="px-4 py-2 font-semibold">Period</th>
                                    <th className="px-4 py-2 font-semibold">Submitted By</th>
                                    <th className="px-4 py-2 font-semibold">Submitted At</th>
                                    <th className="px-4 py-2 font-semibold">Total Amount</th>
                                    <th className="px-4 py-2 font-semibold">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {pending_soas.map((soa) => (
                                    <tr key={soa.id} className="border-b last:border-b-0">
                                        <td className="px-4 py-2 font-semibold text-primary">{soa.account_receivable_number}</td>
                                        <td className="px-4 py-2">{soa.client_name}</td>
                                        <td className="px-4 py-2 text-xs">{soa.period_range}</td>
                                        <td className="px-4 py-2">{soa.submitted_by}</td>
                                        <td className="px-4 py-2 text-xs text-muted-foreground">{formatDate(soa.submitted_at)}</td>
                                        <td className="px-4 py-2 font-bold text-base">{formatCurrency(soa.total_amount)}</td>
                                        <td className="px-4 py-2">
                                            <Button size="sm" asChild>
                                                <Link href={show(soa.id)}>Review & Decide</Link>
                                            </Button>
                                        </td>
                                    </tr>
                                ))}

                                {pending_soas.length === 0 && (
                                    <tr>
                                        <td colSpan={7} className="px-4 py-8 text-center text-muted-foreground">
                                            No pending SOAs awaiting review. All caught up!
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div className="space-y-4">
                    <div>
                        <h3 className="text-lg font-semibold flex items-center gap-2">
                            Official Approved SOA Archive
                            <Badge variant="secondary">{approved_soas.length} booked</Badge>
                        </h3>
                        <p className="text-sm text-muted-foreground">
                            Archived Statements of Account booked as official receivables with due dates
                        </p>
                    </div>

                    <div className="overflow-x-auto rounded-xl border bg-card">
                        <table className="w-full border-collapse text-left text-sm">
                            <thead>
                                <tr className="border-b bg-muted/50 text-xs uppercase tracking-wide text-muted-foreground">
                                    <th className="px-4 py-2 font-semibold">SOA Number</th>
                                    <th className="px-4 py-2 font-semibold">Client</th>
                                    <th className="px-4 py-2 font-semibold">Due Date</th>
                                    <th className="px-4 py-2 font-semibold">Approved By</th>
                                    <th className="px-4 py-2 font-semibold">Approved At</th>
                                    <th className="px-4 py-2 font-semibold">Booked Amount</th>
                                    <th className="px-4 py-2 font-semibold">Balance</th>
                                    <th className="px-4 py-2 font-semibold">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {approved_soas.map((soa) => (
                                    <tr key={soa.id} className="border-b last:border-b-0">
                                        <td className="px-4 py-2 font-medium">{soa.account_receivable_number}</td>
                                        <td className="px-4 py-2">{soa.client_name}</td>
                                        <td className="px-4 py-2">{formatDate(soa.due_date)}</td>
                                        <td className="px-4 py-2">{soa.approved_by}</td>
                                        <td className="px-4 py-2 text-xs text-muted-foreground">{formatDate(soa.approved_at)}</td>
                                        <td className="px-4 py-2 font-semibold">{formatCurrency(soa.total_amount)}</td>
                                        <td className="px-4 py-2 font-semibold text-primary">{formatCurrency(soa.running_balance)}</td>
                                        <td className="px-4 py-2">
                                            <Button size="sm" variant="outline" asChild>
                                                <Link href={show(soa.id)}>View Archive</Link>
                                            </Button>
                                        </td>
                                    </tr>
                                ))}

                                {approved_soas.length === 0 && (
                                    <tr>
                                        <td colSpan={8} className="px-4 py-6 text-center text-muted-foreground">
                                            No approved SOAs in archive yet.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </>
    );
}

GeneralManagerSoaIndex.layout = {
    breadcrumbs: [
        {
            title: 'Management Reviews',
            href: index(),
        },
    ],
};
