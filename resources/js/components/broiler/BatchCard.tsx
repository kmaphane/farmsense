import { show } from '@/actions/App/Http/Controllers/Batches/BatchController';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Link } from '@inertiajs/react';
import {
    Activity,
    AlertTriangle,
    Bird,
    ChevronRight,
    TrendingUp,
} from 'lucide-react';

export interface BatchCardData {
    id: number;
    name: string;
    age_in_days: number;
    current_bird_count: number;
    status: string;
    statusLabel: string;
    statusColor?: string;
    fcr: number | null;
    mortality_rate: number;
}

interface BatchCardProps {
    batch: BatchCardData;
    showLink?: boolean;
    variant?: 'card' | 'list';
}

function getStatusVariant(
    status: string,
): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'active':
            return 'default';
        case 'harvesting':
            return 'secondary';
        case 'completed':
            return 'outline';
        default:
            return 'outline';
    }
}

export function BatchCard({
    batch,
    showLink = true,
    variant = 'card',
}: BatchCardProps) {
    const isAlert = batch.mortality_rate > 5;

    if (variant === 'list') {
        return (
            <Link
                href={show.url(batch.id)}
                className="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-4 transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
            >
                <div className="flex items-center gap-4">
                    <div
                        className={`rounded-full p-2 ${isAlert ? 'bg-red-100 dark:bg-red-900/30' : 'bg-green-100 dark:bg-green-900/30'}`}
                    >
                        <Bird
                            className={`h-5 w-5 ${isAlert ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'}`}
                        />
                    </div>
                    <div>
                        <p className="font-medium text-gray-900 dark:text-gray-100">
                            {batch.name}
                        </p>
                        <p className="text-sm text-gray-500 dark:text-gray-400">
                            Day {batch.age_in_days} •{' '}
                            {batch.current_bird_count.toLocaleString()} birds
                        </p>
                    </div>
                </div>
                <div className="flex items-center gap-4">
                    <div className="text-right">
                        <p className="text-sm font-medium text-gray-900 dark:text-gray-100">
                            FCR: {batch.fcr?.toFixed(2) ?? '-'}
                        </p>
                        <p
                            className={`text-xs ${isAlert ? 'text-red-600' : 'text-gray-500 dark:text-gray-400'}`}
                        >
                            Mortality: {batch.mortality_rate.toFixed(1)}%
                        </p>
                    </div>
                    <ChevronRight className="h-5 w-5 text-gray-400" />
                </div>
            </Link>
        );
    }

    const content = (
        <Card className={showLink ? 'transition-shadow hover:shadow-md' : ''}>
            <CardHeader className="pb-2">
                <div className="flex items-start justify-between">
                    <div className="space-y-1">
                        <CardTitle className="text-lg">{batch.name}</CardTitle>
                        <CardDescription>
                            Day {batch.age_in_days}
                        </CardDescription>
                    </div>
                    <Badge variant={getStatusVariant(batch.status)}>
                        {batch.statusLabel}
                    </Badge>
                </div>
            </CardHeader>
            <CardContent>
                <div className="grid grid-cols-3 gap-4 text-sm">
                    <div className="flex items-center gap-2">
                        <Bird className="h-4 w-4 text-gray-500" />
                        <div>
                            <p className="text-xs text-gray-500 dark:text-gray-400">
                                Birds
                            </p>
                            <p className="font-medium">
                                {batch.current_bird_count.toLocaleString()}
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <TrendingUp className="h-4 w-4 text-gray-500" />
                        <div>
                            <p className="text-xs text-gray-500 dark:text-gray-400">
                                FCR
                            </p>
                            <p className="font-medium">
                                {batch.fcr?.toFixed(2) ?? '-'}
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        {isAlert ? (
                            <AlertTriangle className="h-4 w-4 text-red-500" />
                        ) : (
                            <Activity className="h-4 w-4 text-gray-500" />
                        )}
                        <div>
                            <p className="text-xs text-gray-500 dark:text-gray-400">
                                Mortality
                            </p>
                            <p
                                className={`font-medium ${isAlert ? 'text-red-600' : ''}`}
                            >
                                {batch.mortality_rate.toFixed(1)}%
                            </p>
                        </div>
                    </div>
                </div>
                {showLink && (
                    <div className="mt-4 flex items-center justify-end text-sm text-gray-500">
                        <span>View details</span>
                        <ChevronRight className="h-4 w-4" />
                    </div>
                )}
            </CardContent>
        </Card>
    );

    if (showLink) {
        return (
            <Link href={show.url(batch.id)} className="block">
                {content}
            </Link>
        );
    }

    return content;
}

interface BatchListProps {
    batches: BatchCardData[];
    variant?: 'card' | 'list';
    emptyMessage?: string;
    emptyDescription?: string;
    showCreateButton?: boolean;
}

export function BatchList({
    batches,
    variant = 'card',
    emptyMessage = 'No Active Batches',
    emptyDescription = 'There are no active batches assigned to your team.',
    showCreateButton = false,
}: BatchListProps) {
    if (batches.length === 0) {
        return (
            <Card>
                <CardContent className="flex flex-col items-center justify-center py-12">
                    <Bird className="h-12 w-12 text-gray-400" />
                    <h3 className="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                        {emptyMessage}
                    </h3>
                    <p className="mt-2 text-center text-sm text-gray-500 dark:text-gray-400">
                        {emptyDescription}
                    </p>
                    {showCreateButton && (
                        <Link
                            href="/admin/batches/create"
                            className="mt-4 rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
                        >
                            Create Batch
                        </Link>
                    )}
                </CardContent>
            </Card>
        );
    }

    return (
        <div
            className={
                variant === 'card'
                    ? 'grid gap-4 md:grid-cols-2 lg:grid-cols-3'
                    : 'space-y-3'
            }
        >
            {batches.map((batch) => (
                <BatchCard key={batch.id} batch={batch} variant={variant} />
            ))}
        </div>
    );
}
