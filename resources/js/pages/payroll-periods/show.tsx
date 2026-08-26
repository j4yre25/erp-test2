import { Form, Head, Link, usePage } from '@inertiajs/react';
import PayrollPeriodController from '@/actions/App/Http/Controllers/PayrollPeriodController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { index } from '@/routes/payroll-periods';

type PayrollLineDetail = {
    id: number;
    employee_number: string;
    guard_name: string;
    post_name: string;
    regular_hours: number;
    overtime_hours: number;
    night_diff_hours: number;
    base_pay: number;
    night_diff_pay: number;
    employer_sss: number;
    employer_philhealth: number;
    employer_pagibig: number;
    gross_amount: number;
    billable_amount: number;
    sss_number: string | null;
    philhealth_number: string | null;
    pagibig_number: string | null;
};

type PayrollPeriodDetail = {
    id: number;
    client_id: number;
    client_name: string;
    start_date: string;
    end_date: string;
    status: 'open' | 'closed';
    lines: PayrollLineDetail[];
};

type PageProps = {
    period: PayrollPeriodDetail;
    can: {
        close_period: boolean;
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

export default function PayrollPeriodsShow() {
    const { period, can } = usePage<PageProps>().props;

    const totalGross = period.lines.reduce((sum, line) => sum + line.gross_amount, 0);
    const totalBillable = period.lines.reduce((sum, line) => sum + line.billable_amount, 0);
    const totalEmployerContributions = period.lines.reduce(
        (sum, line) => sum + line.employer_sss + line.employer_philhealth + line.employer_pagibig,
        0,
    );

    return (
        <>
            <Head title={`Payroll Period: ${period.client_name}`} />

            <div className="space-y-6 p-4">
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <div className="flex items-center gap-2">
                            <Heading
                                title={period.client_name}
                                description={`Cutoff Period: ${formatDate(period.start_date)} to ${formatDate(period.end_date)}`}
                            />
                            <Badge variant={period.status === 'open' ? 'default' : 'secondary'}>
                                {period.status}
                            </Badge>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <Button variant="outline" asChild>
                            <Link href={index()}>Back to Periods</Link>
                        </Button>

                        {can.close_period && (
                            <Form {...PayrollPeriodController.close.form(period.id)}>
                                {({ processing }) => (
                                    <Button disabled={processing} className="bg-emerald-600 hover:bg-emerald-700">
                                        Close Period & Compute Pay Lines
                                    </Button>
                                )}
                            </Form>
                        )}
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Total Guard Gross Pay
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(totalGross)}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Employer Contributions (SSS/PH/HDMF)
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-amber-600">
                                {formatCurrency(totalEmployerContributions)}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Total Billable Amount
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-primary">
                                {formatCurrency(totalBillable)}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="space-y-4">
                    <h3 className="text-lg font-semibold">Guard Pay Lines Computation</h3>

                    <div className="overflow-x-auto rounded-xl border">
                        <table className="w-full border-collapse text-left text-sm">
                            <thead>
                                <tr className="border-b bg-muted/50 text-xs uppercase tracking-wide text-muted-foreground">
                                    <th className="px-4 py-2 font-semibold">Guard / Emp #</th>
                                    <th className="px-4 py-2 font-semibold">Post</th>
                                    <th className="px-4 py-2 font-semibold">Reg / OT / Night Hrs</th>
                                    <th className="px-4 py-2 font-semibold">Base Pay</th>
                                    <th className="px-4 py-2 font-semibold">Night Diff Pay</th>
                                    <th className="px-4 py-2 font-semibold">Employer SSS</th>
                                    <th className="px-4 py-2 font-semibold">Employer PhilHealth</th>
                                    <th className="px-4 py-2 font-semibold">Employer Pag-IBIG</th>
                                    <th className="px-4 py-2 font-semibold">Gross Pay</th>
                                    <th className="px-4 py-2 font-semibold">Billable Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                {period.lines.map((line) => (
                                    <tr key={line.id} className="border-b last:border-b-0">
                                        <td className="px-4 py-2">
                                            <div className="font-medium">{line.guard_name}</div>
                                            <div className="text-xs text-muted-foreground">{line.employee_number}</div>
                                        </td>
                                        <td className="px-4 py-2">{line.post_name}</td>
                                        <td className="px-4 py-2 text-xs">
                                            <div>Reg: {line.regular_hours} hrs</div>
                                            <div>OT: {line.overtime_hours} hrs</div>
                                            <div>Night: {line.night_diff_hours} hrs</div>
                                        </td>
                                        <td className="px-4 py-2">{formatCurrency(line.base_pay)}</td>
                                        <td className="px-4 py-2">{formatCurrency(line.night_diff_pay)}</td>
                                        <td className="px-4 py-2 text-xs">{formatCurrency(line.employer_sss)}</td>
                                        <td className="px-4 py-2 text-xs">{formatCurrency(line.employer_philhealth)}</td>
                                        <td className="px-4 py-2 text-xs">{formatCurrency(line.employer_pagibig)}</td>
                                        <td className="px-4 py-2 font-semibold">{formatCurrency(line.gross_amount)}</td>
                                        <td className="px-4 py-2 font-semibold text-primary">{formatCurrency(line.billable_amount)}</td>
                                    </tr>
                                ))}

                                {period.lines.length === 0 && (
                                    <tr>
                                        <td colSpan={10} className="px-4 py-8 text-center text-muted-foreground">
                                            {period.status === 'open'
                                                ? 'Pay lines will be computed when you close the payroll period.'
                                                : 'No active guard deployments were found for this period.'}
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

PayrollPeriodsShow.layout = {
    breadcrumbs: [
        {
            title: 'Payroll Periods',
            href: index(),
        },
        {
            title: 'Period Details',
            href: index(),
        },
    ],
};
