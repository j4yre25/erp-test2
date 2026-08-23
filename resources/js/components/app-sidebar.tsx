import { Link, usePage } from '@inertiajs/react';
import { BookOpen, Calculator, CheckSquare, FileText, FolderGit2, LayoutGrid, ShieldCheck, UserCog, Users } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as clientsIndex } from '@/routes/clients';
import { index as deploymentsIndex } from '@/routes/deployments';
import { index as gmSoaIndex } from '@/routes/gm/statement-of-accounts';
import { index as guardsIndex } from '@/routes/guards';
import { index as payrollPeriodsIndex } from '@/routes/payroll-periods';
import { index as soaIndex } from '@/routes/statement-of-accounts';
import { index as userRolesIndex } from '@/routes/user-roles';
import type { Auth, NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const payrollNavItems: Array<NavItem & { roles: string[] }> = [
    {
        title: 'Guards',
        href: guardsIndex(),
        icon: ShieldCheck,
        roles: ['payroll', 'admin'],
    },
    {
        title: 'Payroll Periods',
        href: payrollPeriodsIndex(),
        icon: Calculator,
        roles: ['payroll', 'admin'],
    },
];

const billingNavItems: Array<NavItem & { roles: string[] }> = [
    {
        title: 'Statement of Accounts',
        href: soaIndex(),
        icon: FileText,
        roles: ['ar', 'account-receivable', 'admin'],
    },
];

const gmNavItems: Array<NavItem & { roles: string[] }> = [
    {
        title: 'SOA Reviews & Archive',
        href: gmSoaIndex(),
        icon: CheckSquare,
        roles: ['general manager', 'gm', 'general-manager', 'admin'],
    },
];

const masterDataNavItems: Array<NavItem & { roles: string[] }> = [
    {
        title: 'Clients',
        href: clientsIndex(),
        icon: Users,
        roles: ['admin'],
    },
    {
        title: 'Deployments',
        href: deploymentsIndex(),
        icon: FolderGit2,
        roles: ['guard supervisor', 'admin'],
    },
    {
        title: 'User Roles',
        href: userRolesIndex(),
        icon: UserCog,
        roles: ['admin'],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    const { auth } = usePage<{ auth: Auth }>().props;
    const roleName = auth.user.role?.role_name?.toLowerCase() ?? '';

    const visiblePayrollItems = payrollNavItems.filter((item) =>
        item.roles.includes(roleName),
    );

    const visibleBillingItems = billingNavItems.filter((item) =>
        item.roles.includes(roleName),
    );

    const visibleGmItems = gmNavItems.filter((item) =>
        item.roles.includes(roleName),
    );

    const visibleMasterDataItems = masterDataNavItems.filter((item) =>
        item.roles.includes(roleName),
    );

    return (
        <Sidebar collapsible="icon" variant="sidebar">
            <SidebarHeader className="border-b border-sidebar-border py-5">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent className="gap-0 py-2">
                <NavMain items={mainNavItems} />

                {visiblePayrollItems.length > 0 && (
                    <NavMain items={visiblePayrollItems} label="Payroll" />
                )}

                {visibleBillingItems.length > 0 && (
                    <NavMain items={visibleBillingItems} label="Billing & AR" />
                )}

                {visibleGmItems.length > 0 && (
                    <NavMain items={visibleGmItems} label="Management" />
                )}

                {visibleMasterDataItems.length > 0 && (
                    <NavMain items={visibleMasterDataItems} label="Master Data" />
                )}
            </SidebarContent>

            <SidebarFooter className="border-t border-sidebar-border">
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
