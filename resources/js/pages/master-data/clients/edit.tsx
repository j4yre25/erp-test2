import { Form, Head, Link, usePage } from '@inertiajs/react';
import ClientController from '@/actions/App/Http/Controllers/ClientController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/clients';

type Client = {
    id: number;
    name: string;
    billing_address: string | null;
    company_address: string | null;
    contact_person: string | null;
    payroll_period: string | null;
    cutoff_type: string | null;
    first_cutoff_day: number | null;
    second_cutoff_day: number | null;
    payroll_frequency: string | null;
};

type PageProps = {
    client: Client;
};

export default function ClientsEdit() {
    const { client } = usePage<PageProps>().props;

    return (
        <>
            <Head title={`Edit ${client.name}`} />

            <div className="max-w-2xl space-y-6 p-4">
                <Heading
                    title="Edit Client"
                    description="Update this client's company info and payroll cutoff schedule"
                />

                <Form {...ClientController.update.form(client.id)} className="space-y-6">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Client Name</Label>
                                <Input id="name" name="name" required defaultValue={client.name} />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="contact_person">Contact Person</Label>
                                <Input id="contact_person" name="contact_person" defaultValue={client.contact_person ?? ''} />
                                <InputError message={errors.contact_person} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="company_address">Company Address</Label>
                                <Input id="company_address" name="company_address" defaultValue={client.company_address ?? ''} />
                                <InputError message={errors.company_address} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="billing_address">Billing Address</Label>
                                <Input id="billing_address" name="billing_address" defaultValue={client.billing_address ?? ''} />
                                <InputError message={errors.billing_address} />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="payroll_period">Payroll Period</Label>
                                    <Input id="payroll_period" name="payroll_period" defaultValue={client.payroll_period ?? ''} />
                                    <InputError message={errors.payroll_period} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="payroll_frequency">Payroll Frequency</Label>
                                    <Input id="payroll_frequency" name="payroll_frequency" defaultValue={client.payroll_frequency ?? ''} />
                                    <InputError message={errors.payroll_frequency} />
                                </div>
                            </div>

                            <div className="grid grid-cols-3 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="cutoff_type">Cutoff Type</Label>
                                    <Input id="cutoff_type" name="cutoff_type" defaultValue={client.cutoff_type ?? ''} />
                                    <InputError message={errors.cutoff_type} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="first_cutoff_day">First Cutoff Day</Label>
                                    <Input
                                        id="first_cutoff_day"
                                        name="first_cutoff_day"
                                        type="number"
                                        min={1}
                                        max={31}
                                        defaultValue={client.first_cutoff_day ?? ''}
                                    />
                                    <InputError message={errors.first_cutoff_day} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="second_cutoff_day">Second Cutoff Day</Label>
                                    <Input
                                        id="second_cutoff_day"
                                        name="second_cutoff_day"
                                        type="number"
                                        min={1}
                                        max={31}
                                        defaultValue={client.second_cutoff_day ?? ''}
                                    />
                                    <InputError message={errors.second_cutoff_day} />
                                </div>
                            </div>

                            <div className="flex items-center gap-4">
                                <Button disabled={processing}>Save</Button>

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

ClientsEdit.layout = {
    breadcrumbs: [
        {
            title: 'Clients',
            href: index(),
        },
        {
            title: 'Edit Client',
            href: index(),
        },
    ],
};
