import { Form, Head, Link, usePage } from '@inertiajs/react';
import DeploymentController from '@/actions/App/Http/Controllers/DeploymentController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/deployments';

type Deployment = {
    id: number;
    client_id: number;
    client_name: string | null;
    employee_id: number;
    guard_name: string;
    post_name: string | null;
    start_date: string | null;
    end_date: string | null;
    billing_rate: string | null;
    status: string;
};

type PageProps = {
    deployment: Deployment;
};

const selectClassName =
    'border-input flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] md:text-sm';

export default function DeploymentsEdit() {
    const { deployment } = usePage<PageProps>().props;

    return (
        <>
            <Head title={`Edit Deployment — ${deployment.guard_name}`} />

            <div className="max-w-2xl space-y-6 p-4">
                <Heading
                    title="Edit Deployment"
                    description={`${deployment.guard_name} at ${deployment.client_name ?? 'Unassigned client'}`}
                />

                <Form {...DeploymentController.update.form(deployment.id)} className="space-y-6">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="post_name">Post Name</Label>
                                <Input id="post_name" name="post_name" defaultValue={deployment.post_name ?? ''} />
                                <InputError message={errors.post_name} />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="end_date">End Date</Label>
                                    <Input id="end_date" name="end_date" type="date" defaultValue={deployment.end_date ?? ''} />
                                    <InputError message={errors.end_date} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="billing_rate">Billing Rate</Label>
                                    <Input
                                        id="billing_rate"
                                        name="billing_rate"
                                        type="number"
                                        step="0.01"
                                        min={0}
                                        required
                                        defaultValue={deployment.billing_rate ?? ''}
                                    />
                                    <InputError message={errors.billing_rate} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="status">Status</Label>
                                <select id="status" name="status" required defaultValue={deployment.status} className={selectClassName}>
                                    <option value="active">Active</option>
                                    <option value="ended">Ended</option>
                                </select>
                                <InputError message={errors.status} />
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

DeploymentsEdit.layout = {
    breadcrumbs: [
        {
            title: 'Deployments',
            href: index(),
        },
        {
            title: 'Edit Deployment',
            href: index(),
        },
    ],
};
