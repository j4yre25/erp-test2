import { Head, Link, usePage } from '@inertiajs/react';
import GuardController from '@/actions/App/Http/Controllers/GuardController';
import ConfirmDeleteButton from '@/components/confirm-delete-button';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { create, edit, index } from '@/routes/guards';

type Guard = {
    id: number;
    employee_number: string;
    first_name: string;
    last_name: string;
    full_name: string;
    contact_number: string | null;
    address: string | null;
    employment_status: string;
    availability_status: string;
    daily_rate: string | null;
    night_differential_rate: string | null;
    sss_number: string | null;
    philhealth_number: string | null;
    pagibig_number: string | null;
};

type PageProps = {
    guards: Guard[];
    can: {
        create_guard: boolean;
    };
};

function availabilityVariant(status: string): 'default' | 'secondary' | 'outline' {
    if (status === 'available') {
        return 'default';
    }

    if (status === 'deployed') {
        return 'secondary';
    }

    return 'outline';
}

export default function GuardsIndex() {
    const { guards, can } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Guards" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Guards"
                        description="Manage guard master data and compensation details"
                    />

                    {can.create_guard && (
                        <Button asChild>
                            <Link href={create()}>Add Guard</Link>
                        </Button>
                    )}
                </div>

                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full border-collapse text-left text-sm">
                        <thead>
                            <tr className="border-b bg-muted/50 text-xs uppercase tracking-wide text-muted-foreground">
                                <th className="px-4 py-2 font-semibold">Employee #</th>
                                <th className="px-4 py-2 font-semibold">Name</th>
                                <th className="px-4 py-2 font-semibold">Contact</th>
                                <th className="px-4 py-2 font-semibold">Employment</th>
                                <th className="px-4 py-2 font-semibold">Availability</th>
                                <th className="px-4 py-2 font-semibold">Daily Rate</th>
                                <th className="px-4 py-2 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {guards.map((guard) => (
                                <tr key={guard.id} className="border-b last:border-b-0">
                                    <td className="px-4 py-2 font-medium">{guard.employee_number}</td>
                                    <td className="px-4 py-2">{guard.full_name}</td>
                                    <td className="px-4 py-2">{guard.contact_number || '—'}</td>
                                    <td className="px-4 py-2">
                                        <Badge variant={guard.employment_status === 'active' ? 'default' : 'outline'}>
                                            {guard.employment_status}
                                        </Badge>
                                    </td>
                                    <td className="px-4 py-2">
                                        <Badge variant={availabilityVariant(guard.availability_status)}>
                                            {guard.availability_status}
                                        </Badge>
                                    </td>
                                    <td className="px-4 py-2">{guard.daily_rate ?? '—'}</td>
                                    <td className="px-4 py-2">
                                        <div className="flex items-center gap-2">
                                            <Button size="sm" variant="outline" asChild>
                                                <Link href={edit(guard.id)}>Edit</Link>
                                            </Button>

                                            <ConfirmDeleteButton
                                                form={GuardController.destroy.form(guard.id)}
                                                title="Delete this guard?"
                                                description={`This will permanently remove ${guard.full_name} and cannot be undone.`}
                                            />
                                        </div>
                                    </td>
                                </tr>
                            ))}

                            {guards.length === 0 && (
                                <tr>
                                    <td colSpan={7} className="px-4 py-6 text-center text-muted-foreground">
                                        No guards yet.
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

GuardsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Guards',
            href: index(),
        },
    ],
};
