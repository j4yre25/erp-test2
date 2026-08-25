import { Form, Head, Link, usePage } from '@inertiajs/react';
import AccountReceivableController from '@/actions/App/Http/Controllers/AccountReceivableController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/statement-of-accounts';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { DatePicker } from '@/components/ui/date-picker';

type AvailablePeriod = {
    id: number;
    client_name: string;
    start_date: string;
    end_date: string;
    total_billable: number;
};

type PageProps = {
    available_periods: AvailablePeriod[];
};


function formatCurrency(amount: number): string {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount);
}

export default function StatementOfAccountsCreate() {
    const { available_periods } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Generate Statement of Account" />

            <div className="max-w-2xl space-y-6 p-4">
                <Heading
                    title="Generate Statement of Account"
                    description="Create a draft billing statement from a closed client payroll period"
                />

                {available_periods.length === 0 ? (
                    <div className="rounded-xl border border-dashed p-8 text-center space-y-3">
                        <p className="text-muted-foreground">
                            No closed payroll periods available for billing generation.
                        </p>
                        <Button variant="secondary" asChild>
                            <Link href={index()}>Back to SOAs</Link>
                        </Button>
                    </div>
                ) : (
                    <Form {...AccountReceivableController.store.form()} className="space-y-6">
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="payroll_period_id">Closed Payroll Period</Label>
                                    <Select name="payroll_period_id" required>
                                        <SelectTrigger id="payroll_period_id" className="w-full">
                                            <SelectValue placeholder="Select a closed payroll period" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {available_periods.map((period) => (
                                                <SelectItem key={period.id} value={period.id.toString()}>
                                                    {period.client_name} ({period.start_date} to {period.end_date}) — Billable: {formatCurrency(period.total_billable)}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.payroll_period_id} />
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="account_receivable_date">Statement Date</Label>
                                        <DatePicker
                                            id="account_receivable_date"
                                            name="account_receivable_date"
                                            value={new Date().toISOString().split('T')[0]}
                                            required
                                        />
                                        <InputError message={errors.account_receivable_date} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="tax_amount">VAT / Tax Amount</Label>
                                        <Input
                                            id="tax_amount"
                                            name="tax_amount"
                                            type="text"
                                            inputMode="decimal"
                                            defaultValue="0.00"
                                        />
                                        <InputError message={errors.tax_amount} />
                                    </div>
                                </div>

                                <div className="flex items-center gap-4">
                                    <Button disabled={processing}>Generate Draft SOA</Button>

                                    <Button variant="secondary" asChild>
                                        <Link href={index()}>Cancel</Link>
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                )}
            </div>
        </>
    );
}

StatementOfAccountsCreate.layout = {
    breadcrumbs: [
        {
            title: 'Statements of Account',
            href: index(),
        },
        {
            title: 'Generate SOA',
            href: index(),
        },
    ],
};
