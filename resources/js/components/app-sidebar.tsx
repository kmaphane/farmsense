import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavSection } from '@/components/nav-section';
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
import { type NavGroup, type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import {
    Bird,
    BookOpen,
    Building2,
    ClipboardList,
    FileText,
    Layers,
    LayoutGrid,
    Package,
    Scissors,
    Settings,
    ShoppingCart,
    TrendingUp,
    Users,
    Weight,
} from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
];

const navGroups: NavGroup[] = [
    {
        title: 'Batches',
        items: [
            {
                title: 'Active Batches',
                href: '/batches',
                icon: Bird,
            },
            {
                title: 'Batch History',
                href: '/batches/history',
                icon: ClipboardList,
            },
            {
                title: 'Daily Logs',
                href: '/daily-logs',
                icon: FileText,
            },
            {
                title: 'Slaughter',
                href: '/slaughter',
                icon: Scissors,
            },
            {
                title: 'Portioning',
                href: '/portioning',
                icon: Weight,
            },
        ],
    },
    {
        title: 'Inventory',
        items: [
            {
                title: 'Products',
                href: '/inventory/products',
                icon: Package,
            },
            {
                title: 'Stock Movements',
                href: '/stock-movements',
                icon: TrendingUp,
            },
            {
                title: 'Warehouses',
                href: '/warehouses',
                icon: Building2,
            },
        ],
    },
    {
        title: 'CRM',
        items: [
            {
                title: 'Customers',
                href: '/customers',
                icon: Users,
            },
            {
                title: 'Suppliers',
                href: '/suppliers',
                icon: Layers,
            },
        ],
    },
    {
        title: 'Sales',
        items: [
            {
                title: 'Live Sales',
                href: '/live-sales',
                icon: ShoppingCart,
            },
        ],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Admin Panel',
        href: '/admin',
        icon: Settings,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
                <NavSection groups={navGroups} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
