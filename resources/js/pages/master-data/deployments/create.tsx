import { Form, Head, Link, usePage } from '@inertiajs/react';
import DeploymentController from '@/actions/App/Http/Controllers/DeploymentController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/deployments';

type ClientOption = {
    id: number;
    name: string;
};

type GuardOption = {
    id: number;
    full_name: string;
};

type PageProps = {
    clients: ClientOption[];
    guards: GuardOption[];
};

const selectClassName =
    'border-input flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] md:text-sm';

export default function DeploymentsCreate() {
    const { clients, guards } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Assign Guard" />

            <div className="max-w-2xl space-y-6 p-4">
                <Heading
                    title="Assign Guard"
                    description="Create a deployment record and flip the guard's status to deployed"
                />

                <Form {...DeploymentController.store.form()} className="space-y-6">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="client_id">Client</Label>
                                    <select id="client_id" name="client_id" required defaultValue="" className={selectClassName}>
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

                                <div className="grid gap-2">
                                    <Label htmlFor="employee_id">Guard</Label>
                                    <select id="employee_id" name="employee_id" required defaultValue="" className={selectClassName}>
                                        <option value="" disabled>
                                            Select an available guard
                                        </option>
                                        {guards.map((guard) => (
                                            <option key={guard.id} value={guard.id}>
                                                {guard.full_name}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.employee_id} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="post_name">Post Name</Label>
                                <Input id="post_name" name="post_name" placeholder="e.g. Main Gate" />
                                <InputError message={errors.post_name} />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="start_date">Start Date</Label>
                                    <Input id="start_date" name="start_date" type="date" required />
                                    <InputError message={errors.start_date} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="billing_rate">Billing Rate</Label>
                                    <Input id="billing_rate" name="billing_rate" type="number" step="0.01" min={0} required placeholder="0.00" />
                                    <InputError message={errors.billing_rate} />
                                </div>
                            </div>

                            <input type="hidden" name="status" value="active" />

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

DeploymentsCreate.layout = {
    breadcrumbs: [
        {
            title: 'Deployments',
            href: index(),
        },
        {
            title: 'Assign Guard',
            href: index(),
        },
    ],
};
