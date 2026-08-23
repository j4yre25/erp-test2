import { Head, Link, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { create, edit, index, show } from '@/routes/statement-of-accounts';

type SoaSummary = {
    id: number;
    account_receivable_number: string;
    client_name: string;
    account_receivable_date: string;
    due_date: string | null;
    subtotal: number;
    tax_amount: number;
    total_amount: number;
    running_balance: number;
    status: 'draft' | 'submitted' | 'approved' | 'rejected';
    submitted_by_name: string | null;
    submitted_at: string | null;
    approved_by_name: string | null;
    approved_at: string | null;
    rejected_by_name: string | null;
    rejected_at: string | null;
    rejection_reason: string | null;
    can_edit: boolean;
    can_submit: boolean;
};

type PageProps = {
    soas: SoaSummary[];
    can: {
        create_soa: boolean;
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

export default function StatementOfAccountsIndex() {
    const { soas, can } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Statements of Account" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Statements of Account (SOA)"
                        description="Generate billing statements from closed payroll periods and track receivables"
                    />

                    {can.create_soa && (
                        <Button asChild>
                            <Link href={create()}>Generate SOA</Link>
                        </Button>
                    )}
                </div>

                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full border-collapse text-left text-sm">
                        <thead>
                            <tr className="border-b bg-muted/50 text-xs uppercase tracking-wide text-muted-foreground">
                                <th className="px-4 py-2 font-semibold">SOA Number</th>
                                <th className="px-4 py-2 font-semibold">Client</th>
                                <th className="px-4 py-2 font-semibold">SOA Date</th>
                                <th className="px-4 py-2 font-semibold">Due Date</th>
                                <th className="px-4 py-2 font-semibold">Status</th>
                                <th className="px-4 py-2 font-semibold">Total Amount</th>
                                <th className="px-4 py-2 font-semibold">Balance</th>
                                <th className="px-4 py-2 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {soas.map((soa) => (
                                <tr key={soa.id} className="border-b last:border-b-0">
                                    <td className="px-4 py-2 font-medium">
                                        <Link href={show(soa.id)} className="hover:underline">
                                            {soa.account_receivable_number}
                                        </Link>
                                    </td>
                                    <td className="px-4 py-2">{soa.client_name}</td>
                                    <td className="px-4 py-2">{soa.account_receivable_date}</td>
                                    <td className="px-4 py-2">{soa.due_date ?? '—'}</td>
                                    <td className="px-4 py-2">
                                        <Badge variant={statusBadgeVariant(soa.status)}>
                                            {soa.status}
                                        </Badge>
                                    </td>
                                    <td className="px-4 py-2 font-semibold">
                                        {formatCurrency(soa.total_amount)}
                                    </td>
                                    <td className="px-4 py-2 font-semibold text-primary">
                                        {formatCurrency(soa.running_balance)}
                                    </td>
                                    <td className="px-4 py-2">
                                        <div className="flex items-center gap-2">
                                            <Button size="sm" variant="outline" asChild>
                                                <Link href={show(soa.id)}>View</Link>
                                            </Button>

                                            {soa.can_edit && (
                                                <Button size="sm" variant="secondary" asChild>
                                                    <Link href={edit(soa.id)}>Edit</Link>
                                                </Button>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}

                            {soas.length === 0 && (
                                <tr>
                                    <td colSpan={8} className="px-4 py-6 text-center text-muted-foreground">
                                        No Statement of Accounts generated yet.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

StatementOfAccountsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Statements of Account',
            href: index(),
        },
    ],
};
