import { Link } from '@inertiajs/react';
import { Bird, DollarSign, Package, Plus, Scissors, Users } from 'lucide-react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

interface QuickAction {
    label: string;
    description: string;
    href: string;
    icon: React.ElementType;
    color: string;
    group: 'production' | 'management';
}

const quickActions: QuickAction[] = [
    {
        label: 'New Batch',
        description: 'Start a new broiler batch',
        href: '/admin/batches/create',
        icon: Bird,
        color: 'text-green-600 dark:text-green-400',
        group: 'production',
    },
    {
        label: 'Record Slaughter',
        description: 'Process birds for sale',
        href: '/slaughter/create',
        icon: Scissors,
        color: 'text-red-600 dark:text-red-400',
        group: 'production',
    },
    {
        label: 'New Portioning',
        description: 'Cut into packs',
        href: '/portioning/create',
        icon: Package,
        color: 'text-purple-600 dark:text-purple-400',
        group: 'production',
    },
    {
        label: 'Update Pricing',
        description: 'Manage product prices',
        href: '/products/pricing',
        icon: DollarSign,
        color: 'text-blue-600 dark:text-blue-400',
        group: 'management',
    },
    {
        label: 'New Customer',
        description: 'Add a customer',
        href: '/admin/customers/create',
        icon: Users,
        color: 'text-amber-600 dark:text-amber-400',
        group: 'management',
    },
];

export function QuickActionsMenu() {
    const [open, setOpen] = useState(false);

    const productionActions = quickActions.filter(
        (action) => action.group === 'production',
    );
    const managementActions = quickActions.filter(
        (action) => action.group === 'management',
    );

    return (
        <TooltipProvider>
            <DropdownMenu open={open} onOpenChange={setOpen}>
                <Tooltip>
                    <TooltipTrigger asChild>
                        <DropdownMenuTrigger asChild>
                            <Button
                                variant="ghost"
                                size="icon"
                                className="h-8 w-8 rounded-full bg-amber-100 text-amber-700 hover:bg-amber-200 hover:text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 dark:hover:bg-amber-900/50"
                            >
                                <Plus className="h-4 w-4" />
                                <span className="sr-only">Quick Create</span>
                            </Button>
                        </DropdownMenuTrigger>
                    </TooltipTrigger>
                    <TooltipContent side="bottom">
                        <p>Quick Create</p>
                    </TooltipContent>
                </Tooltip>
                <DropdownMenuContent align="end" className="w-56">
                    <DropdownMenuLabel>Quick Create</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuGroup>
                        <DropdownMenuLabel className="text-xs font-normal text-muted-foreground">
                            Production
                        </DropdownMenuLabel>
                        {productionActions.map((action) => (
                            <DropdownMenuItem key={action.href} asChild>
                                <Link
                                    href={action.href}
                                    className="flex cursor-pointer items-center gap-3"
                                    onClick={() => setOpen(false)}
                                >
                                    <action.icon
                                        className={`h-4 w-4 ${action.color}`}
                                    />
                                    <div className="flex flex-col">
                                        <span className="font-medium">
                                            {action.label}
                                        </span>
                                        <span className="text-xs text-muted-foreground">
                                            {action.description}
                                        </span>
                                    </div>
                                </Link>
                            </DropdownMenuItem>
                        ))}
                    </DropdownMenuGroup>
                    <DropdownMenuSeparator />
                    <DropdownMenuGroup>
                        <DropdownMenuLabel className="text-xs font-normal text-muted-foreground">
                            Management
                        </DropdownMenuLabel>
                        {managementActions.map((action) => (
                            <DropdownMenuItem key={action.href} asChild>
                                <Link
                                    href={action.href}
                                    className="flex cursor-pointer items-center gap-3"
                                    onClick={() => setOpen(false)}
                                >
                                    <action.icon
                                        className={`h-4 w-4 ${action.color}`}
                                    />
                                    <div className="flex flex-col">
                                        <span className="font-medium">
                                            {action.label}
                                        </span>
                                        <span className="text-xs text-muted-foreground">
                                            {action.description}
                                        </span>
                                    </div>
                                </Link>
                            </DropdownMenuItem>
                        ))}
                    </DropdownMenuGroup>
                </DropdownMenuContent>
            </DropdownMenu>
        </TooltipProvider>
    );
}
