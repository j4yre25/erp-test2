import { Form, Head, Link } from '@inertiajs/react';
import ClientController from '@/actions/App/Http/Controllers/ClientController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/clients';

export default function ClientsCreate() {
    return (
        <>
            <Head title="Add Client" />

            <div className="max-w-2xl space-y-6 p-4">
                <Heading
                    title="Add Client"
                    description="Register a new client company and its payroll cutoff schedule"
                />

                <Form {...ClientController.store.form()} className="space-y-6">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Client Name</Label>
                                <Input id="name" name="name" required placeholder="Client name" />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="contact_person">Contact Person</Label>
                                <Input id="contact_person" name="contact_person" placeholder="Contact person" />
                                <InputError message={errors.contact_person} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="company_address">Company Address</Label>
                                <Input id="company_address" name="company_address" placeholder="Company address" />
                                <InputError message={errors.company_address} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="billing_address">Billing Address</Label>
                                <Input id="billing_address" name="billing_address" placeholder="Billing address" />
                                <InputError message={errors.billing_address} />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="payroll_period">Payroll Period</Label>
                                    <Input id="payroll_period" name="payroll_period" placeholder="e.g. Semi-monthly" />
                                    <InputError message={errors.payroll_period} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="payroll_frequency">Payroll Frequency</Label>
                                    <Input id="payroll_frequency" name="payroll_frequency" placeholder="e.g. Twice a month" />
                                    <InputError message={errors.payroll_frequency} />
                                </div>
                            </div>

                            <div className="grid grid-cols-3 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="cutoff_type">Cutoff Type</Label>
                                    <Input id="cutoff_type" name="cutoff_type" placeholder="e.g. Fixed" />
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
                                        placeholder="15"
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
                                        placeholder="30"
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

ClientsCreate.layout = {
    breadcrumbs: [
        {
            title: 'Clients',
            href: index(),
        },
        {
            title: 'Add Client',
            href: index(),
        },
    ],
};
