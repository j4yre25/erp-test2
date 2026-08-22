import { Head, Link, usePage } from '@inertiajs/react';
import DeploymentController from '@/actions/App/Http/Controllers/DeploymentController';
import ConfirmDeleteButton from '@/components/confirm-delete-button';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { create, edit, index } from '@/routes/deployments';

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
    deployments: Deployment[];
    can: {
        create_deployment: boolean;
    };
};

export default function DeploymentsIndex() {
    const { deployments, can } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Deployments" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Deployments"
                        description="Assign guards to clients and track active postings"
                    />

                    {can.create_deployment && (
                        <Button asChild>
                            <Link href={create()}>Assign Guard</Link>
                        </Button>
                    )}
                </div>

                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full border-collapse text-left text-sm">
                        <thead>
                            <tr className="border-b bg-muted/50 text-xs uppercase tracking-wide text-muted-foreground">
                                <th className="px-4 py-2 font-semibold">Client</th>
                                <th className="px-4 py-2 font-semibold">Guard</th>
                                <th className="px-4 py-2 font-semibold">Post</th>
                                <th className="px-4 py-2 font-semibold">Start Date</th>
                                <th className="px-4 py-2 font-semibold">End Date</th>
                                <th className="px-4 py-2 font-semibold">Billing Rate</th>
                                <th className="px-4 py-2 font-semibold">Status</th>
                                <th className="px-4 py-2 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {deployments.map((deployment) => (
                                <tr key={deployment.id} className="border-b last:border-b-0">
                                    <td className="px-4 py-2 font-medium">{deployment.client_name || '—'}</td>
                                    <td className="px-4 py-2">{deployment.guard_name}</td>
                                    <td className="px-4 py-2">{deployment.post_name || '—'}</td>
                                    <td className="px-4 py-2">{deployment.start_date || '—'}</td>
                                    <td className="px-4 py-2">{deployment.end_date || '—'}</td>
                                    <td className="px-4 py-2">{deployment.billing_rate ?? '—'}</td>
                                    <td className="px-4 py-2">
                                        <Badge variant={deployment.status === 'active' ? 'default' : 'outline'}>
                                            {deployment.status}
                                        </Badge>
                                    </td>
                                    <td className="px-4 py-2">
                                        <div className="flex items-center gap-2">
                                            <Button size="sm" variant="outline" asChild>
                                                <Link href={edit(deployment.id)}>Edit</Link>
                                            </Button>

                                            <ConfirmDeleteButton
                                                form={DeploymentController.destroy.form(deployment.id)}
                                                title="Delete this deployment?"
                                                description={`This will permanently remove the deployment for ${deployment.guard_name} and cannot be undone.`}
                                            />
                                        </div>
                                    </td>
                                </tr>
                            ))}

                            {deployments.length === 0 && (
                                <tr>
                                    <td colSpan={8} className="px-4 py-6 text-center text-muted-foreground">
                                        No deployments yet.
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

DeploymentsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Deployments',
            href: index(),
        },
    ],
};
