import { BatchCard, type BatchCardData } from '@/components/broiler/BatchCard';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import {
    Activity,
    AlertTriangle,
    Bird,
    TrendingUp,
    Utensils,
} from 'lucide-react';

interface DashboardStats {
    activeBatches: number;
    totalBirds: number;
    avgFCR: number | null;
    avgMortalityRate: number;
    todayLogs: number;
    pendingAlerts: number;
}

interface Props {
    stats: DashboardStats;
    recentBatches: BatchCardData[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

function StatCard({
    title,
    value,
    description,
    icon: Icon,
    alert,
}: {
    title: string;
    value: string | number;
    description?: string;
    icon: React.ElementType;
    alert?: boolean;
}) {
    return (
        <Card className={alert ? 'border-red-200 dark:border-red-900' : ''}>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">{title}</CardTitle>
                <Icon
                    className={`h-4 w-4 ${alert ? 'text-red-500' : 'text-muted-foreground'}`}
                />
            </CardHeader>
            <CardContent>
                <div
                    className={`text-2xl font-bold ${alert ? 'text-red-600 dark:text-red-400' : ''}`}
                >
                    {value}
                </div>
                {description && (
                    <p className="text-xs text-muted-foreground">
                        {description}
                    </p>
                )}
            </CardContent>
        </Card>
    );
}

export default function Dashboard({ stats, recentBatches }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {/* Stats Grid */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <StatCard
                        title="Active Batches"
                        value={stats.activeBatches}
                        description="Currently running"
                        icon={Bird}
                    />
                    <StatCard
                        title="Total Birds"
                        value={stats.totalBirds.toLocaleString()}
                        description="Across all batches"
                        icon={Bird}
                    />
                    <StatCard
                        title="Average FCR"
                        value={stats.avgFCR?.toFixed(2) ?? '-'}
                        description="Feed Conversion Ratio"
                        icon={Utensils}
                    />
                    <StatCard
                        title="Avg Mortality Rate"
                        value={`${stats.avgMortalityRate.toFixed(1)}%`}
                        description={
                            stats.avgMortalityRate > 5
                                ? 'Above target'
                                : 'Within target'
                        }
                        icon={Activity}
                        alert={stats.avgMortalityRate > 5}
                    />
                </div>

                {/* Quick Stats Row */}
                <div className="grid gap-4 md:grid-cols-2">
                    <StatCard
                        title="Today's Logs"
                        value={stats.todayLogs}
                        description="Daily logs recorded today"
                        icon={TrendingUp}
                    />
                    <StatCard
                        title="Pending Alerts"
                        value={stats.pendingAlerts}
                        description={
                            stats.pendingAlerts > 0
                                ? 'Requires attention'
                                : 'All clear'
                        }
                        icon={AlertTriangle}
                        alert={stats.pendingAlerts > 0}
                    />
                </div>

                {/* Active Batches */}
                <Card className="flex-1">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle>Active Batches</CardTitle>
                                <CardDescription>
                                    Your broiler batches currently in production
                                </CardDescription>
                            </div>
                            <Link
                                href="/admin/batches/create"
                                className="rounded-md bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700"
                            >
                                + New Batch
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        {recentBatches.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12">
                                <Bird className="h-12 w-12 text-gray-400" />
                                <h3 className="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                                    No Active Batches
                                </h3>
                                <p className="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Create a new batch in the admin panel to get
                                    started.
                                </p>
                            </div>
                        ) : (
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                {recentBatches.map((batch) => (
                                    <BatchCard
                                        key={batch.id}
                                        batch={batch}
                                        variant="card"
                                    />
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
