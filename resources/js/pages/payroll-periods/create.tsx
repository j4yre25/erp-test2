import { Form, Head, Link, usePage } from '@inertiajs/react';
import PayrollPeriodController from '@/actions/App/Http/Controllers/PayrollPeriodController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/payroll-periods';

type ClientOption = {
    id: number;
    name: string;
};

type PageProps = {
    clients: ClientOption[];
};

const selectClassName =
    'border-input flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] md:text-sm';

export default function PayrollPeriodsCreate() {
    const { clients } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Open Payroll Period" />

            <div className="max-w-2xl space-y-6 p-4">
                <Heading
                    title="Open Payroll Period"
                    description="Open a new payroll cutoff period for a client"
                />

                <Form {...PayrollPeriodController.store.form()} className="space-y-6">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="client_id">Client</Label>
                                <select
                                    id="client_id"
                                    name="client_id"
                                    required
                                    defaultValue=""
                                    className={selectClassName}
                                >
                                    <option value="" disabled>
                                        Select a client
                                    </option>
                                    {clients.map((client) => (
                                        <option key={client.id} value={client.id}>
                                            {client.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.client_id} />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="start_date">Start Date</Label>
                                    <Input id="start_date" name="start_date" type="date" required />
                                    <InputError message={errors.start_date} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="end_date">End Date</Label>
                                    <Input id="end_date" name="end_date" type="date" required />
                                    <InputError message={errors.end_date} />
                                </div>
                            </div>

                            <div className="flex items-center gap-4">
                                <Button disabled={processing}>Open Period</Button>

                                <Button variant="secondary" asChild>
                                    <Link href={index()}>Cancel</Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

PayrollPeriodsCreate.layout = {
    breadcrumbs: [
        {
            title: 'Payroll Periods',
            href: index(),
        },
        {
            title: 'Open Payroll Period',
            href: index(),
        },
    ],
};
