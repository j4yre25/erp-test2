import { Moon, Sun } from 'lucide-react';
import { useState, useEffect } from 'react';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { Button } from '@/components/ui/button';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { useAppearance } from '@/hooks/use-appearance';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const [mounted, setMounted] = useState(false);

    useEffect(() => {
        setMounted(true);
    }, []);

    return (
        <header className="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border bg-card px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-14 md:px-6">
            <div className="flex items-center gap-2">
                <SidebarTrigger className="-ml-1" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>

            <Button
                variant="ghost"
                size="icon"
                className="text-muted-foreground"
                onClick={() =>
                    updateAppearance(
                        resolvedAppearance === 'dark' ? 'light' : 'dark',
                    )
                }
            >
                {mounted ? (
                    resolvedAppearance === 'dark' ? (
                        <Sun className="size-4.5" />
                    ) : (
                        <Moon className="size-4.5" />
                    )
                ) : (
                    <Moon className="size-4.5" />
                )}
                <span className="sr-only">Toggle dark mode</span>
            </Button>
        </header>
    );
}
