import { Form, Head, Link, usePage } from '@inertiajs/react';
import GuardController from '@/actions/App/Http/Controllers/GuardController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/guards';

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
    guard: Guard;
};

const selectClassName =
    'border-input flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] md:text-sm';

export default function GuardsEdit() {
    const { guard } = usePage<PageProps>().props;

    return (
        <>
            <Head title={`Edit ${guard.full_name}`} />

            <div className="max-w-2xl space-y-6 p-4">
                <Heading
                    title="Edit Guard"
                    description="Update this guard's profile and compensation details"
                />

                <Form {...GuardController.update.form(guard.id)} className="space-y-6">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="employee_number">Employee Number</Label>
                                    <Input id="employee_number" name="employee_number" required defaultValue={guard.employee_number} />
                                    <InputError message={errors.employee_number} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="contact_number">Contact Number</Label>
                                    <Input id="contact_number" name="contact_number" defaultValue={guard.contact_number ?? ''} />
                                    <InputError message={errors.contact_number} />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="first_name">First Name</Label>
                                    <Input id="first_name" name="first_name" required defaultValue={guard.first_name} />
                                    <InputError message={errors.first_name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="last_name">Last Name</Label>
                                    <Input id="last_name" name="last_name" required defaultValue={guard.last_name} />
                                    <InputError message={errors.last_name} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="address">Address</Label>
                                <Input id="address" name="address" defaultValue={guard.address ?? ''} />
                                <InputError message={errors.address} />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="employment_status">Employment Status</Label>
                                    <select
                                        id="employment_status"
                                        name="employment_status"
                                        required
                                        defaultValue={guard.employment_status}
                                        className={selectClassName}
                                    >
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    <InputError message={errors.employment_status} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="availability_status">Availability Status</Label>
                                    <select
                                        id="availability_status"
                                        name="availability_status"
                                        required
                                        defaultValue={guard.availability_status}
                                        className={selectClassName}
                                    >
                                        <option value="available">Available</option>
                                        <option value="deployed">Deployed</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    <InputError message={errors.availability_status} />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="daily_rate">Daily Rate</Label>
                                    <Input
                                        id="daily_rate"
                                        name="daily_rate"
                                        type="number"
                                        step="0.01"
                                        min={0}
                                        required
                                        defaultValue={guard.daily_rate ?? ''}
                                    />
                                    <InputError message={errors.daily_rate} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="night_differential_rate">Night Differential Rate</Label>
                                    <Input
                                        id="night_differential_rate"
                                        name="night_differential_rate"
                                        type="number"
                                        step="0.01"
                                        min={0}
                                        required
                                        defaultValue={guard.night_differential_rate ?? ''}
                                    />
                                    <InputError message={errors.night_differential_rate} />
                                </div>
                            </div>

                            <div className="grid grid-cols-3 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="sss_number">SSS Number</Label>
                                    <Input id="sss_number" name="sss_number" defaultValue={guard.sss_number ?? ''} />
                                    <InputError message={errors.sss_number} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="philhealth_number">PhilHealth Number</Label>
                                    <Input id="philhealth_number" name="philhealth_number" defaultValue={guard.philhealth_number ?? ''} />
                                    <InputError message={errors.philhealth_number} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="pagibig_number">Pag-IBIG Number</Label>
                                    <Input id="pagibig_number" name="pagibig_number" defaultValue={guard.pagibig_number ?? ''} />
                                    <InputError message={errors.pagibig_number} />
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

GuardsEdit.layout = {
    breadcrumbs: [
        {
            title: 'Guards',
            href: index(),
        },
        {
            title: 'Edit Guard',
            href: index(),
        },
    ],
};
