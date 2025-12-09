import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { resolveUrl } from '@/lib/utils';
import { type NavGroup } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import { useState } from 'react';

export function NavSection({ groups = [] }: { groups: NavGroup[] }) {
    const page = usePage();
    const [openGroups, setOpenGroups] = useState<Record<string, boolean>>(
        () => {
            // Initialize all groups as open by default
            return groups.reduce(
                (acc, group) => ({ ...acc, [group.title]: true }),
                {},
            );
        },
    );

    const toggleGroup = (title: string) => {
        setOpenGroups((prev) => ({ ...prev, [title]: !prev[title] }));
    };

    return (
        <>
            {groups.map((group) => {
                const isOpen = openGroups[group.title];
                return (
                    <SidebarGroup key={group.title} className="px-2 py-0">
                        <SidebarGroupLabel
                            className="group/label flex cursor-pointer items-center justify-between hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                            onClick={() => toggleGroup(group.title)}
                        >
                            <span>{group.title}</span>
                            <ChevronRight
                                className={`size-4 transition-transform duration-200 ${
                                    isOpen ? 'rotate-90' : ''
                                }`}
                            />
                        </SidebarGroupLabel>
                        {isOpen && (
                            <SidebarGroupContent>
                                <SidebarMenu>
                                    {group.items.map((item) => (
                                        <SidebarMenuItem key={item.title}>
                                            <SidebarMenuButton
                                                asChild
                                                isActive={page.url.startsWith(
                                                    resolveUrl(item.href),
                                                )}
                                                tooltip={{
                                                    children: item.title,
                                                }}
                                            >
                                                <Link href={item.href} prefetch>
                                                    {item.icon && <item.icon />}
                                                    <span>{item.title}</span>
                                                </Link>
                                            </SidebarMenuButton>
                                        </SidebarMenuItem>
                                    ))}
                                </SidebarMenu>
                            </SidebarGroupContent>
                        )}
                    </SidebarGroup>
                );
            })}
        </>
    );
}
