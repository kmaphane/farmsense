'use client';

import * as React from 'react';
import * as SheetPrimitive from '@radix-ui/react-dialog';
import { XIcon } from 'lucide-react';

import { cn } from '@/lib/utils';

/**
 * Sheet Component
 *
 * A side panel component that slides in from the edge of the screen.
 * Built following shadcn/ui v4 patterns using @radix-ui/react-dialog.
 */

function Sheet({ ...props }: React.ComponentProps<typeof SheetPrimitive.Root>) {
    return <SheetPrimitive.Root data-slot="sheet" {...props} />;
}

function SheetTrigger({ ...props }: React.ComponentProps<typeof SheetPrimitive.Trigger>) {
    return <SheetPrimitive.Trigger data-slot="sheet-trigger" {...props} />;
}

function SheetClose({ ...props }: React.ComponentProps<typeof SheetPrimitive.Close>) {
    return <SheetPrimitive.Close data-slot="sheet-close" {...props} />;
}

function SheetPortal({ ...props }: React.ComponentProps<typeof SheetPrimitive.Portal>) {
    return <SheetPrimitive.Portal data-slot="sheet-portal" {...props} />;
}

function SheetOverlay({ className, ...props }: React.ComponentProps<typeof SheetPrimitive.Overlay>) {
    return (
        <SheetPrimitive.Overlay
            data-slot="sheet-overlay"
            className={cn(
                'data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 fixed inset-0 z-50 bg-black/50',
                className
            )}
            {...props}
        />
    );
}

const sizeClasses = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    full: 'sm:max-w-full',
};

function SheetContent({
    className,
    children,
    side = 'right',
    size = 'sm',
    ...props
}: React.ComponentProps<typeof SheetPrimitive.Content> & {
    side?: 'top' | 'right' | 'bottom' | 'left';
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'full';
}) {
    return (
        <SheetPortal>
            <SheetOverlay />
            <SheetPrimitive.Content
                data-slot="sheet-content"
                className={cn(
                    'bg-background data-[state=open]:animate-in data-[state=closed]:animate-out fixed z-50 flex flex-col gap-4 shadow-lg transition ease-in-out data-[state=closed]:duration-300 data-[state=open]:duration-500',
                    side === 'right' &&
                        `data-[state=closed]:slide-out-to-right data-[state=open]:slide-in-from-right inset-y-0 right-0 h-full w-3/4 border-l ${sizeClasses[size]}`,
                    side === 'left' &&
                        `data-[state=closed]:slide-out-to-left data-[state=open]:slide-in-from-left inset-y-0 left-0 h-full w-3/4 border-r ${sizeClasses[size]}`,
                    side === 'top' &&
                        'data-[state=closed]:slide-out-to-top data-[state=open]:slide-in-from-top inset-x-0 top-0 h-auto border-b',
                    side === 'bottom' &&
                        'data-[state=closed]:slide-out-to-bottom data-[state=open]:slide-in-from-bottom inset-x-0 bottom-0 h-auto border-t',
                    className
                )}
                {...props}
            >
                {children}
                <SheetPrimitive.Close className="ring-offset-background focus:ring-ring data-[state=open]:bg-secondary absolute top-4 right-4 rounded-sm opacity-70 transition-opacity hover:opacity-100 focus:ring-2 focus:ring-offset-2 focus:outline-hidden disabled:pointer-events-none">
                    <XIcon className="size-4" />
                    <span className="sr-only">Close</span>
                </SheetPrimitive.Close>
            </SheetPrimitive.Content>
        </SheetPortal>
    );
}

function SheetHeader({
    className,
    title,
    description,
    icon,
    children,
    ...props
}: React.ComponentProps<'div'> & {
    title?: string;
    description?: string;
    icon?: React.ReactNode;
}) {
    // If using simple composition pattern (children only)
    if (children && !title) {
        return (
            <div data-slot="sheet-header" className={cn('flex flex-col gap-1.5 p-4', className)} {...props}>
                {children}
            </div>
        );
    }

    // Enhanced header with icon support
    return (
        <div data-slot="sheet-header" className={cn('flex flex-col gap-1.5 border-b p-4', className)} {...props}>
            <div className="flex items-center gap-3 pr-8">
                {icon && (
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                        {icon}
                    </div>
                )}
                <div className="min-w-0 flex-1">
                    {title && (
                        <SheetPrimitive.Title className="text-foreground truncate font-semibold">
                            {title}
                        </SheetPrimitive.Title>
                    )}
                    {description ? (
                        <SheetPrimitive.Description className="text-muted-foreground mt-1 text-sm">
                            {description}
                        </SheetPrimitive.Description>
                    ) : (
                        <SheetPrimitive.Description className="sr-only">
                            {title}
                        </SheetPrimitive.Description>
                    )}
                    {children}
                </div>
            </div>
        </div>
    );
}

function SheetFooter({ className, ...props }: React.ComponentProps<'div'>) {
    return <div data-slot="sheet-footer" className={cn('mt-auto flex flex-col gap-2 border-t p-4', className)} {...props} />;
}

function SheetBody({ className, children, ...props }: React.ComponentProps<'div'>) {
    return (
        <div data-slot="sheet-body" className={cn('flex-1 overflow-y-auto p-4', className)} {...props}>
            {children}
        </div>
    );
}

function SheetTitle({ className, ...props }: React.ComponentProps<typeof SheetPrimitive.Title>) {
    return <SheetPrimitive.Title data-slot="sheet-title" className={cn('text-foreground font-semibold', className)} {...props} />;
}

function SheetDescription({ className, ...props }: React.ComponentProps<typeof SheetPrimitive.Description>) {
    return <SheetPrimitive.Description data-slot="sheet-description" className={cn('text-muted-foreground text-sm', className)} {...props} />;
}

export { Sheet, SheetTrigger, SheetClose, SheetContent, SheetHeader, SheetFooter, SheetBody, SheetTitle, SheetDescription };
