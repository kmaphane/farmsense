import { cn } from '@/lib/utils';
import * as SheetPrimitive from '@radix-ui/react-dialog';
import { X } from 'lucide-react';
import * as React from 'react';

interface DrawerProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    children: React.ReactNode;
}

interface DrawerContentProps {
    children: React.ReactNode;
    className?: string;
    side?: 'left' | 'right';
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'full';
}

interface DrawerHeaderProps {
    title: string;
    description?: string;
    icon?: React.ReactNode;
    badge?: React.ReactNode;
    className?: string;
}

interface DrawerBodyProps {
    children: React.ReactNode;
    className?: string;
}

interface DrawerFooterProps {
    children: React.ReactNode;
    className?: string;
}

const sizeClasses = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    full: 'sm:max-w-full',
};

function Drawer({ open, onOpenChange, children }: DrawerProps) {
    return (
        <SheetPrimitive.Root open={open} onOpenChange={onOpenChange}>
            {children}
        </SheetPrimitive.Root>
    );
}

function DrawerTrigger({ children, asChild = true }: { children: React.ReactNode; asChild?: boolean }) {
    return <SheetPrimitive.Trigger asChild={asChild}>{children}</SheetPrimitive.Trigger>;
}

function DrawerContent({ children, className, side = 'right', size = 'lg' }: DrawerContentProps) {
    return (
        <SheetPrimitive.Portal>
            {/* Overlay */}
            <SheetPrimitive.Overlay
                className={cn(
                    'fixed inset-0 z-50 bg-black/60 backdrop-blur-sm',
                    'data-[state=open]:animate-in data-[state=closed]:animate-out',
                    'data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0'
                )}
            />

            {/* Content */}
            <SheetPrimitive.Content
                className={cn(
                    'fixed z-50 flex h-full flex-col bg-white shadow-2xl dark:bg-gray-900',
                    'transition-all ease-in-out',
                    'data-[state=open]:animate-in data-[state=closed]:animate-out',
                    'data-[state=closed]:duration-300 data-[state=open]:duration-400',
                    // Position & animation based on side
                    side === 'right' && [
                        'inset-y-0 right-0 w-full border-l border-gray-200 dark:border-gray-800',
                        'data-[state=closed]:slide-out-to-right data-[state=open]:slide-in-from-right',
                        sizeClasses[size],
                    ],
                    side === 'left' && [
                        'inset-y-0 left-0 w-full border-r border-gray-200 dark:border-gray-800',
                        'data-[state=closed]:slide-out-to-left data-[state=open]:slide-in-from-left',
                        sizeClasses[size],
                    ],
                    className
                )}
            >
                {/* Close button */}
                <SheetPrimitive.Close
                    className={cn(
                        'absolute right-4 top-4 z-10 rounded-full p-2',
                        'bg-gray-100 text-gray-500 transition-colors',
                        'hover:bg-gray-200 hover:text-gray-700',
                        'dark:bg-gray-800 dark:text-gray-400',
                        'dark:hover:bg-gray-700 dark:hover:text-gray-200',
                        'focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2',
                        'dark:focus:ring-offset-gray-900'
                    )}
                >
                    <X className="h-4 w-4" />
                    <span className="sr-only">Close</span>
                </SheetPrimitive.Close>

                {children}
            </SheetPrimitive.Content>
        </SheetPrimitive.Portal>
    );
}

function DrawerHeader({ title, description, icon, badge, className }: DrawerHeaderProps) {
    return (
        <div
            className={cn(
                'shrink-0 border-b border-gray-200 bg-gray-50 px-6 py-5 dark:border-gray-800 dark:bg-gray-900/50',
                className
            )}
        >
            <div className="flex items-start gap-3 pr-8">
                {icon && (
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                        {icon}
                    </div>
                )}
                <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2">
                        <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate">
                            {title}
                        </h2>
                        {badge}
                    </div>
                    {description && (
                        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">{description}</p>
                    )}
                </div>
            </div>
        </div>
    );
}

function DrawerBody({ children, className }: DrawerBodyProps) {
    return (
        <div className={cn('flex-1 overflow-y-auto', className)}>
            <div className="px-6 py-6">{children}</div>
        </div>
    );
}

function DrawerFooter({ children, className }: DrawerFooterProps) {
    return (
        <div
            className={cn(
                'shrink-0 border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-800 dark:bg-gray-900/50',
                className
            )}
        >
            {children}
        </div>
    );
}

function DrawerSection({
    children,
    title,
    className,
}: {
    children: React.ReactNode;
    title?: string;
    className?: string;
}) {
    return (
        <div className={cn('space-y-4', className)}>
            {title && (
                <h3 className="text-sm font-medium text-gray-700 dark:text-gray-300">{title}</h3>
            )}
            {children}
        </div>
    );
}

export { Drawer, DrawerTrigger, DrawerContent, DrawerHeader, DrawerBody, DrawerFooter, DrawerSection };
