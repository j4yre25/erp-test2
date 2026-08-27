import { Form, Head, usePage } from '@inertiajs/react';
import { useState } from 'react';
import UserRoleController from '@/actions/App/Http/Controllers/UserRoleController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter, 
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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

export default function UserManagementIndex() {
    const { users, roles } = usePage<PageProps>().props;

    return (
        <>
            <Head title="User Management" />

            <div className="space-y-6 p-4">
                <Heading
                    title="User Management"
                    description="View account details and assign each user to a role"
                />

                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full border-collapse text-left text-sm">
                        <thead>
                            <tr className="border-b bg-muted/50 text-xs uppercase tracking-wide text-muted-foreground">
                                <th className="px-4 py-2 font-semibold">Name</th>
                                <th className="px-4 py-2 font-semibold">Email</th>
                                <th className="px-4 py-2 font-semibold">Current Role</th>
                                <th className="px-4 py-2 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {users.map((user) => (
                                <UserManagementRow key={user.id} user={user} roles={roles} />
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

UserManagementIndex.layout = {
    breadcrumbs: [
        {
            title: 'User Management',
            href: index(),
        },
    ],
};

function UserManagementRow({ user, roles }: { user: UserRow; roles: RoleOption[] }) {
    const [viewOpen, setViewOpen] = useState(false);
    const [editOpen, setEditOpen] = useState(false);

    return (
        <tr className="border-b last:border-b-0">
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
                <div className="flex items-center gap-2">
                    <Dialog open={viewOpen} onOpenChange={setViewOpen}>
                        <DialogTrigger asChild>
                            <Button size="sm" variant="outline">
                                View
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>{user.name}</DialogTitle>
                                <DialogDescription>User account details</DialogDescription>
                            </DialogHeader>

                            <div className="grid gap-3 text-sm">
                                <div className="grid gap-1">
                                    <span className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Name
                                    </span>
                                    <span>{user.name}</span>
                                </div>
                                <div className="grid gap-1">
                                    <span className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Email
                                    </span>
                                    <span>{user.email}</span>
                                </div>
                                <div className="grid gap-1">
                                    <span className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                        Role
                                    </span>
                                    {user.role_name ? (
                                        <Badge variant="secondary" className="w-fit">
                                            {user.role_name}
                                        </Badge>
                                    ) : (
                                        <span className="text-muted-foreground">Unassigned</span>
                                    )}
                                </div>
                            </div>

                            <DialogFooter>
                                <Button type="button" variant="outline" onClick={() => setViewOpen(false)}>
                                    Close
                                </Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>

                    <Dialog open={editOpen} onOpenChange={setEditOpen}>
                        <DialogTrigger asChild>
                            <Button size="sm">Edit</Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Edit User</DialogTitle>
                                <DialogDescription>
                                    Update account details and assign a role for {user.name}.
                                </DialogDescription>
                            </DialogHeader>

                            <Form
                                {...UserRoleController.update.form(user.id)}
                                options={{ preserveScroll: true }}
                                className="space-y-4"
                                onSuccess={() => setEditOpen(false)}
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor={`name-${user.id}`}>Name</Label>
                                            <Input id={`name-${user.id}`} name="name" defaultValue={user.name} />
                                            <InputError message={errors.name} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor={`email-${user.id}`}>Email</Label>
                                            <Input
                                                id={`email-${user.id}`}
                                                name="email"
                                                type="email"
                                                defaultValue={user.email}
                                            />
                                            <InputError message={errors.email} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor={`role-${user.id}`}>Role</Label>
                                            <select
                                                id={`role-${user.id}`}
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

                                        <DialogFooter>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={() => setEditOpen(false)}
                                            >
                                                Cancel
                                            </Button>
                                            <Button disabled={processing} type="submit">
                                                Save
                                            </Button>
                                        </DialogFooter>
                                    </>
                                )}
                            </Form>
                        </DialogContent>
                    </Dialog>
                </div>
            </td>
        </tr>
    );
}
