import { Form, Head, Link, usePage } from '@inertiajs/react';
import PayrollPeriodController from '@/actions/App/Http/Controllers/PayrollPeriodController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/payroll-periods';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { DatePicker } from '@/components/ui/date-picker';

type ClientOption = {
    id: number;
    name: string;
};

type PageProps = {
    clients: ClientOption[];
};


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
                                <Select name="client_id" required>
                                    <SelectTrigger id="client_id" className="w-full">
                                        <SelectValue placeholder="Select a client" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {clients.map((client) => (
                                            <SelectItem key={client.id} value={client.id.toString()}>
                                                {client.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.client_id} />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="start_date">Start Date</Label>
                                    <DatePicker id="start_date" name="start_date" required />
                                    <InputError message={errors.start_date} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="end_date">End Date</Label>
                                    <DatePicker id="end_date" name="end_date" required />
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
