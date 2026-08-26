import { Head, Link, usePage } from '@inertiajs/react';
import ClientController from '@/actions/App/Http/Controllers/ClientController';
import ConfirmDeleteButton from '@/components/confirm-delete-button';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { optionLabel, PAYROLL_PERIOD_OPTIONS } from '@/lib/client-payroll-options';
import { create, edit, index } from '@/routes/clients';

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
    clients: Client[];
    can: {
        create_client: boolean;
    };
};

function cutoffLabel(client: Client): string {
    if (!client.first_cutoff_day) {
        return '—';
    }

    return client.second_cutoff_day
        ? `${client.first_cutoff_day} & ${client.second_cutoff_day}`
        : `${client.first_cutoff_day}`;
}

export default function ClientsIndex() {
    const { clients, can } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Clients" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Clients"
                        description="Manage client company records and payroll cutoff schedules"
                    />

                    {can.create_client && (
                        <Button asChild>
                            <Link href={create()}>Add Client</Link>
                        </Button>
                    )}
                </div>

                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full border-collapse text-left text-sm">
                        <thead>
                            <tr className="border-b bg-muted/50 text-xs uppercase tracking-wide text-muted-foreground">
                                <th className="px-4 py-2 font-semibold">Name</th>
                                <th className="px-4 py-2 font-semibold">Contact Person</th>
                                <th className="px-4 py-2 font-semibold">Payroll Period</th>
                                <th className="px-4 py-2 font-semibold">Cutoff Day(s)</th>
                                <th className="px-4 py-2 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {clients.map((client) => (
                                <tr key={client.id} className="border-b last:border-b-0">
                                    <td className="px-4 py-2 font-medium">{client.name}</td>
                                    <td className="px-4 py-2">{client.contact_person || '—'}</td>
                                    <td className="px-4 py-2">{optionLabel(PAYROLL_PERIOD_OPTIONS, client.payroll_period)}</td>
                                    <td className="px-4 py-2">{cutoffLabel(client)}</td>
                                    <td className="px-4 py-2">
                                        <div className="flex items-center gap-2">
                                            <Button size="sm" variant="outline" asChild>
                                                <Link href={edit(client.id)}>Edit</Link>
                                            </Button>

                                            <ConfirmDeleteButton
                                                form={ClientController.destroy.form(client.id)}
                                                title="Delete this client?"
                                                description={`This will permanently remove ${client.name} and cannot be undone.`}
                                            />
                                        </div>
                                    </td>
                                </tr>
                            ))}

                            {clients.length === 0 && (
                                <tr>
                                    <td colSpan={5} className="px-4 py-6 text-center text-muted-foreground">
                                        No clients yet.
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

ClientsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Clients',
            href: index(),
        },
    ],
};
