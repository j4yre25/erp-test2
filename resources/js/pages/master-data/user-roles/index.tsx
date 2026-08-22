import { Form, Head, usePage } from '@inertiajs/react';
import UserRoleController from '@/actions/App/Http/Controllers/UserRoleController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { index } from '@/routes/user-roles';

type RoleOption = {
    id: number;
    role_name: string;
};

type UserRow = {
    id: number;
    name: string;
    email: string;
    role_id: number | null;
    role_name: string | null;
};

type PageProps = {
    users: UserRow[];
    roles: RoleOption[];
};

const selectClassName =
    'border-input flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] md:text-sm';

export default function UserRolesIndex() {
    const { users, roles } = usePage<PageProps>().props;

    return (
        <>
            <Head title="User Roles" />

            <div className="space-y-6 p-4">
                <Heading
                    title="User Roles"
                    description="Assign each user account to a role"
                />

                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full border-collapse text-left text-sm">
                        <thead>
                            <tr className="border-b bg-muted/50 text-xs uppercase tracking-wide text-muted-foreground">
                                <th className="px-4 py-2 font-semibold">Name</th>
                                <th className="px-4 py-2 font-semibold">Email</th>
                                <th className="px-4 py-2 font-semibold">Current Role</th>
                                <th className="px-4 py-2 font-semibold">Assign Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            {users.map((user) => (
                                <tr key={user.id} className="border-b last:border-b-0">
                                    <td className="px-4 py-2 font-medium">{user.name}</td>
                                    <td className="px-4 py-2">{user.email}</td>
                                    <td className="px-4 py-2">
                                        {user.role_name ? (
                                            <Badge variant="secondary">{user.role_name}</Badge>
                                        ) : (
                                            <span className="text-muted-foreground">Unassigned</span>
                                        )}
                                    </td>
                                    <td className="px-4 py-2">
                                        <Form
                                            {...UserRoleController.update.form(user.id)}
                                            options={{ preserveScroll: true }}
                                            className="flex items-start gap-2"
                                        >
                                            {({ processing, errors }) => (
                                                <>
                                                    <div className="grid gap-1">
                                                        <select
                                                            name="role_id"
                                                            defaultValue={user.role_id ?? ''}
                                                            className={selectClassName}
                                                        >
                                                            <option value="">Unassigned</option>
                                                            {roles.map((role) => (
                                                                <option key={role.id} value={role.id}>
                                                                    {role.role_name}
                                                                </option>
                                                            ))}
                                                        </select>
                                                        <InputError message={errors.role_id} />
                                                    </div>

                                                    <Button size="sm" disabled={processing} type="submit">
                                                        Save
                                                    </Button>
                                                </>
                                            )}
                                        </Form>
                                    </td>
                                </tr>
                            ))}

                            {users.length === 0 && (
                                <tr>
                                    <td colSpan={4} className="px-4 py-6 text-center text-muted-foreground">
                                        No users yet.
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

UserRolesIndex.layout = {
    breadcrumbs: [
        {
            title: 'User Roles',
            href: index(),
        },
    ],
};
