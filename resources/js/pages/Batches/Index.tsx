import { BatchList, type BatchCardData } from '@/components/broiler/BatchCard';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    batches: BatchCardData[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Batches',
        href: '/batches',
    },
];

export default function Index({ batches }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Batches" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                            Active Batches
                        </h1>
                        <p className="text-sm text-gray-500 dark:text-gray-400">
                            Manage and monitor your broiler batches
                        </p>
                    </div>
                </div>
                <BatchList
                    batches={batches}
                    variant="card"
                    emptyMessage="No Active Batches"
                    emptyDescription="There are no active batches assigned to your team. Create a new batch in the admin panel to get started."
                    showCreateButton
                />
            </div>
        </AppLayout>
    );
}
