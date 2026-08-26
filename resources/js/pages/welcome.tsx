import { Head, Link, usePage } from '@inertiajs/react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { ClipboardList, ShieldCheck, UserCog, Wallet } from 'lucide-react';
import { useEffect, useRef } from 'react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard, login } from '@/routes';

gsap.registerPlugin(ScrollTrigger);

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
    const year = new Date().getFullYear();
    const rootRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const ctx = gsap.context(() => {
            gsap.timeline({ defaults: { ease: 'power3.out' } })
                .from('.hero-badge', { opacity: 0, y: 16, duration: 0.5 })
                .from('.hero-title', { opacity: 0, y: 24, duration: 0.7 }, '-=0.25')
                .from('.hero-desc', { opacity: 0, y: 20, duration: 0.6 }, '-=0.35')
                .from('.hero-cta', { opacity: 0, y: 16, duration: 0.5 }, '-=0.3');

            gsap.from('.module-card', {
                opacity: 0,
                y: 32,
                duration: 0.6,
                stagger: 0.12,
                scrollTrigger: {
                    trigger: '.module-grid',
                    start: 'top 85%',
                },
            });
        }, rootRef);

        return () => ctx.revert();
    }, []);

    return (
        <>
            <Head title="Welcome" />

            <div ref={rootRef} className="flex min-h-screen flex-col bg-background text-foreground">
                <header className="border-b border-white/10 bg-zinc-900 text-white">
                    <div className="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-4">
                        <div className="flex items-center gap-3">
                            <AppLogoIcon className="size-9 rounded-full object-contain" />
                            <div className="leading-tight">
                                <span className="block text-base font-semibold tracking-tight">{name}</span>
                                <span className="block text-xs text-zinc-400">Security Agency, Inc.</span>
                            </div>
                        </div>

                        <Button asChild variant="secondary">
                            <Link href={auth.user ? dashboard() : login()}>
                                {auth.user ? 'Dashboard' : 'Staff Log in'}
                            </Link>
                        </Button>
                    </div>
                </header>

                <main className="flex-1">
                    <section className="relative overflow-hidden bg-zinc-900 text-white">
                        <div
                            className="pointer-events-none absolute inset-0 opacity-40"
                            style={{
                                backgroundImage: 'radial-gradient(rgba(255,255,255,0.08) 1px, transparent 1px)',
                                backgroundSize: '22px 22px',
                            }}
                        />
                        <div className="absolute inset-0 bg-gradient-to-b from-primary/10 via-transparent to-zinc-900" />

                        <div className="relative mx-auto flex w-full max-w-6xl flex-col items-center gap-6 px-6 py-20 text-center">
                            <span className="hero-badge inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-1.5 text-xs font-medium tracking-wide text-zinc-300 uppercase">
                                <ShieldCheck className="size-3.5 text-primary" />
                                Licensed Private Security Agency
                            </span>

                            <h1 className="hero-title max-w-3xl text-4xl font-semibold tracking-tight sm:text-5xl">
                                Guarding Every Post. Managing Every Guard.
                            </h1>

                            <p className="hero-desc max-w-xl text-lg text-zinc-300">
                                The internal Human Resource &amp; Payroll System of {name} Security Agency, Inc. —
                                built to keep client deployments, guard records, and payroll running as reliably as
                                the guards on post.
                            </p>

                            <div className="hero-cta pt-2">
                                <Button size="lg" asChild>
                                    <Link href={auth.user ? dashboard() : login()}>
                                        {auth.user ? 'Go to Dashboard' : 'Personnel Log in'}
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </section>

                    <section className="mx-auto w-full max-w-6xl px-6 py-16">
                        <div className="mb-10 text-center">
                            <h2 className="text-2xl font-semibold tracking-tight">One system, every post covered</h2>
                            <p className="mt-2 text-muted-foreground">
                                From client onboarding to payroll release, every operation runs through one system.
                            </p>
                        </div>

                        <div className="module-grid grid gap-4 sm:grid-cols-2">
                            {modules.map((module) => (
                                <Card key={module.title} className="module-card border-l-4 border-l-primary text-left">
                                    <CardHeader>
                                        <div className="mb-1 flex size-10 items-center justify-center rounded-lg bg-primary/10">
                                            <module.icon className="size-5 text-primary" />
                                        </div>
                                        <CardTitle>{module.title}</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <p className="text-sm text-muted-foreground">{module.description}</p>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </section>
                </main>

                <footer className="border-t bg-muted/30 py-6 text-center text-xs text-muted-foreground">
                    <p>
                        &copy; {year} {name} Security Agency, Inc. All rights reserved.
                    </p>
                    <p className="mt-1">This system is restricted to authorized personnel only.</p>
                </footer>
            </div>
        </>
    );
}
