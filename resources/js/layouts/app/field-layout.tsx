import AppLogoIcon from '@/components/app-logo-icon';
import { SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ArrowLeft, Home, LogOut } from 'lucide-react';
import * as React from 'react';

interface FieldLayoutProps {
    children: React.ReactNode;
    title: string;
    backHref?: string;
    backLabel?: string;
}

export function FieldLayout({ children, title, backHref, backLabel }: FieldLayoutProps) {
    const { auth } = usePage<SharedData>().props;

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
            {/* Header */}
            <header className="sticky top-0 z-50 border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-950">
                <div className="flex h-14 items-center justify-between px-4">
                    <div className="flex items-center gap-3">
                        {backHref ? (
                            <Link
                                href={backHref}
                                className="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                            >
                                <ArrowLeft className="h-4 w-4" />
                                <span className="hidden sm:inline">{backLabel || 'Back'}</span>
                            </Link>
                        ) : (
                            <Link href="/batches" className="flex items-center gap-2">
                                <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-green-600 text-white">
                                    <AppLogoIcon className="size-5 fill-current" />
                                </div>
                                <span className="font-semibold text-gray-900 dark:text-gray-100">FarmSense</span>
                            </Link>
                        )}
                    </div>

                    <h1 className="absolute left-1/2 -translate-x-1/2 text-base font-semibold text-gray-900 dark:text-gray-100">
                        {title}
                    </h1>

                    <div className="flex items-center gap-2">
                        <span className="hidden text-sm text-gray-600 dark:text-gray-400 sm:inline">
                            {auth.user.name}
                        </span>
                        <Link href="/admin" className="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" title="Admin Panel">
                            <Home className="h-5 w-5" />
                        </Link>
                    </div>
                </div>
            </header>

            {/* Main Content */}
            <main className="mx-auto max-w-3xl px-4 py-6">
                {children}
            </main>

            {/* Bottom Navigation (Mobile) */}
            <nav className="fixed bottom-0 left-0 right-0 border-t border-gray-200 bg-white pb-safe dark:border-gray-800 dark:bg-gray-950 sm:hidden">
                <div className="flex h-16 items-center justify-around">
                    <Link
                        href="/batches"
                        className="flex flex-col items-center gap-1 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        <Home className="h-5 w-5" />
                        <span>Batches</span>
                    </Link>
                    <Link
                        href="/admin"
                        className="flex flex-col items-center gap-1 text-xs text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        <LogOut className="h-5 w-5" />
                        <span>Admin</span>
                    </Link>
                </div>
            </nav>
        </div>
    );
}
