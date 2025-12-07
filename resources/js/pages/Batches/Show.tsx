import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { FieldLayout } from '@/layouts/app/field-layout';
import { Head, Link } from '@inertiajs/react';
import { create } from '@/actions/App/Http/Controllers/Batches/DailyLogController';
import { index } from '@/actions/App/Http/Controllers/Batches/BatchController';
import {
    Activity,
    AlertTriangle,
    Bird,
    Calendar,
    ClipboardList,
    Droplets,
    Edit,
    Plus,
    Scale,
    Thermometer,
    TrendingUp,
    Utensils,
} from 'lucide-react';

interface DailyLogData {
    id: number;
    log_date: string;
    mortality_count: number;
    feed_consumed_kg: number;
    water_consumed_liters: number | null;
    temperature_celsius: number | null;
    humidity_percent: number | null;
    isEditable: boolean;
}

interface BatchStats {
    fcr: number | null;
    epef: number | null;
    mortalityRate: number;
    avgDailyGain: number | null;
    totalFeedConsumed: number;
}

interface BatchData {
    id: number;
    name: string;
    age_in_days: number;
    current_bird_count: number;
    initial_bird_count: number;
    status: string;
    statusLabel: string;
    statusColor: string;
    start_date: string;
    target_weight_kg: number;
    supplier: { name: string } | null;
}

interface Props {
    batch: BatchData;
    stats: BatchStats;
    dailyLogs: DailyLogData[];
}

function StatCard({
    icon: Icon,
    label,
    value,
    unit,
    alert,
}: {
    icon: React.ElementType;
    label: string;
    value: string | number | null;
    unit?: string;
    alert?: boolean;
}) {
    return (
        <div className="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
            <div className={`rounded-full p-2 ${alert ? 'bg-red-100 dark:bg-red-900/30' : 'bg-gray-100 dark:bg-gray-800'}`}>
                <Icon className={`h-4 w-4 ${alert ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400'}`} />
            </div>
            <div className="flex-1 min-w-0">
                <p className="text-xs text-gray-500 dark:text-gray-400">{label}</p>
                <p className={`font-semibold truncate ${alert ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100'}`}>
                    {value ?? '-'}{unit && value !== null ? ` ${unit}` : ''}
                </p>
            </div>
        </div>
    );
}

function LogEntry({ log, batchId }: { log: DailyLogData; batchId: number }) {
    const logDate = new Date(log.log_date);

    return (
        <div className="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div className="flex items-center gap-4">
                <div className="text-center">
                    <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {logDate.getDate()}
                    </p>
                    <p className="text-xs text-gray-500 dark:text-gray-400">
                        {logDate.toLocaleDateString('en-US', { month: 'short' })}
                    </p>
                </div>
                <div className="h-12 w-px bg-gray-200 dark:bg-gray-700" />
                <div className="grid grid-cols-2 gap-x-6 gap-y-1 text-sm sm:grid-cols-4">
                    <div className="flex items-center gap-1.5">
                        <AlertTriangle className="h-3.5 w-3.5 text-gray-400" />
                        <span className="text-gray-600 dark:text-gray-400">{log.mortality_count}</span>
                    </div>
                    <div className="flex items-center gap-1.5">
                        <Utensils className="h-3.5 w-3.5 text-gray-400" />
                        <span className="text-gray-600 dark:text-gray-400">{log.feed_consumed_kg}kg</span>
                    </div>
                    {log.temperature_celsius && (
                        <div className="flex items-center gap-1.5">
                            <Thermometer className="h-3.5 w-3.5 text-gray-400" />
                            <span className="text-gray-600 dark:text-gray-400">{log.temperature_celsius}°C</span>
                        </div>
                    )}
                    {log.humidity_percent && (
                        <div className="flex items-center gap-1.5">
                            <Droplets className="h-3.5 w-3.5 text-gray-400" />
                            <span className="text-gray-600 dark:text-gray-400">{log.humidity_percent}%</span>
                        </div>
                    )}
                </div>
            </div>
            {log.isEditable && (
                <Link
                    href={`/batches/${batchId}/logs/${log.id}/edit`}
                    className="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                >
                    <Edit className="h-4 w-4" />
                </Link>
            )}
        </div>
    );
}

export default function Show({ batch, stats, dailyLogs }: Props) {
    const isHighMortality = stats.mortalityRate > 5;

    return (
        <FieldLayout
            title={batch.name}
            backHref={index.url()}
            backLabel="Batches"
        >
            <Head title={batch.name} />

            <div className="space-y-6">
                {/* Batch Header */}
                <Card>
                    <CardHeader className="pb-2">
                        <div className="flex items-start justify-between">
                            <div>
                                <CardTitle className="text-xl">{batch.name}</CardTitle>
                                <CardDescription className="flex items-center gap-2 mt-1">
                                    <Calendar className="h-3.5 w-3.5" />
                                    Started {new Date(batch.start_date).toLocaleDateString()}
                                    {batch.supplier && ` • ${batch.supplier.name}`}
                                </CardDescription>
                            </div>
                            <Badge>{batch.statusLabel}</Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <StatCard icon={Calendar} label="Age" value={batch.age_in_days} unit="days" />
                            <StatCard icon={Bird} label="Birds" value={batch.current_bird_count.toLocaleString()} />
                            <StatCard icon={TrendingUp} label="FCR" value={stats.fcr?.toFixed(2) ?? null} />
                            <StatCard
                                icon={Activity}
                                label="Mortality"
                                value={stats.mortalityRate.toFixed(1)}
                                unit="%"
                                alert={isHighMortality}
                            />
                        </div>
                    </CardContent>
                </Card>

                {/* Performance Stats */}
                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Performance</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <StatCard icon={Scale} label="Avg Daily Gain" value={stats.avgDailyGain?.toFixed(1) ?? null} unit="g" />
                            <StatCard icon={Utensils} label="Total Feed" value={Math.round(stats.totalFeedConsumed).toLocaleString()} unit="kg" />
                            <StatCard icon={TrendingUp} label="EPEF" value={stats.epef?.toFixed(0) ?? null} />
                        </div>
                    </CardContent>
                </Card>

                {/* Daily Logs */}
                <Card>
                    <CardHeader className="flex-row items-center justify-between space-y-0">
                        <div className="flex items-center gap-2">
                            <ClipboardList className="h-5 w-5 text-gray-500" />
                            <CardTitle className="text-base">Daily Logs</CardTitle>
                        </div>
                        {batch.status === 'active' && (
                            <Link href={create.url(batch.id)}>
                                <Button size="sm">
                                    <Plus className="h-4 w-4" />
                                    Add Log
                                </Button>
                            </Link>
                        )}
                    </CardHeader>
                    <CardContent>
                        {dailyLogs.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-8 text-center">
                                <ClipboardList className="h-10 w-10 text-gray-400" />
                                <p className="mt-3 text-sm text-gray-600 dark:text-gray-400">
                                    No daily logs recorded yet.
                                </p>
                                {batch.status === 'active' && (
                                    <Link href={create.url(batch.id)} className="mt-3">
                                        <Button variant="outline" size="sm">
                                            <Plus className="h-4 w-4" />
                                            Record First Log
                                        </Button>
                                    </Link>
                                )}
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {dailyLogs.map((log) => (
                                    <LogEntry key={log.id} log={log} batchId={batch.id} />
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </FieldLayout>
    );
}
