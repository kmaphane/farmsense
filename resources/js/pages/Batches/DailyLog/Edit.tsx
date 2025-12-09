import {
    index,
    show,
} from '@/actions/App/Http/Controllers/Batches/BatchController';
import {
    DailyLogForm,
    type DailyLogFormData,
} from '@/components/broiler/DailyLogForm';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface BatchData {
    id: number;
    name: string;
    age_in_days: number;
}

interface Props {
    batch: BatchData;
    dailyLog: DailyLogFormData;
}

export default function Edit({ batch, dailyLog }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Batches', href: index.url() },
        { title: batch.name, href: show.url(batch.id) },
        { title: 'Edit Daily Log', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Log - ${batch.name}`} />

            <div className="px-4 py-6">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
                        Edit Daily Log
                    </h1>
                    <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {batch.name} •{' '}
                        {new Date(dailyLog.log_date).toLocaleDateString()}
                    </p>
                </div>

                <div className="max-w-2xl">
                    <DailyLogForm
                        batchId={batch.id}
                        dailyLog={dailyLog}
                        isEdit
                    />
                </div>
            </div>
        </AppLayout>
    );
}
