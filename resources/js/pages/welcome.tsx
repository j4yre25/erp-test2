import { Head, Link, usePage } from '@inertiajs/react';
import { ClipboardList, ShieldCheck, UserCog, Wallet } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard, login } from '@/routes';

const modules = [
    {
        title: 'Client Management',
        description: "Maintain client company records and each account's payroll cutoff schedule.",
        icon: ClipboardList,
    },
    {
        title: 'Guard Deployment',
        description: 'Assign guards to clients, track posts, and flip status the moment a guard is deployed.',
        icon: ShieldCheck,
    },
    {
        title: 'Payroll & Compensation',
        description: "Keep every guard's rates, government numbers, and compensation details in one place.",
        icon: Wallet,
    },
    {
        title: 'Role-Based Access',
        description: 'Admin, Guard Supervisor, and Payroll each see only the tools their role needs.',
        icon: UserCog,
    },
];

export default function Welcome() {
    const { auth, name } = usePage().props;

    return (
        <>
            <Head title="Welcome" />

            <div className="flex min-h-screen flex-col bg-background text-foreground">
                <header className="mx-auto flex w-full max-w-5xl items-center justify-between p-6">
                    <div className="flex items-center gap-2 font-medium">
                        <AppLogoIcon className="size-9 rounded-full object-contain" />
                        <span className="text-lg font-semibold tracking-tight">{name}</span>
                    </div>

                    <Button asChild>
                        <Link href={auth.user ? dashboard() : login()}>
                            {auth.user ? 'Dashboard' : 'Log in'}
                        </Link>
                    </Button>
                </header>

                <main className="mx-auto flex w-full max-w-5xl flex-1 flex-col items-center justify-center gap-12 px-6 py-16 text-center">
                    <div className="space-y-4">
                        <AppLogoIcon className="mx-auto size-20 rounded-full object-contain shadow-sm" />

                        <h1 className="text-4xl font-semibold tracking-tight sm:text-5xl">
                            {name}
                        </h1>
                        <p className="mx-auto max-w-xl text-lg text-muted-foreground">
                            SOCOPA HRIS
                        </p>

                        <div className="pt-2">
                            <Button size="lg" asChild>
                                <Link href={auth.user ? dashboard() : login()}>
                                    {auth.user ? 'Go to Dashboard' : 'Log in to get started'}
                                </Link>
                            </Button>
                        </div>
                    </div>

                    <div className="grid w-full gap-4 sm:grid-cols-2">
                        {modules.map((module) => (
                            <Card key={module.title} className="text-left">
                                <CardHeader>
                                    <module.icon className="mb-1 size-6 text-primary" />
                                    <CardTitle>{module.title}</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-sm text-muted-foreground">
                                        {module.description}
                                    </p>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </main>
            </div>
        </>
    );
}
