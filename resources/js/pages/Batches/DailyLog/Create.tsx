import { DailyLogForm, type LastLogData } from '@/components/broiler/DailyLogForm';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { index } from '@/actions/App/Http/Controllers/Batches/BatchController';
import { show } from '@/actions/App/Http/Controllers/Batches/BatchController';

interface BatchData {
    id: number;
    name: string;
    age_in_days: number;
    current_bird_count: number;
    status: string;
    statusLabel: string;
}

interface Props {
    batch: BatchData;
    lastLog: LastLogData | null;
    suggestedDate: string;
}

export default function Create({ batch, lastLog, suggestedDate }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Batches', href: index.url() },
        { title: batch.name, href: show.url(batch.id) },
        { title: 'Record Daily Log', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Daily Log - ${batch.name}`} />

            <div className="px-4 py-6">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
                        Record Daily Log
                    </h1>
                    <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {batch.name} • Day {batch.age_in_days} • {batch.current_bird_count.toLocaleString()} birds
                    </p>
                </div>

                <div className="max-w-2xl">
                    <DailyLogForm
                        batchId={batch.id}
                        lastLog={lastLog}
                        suggestedDate={suggestedDate}
                    />
                </div>
            </div>
        </AppLayout>
    );
}
