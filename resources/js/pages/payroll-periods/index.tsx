import { Head, Link, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { create, index, show } from '@/routes/payroll-periods';

type PayrollPeriodSummary = {
    id: number;
    client_id: number;
    client_name: string;
    start_date: string;
    end_date: string;
    status: 'open' | 'closed';
    lines_count: number;
    total_gross_pay: number;
    total_billable_amount: number;
    can_close: boolean;
};

type PageProps = {
    periods: PayrollPeriodSummary[];
    can: {
        create_period: boolean;
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

export default function PayrollPeriodsIndex() {
    const { periods, can } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Payroll Periods" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Payroll Periods"
                        description="Manage client payroll cutoffs, compute guard pay lines, and close periods"
                    />

                    {can.create_period && (
                        <Button asChild>
                            <Link href={create()}>Open New Period</Link>
                        </Button>
                    )}
                </div>

                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full border-collapse text-left text-sm">
                        <thead>
                            <tr className="border-b bg-muted/50 text-xs uppercase tracking-wide text-muted-foreground">
                                <th className="px-4 py-2 font-semibold">Client</th>
                                <th className="px-4 py-2 font-semibold">Cutoff Period</th>
                                <th className="px-4 py-2 font-semibold">Status</th>
                                <th className="px-4 py-2 font-semibold">Guards</th>
                                <th className="px-4 py-2 font-semibold">Total Gross Pay</th>
                                <th className="px-4 py-2 font-semibold">Total Billable</th>
                                <th className="px-4 py-2 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {periods.map((period) => (
                                <tr key={period.id} className="border-b last:border-b-0">
                                    <td className="px-4 py-2 font-medium">{period.client_name}</td>
                                    <td className="px-4 py-2">
                                        {formatDate(period.start_date)} to {formatDate(period.end_date)}
                                    </td>
                                    <td className="px-4 py-2">
                                        <Badge variant={period.status === 'open' ? 'default' : 'secondary'}>
                                            {period.status}
                                        </Badge>
                                    </td>
                                    <td className="px-4 py-2">{period.lines_count}</td>
                                    <td className="px-4 py-2 font-medium">
                                        {formatCurrency(period.total_gross_pay)}
                                    </td>
                                    <td className="px-4 py-2 font-medium text-primary">
                                        {formatCurrency(period.total_billable_amount)}
                                    </td>
                                    <td className="px-4 py-2">
                                        <Button size="sm" variant="outline" asChild>
                                            <Link href={show(period.id)}>View Breakdown</Link>
                                        </Button>
                                    </td>
                                </tr>
                            ))}

                            {periods.length === 0 && (
                                <tr>
                                    <td colSpan={7} className="px-4 py-6 text-center text-muted-foreground">
                                        No payroll periods found.
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

PayrollPeriodsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Payroll Periods',
            href: index(),
        },
    ],
};
