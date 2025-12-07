import {
    DailyLogCalendar,
    type DailyLogData,
} from '@/components/broiler/DailyLogCalendar';
import { DailyLogForm } from '@/components/broiler/DailyLogForm';
import { LiveSaleForm } from '@/components/broiler/LiveSaleForm';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Sheet,
    SheetBody,
    SheetContent,
    SheetHeader,
    SheetTrigger,
} from '@/components/ui/sheet';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import {
    Activity,
    Bird,
    Calendar,
    ClipboardList,
    DollarSign,
    Edit,
    Plus,
    Scale,
    Scissors,
    TrendingUp,
    Utensils,
} from 'lucide-react';
import * as React from 'react';

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

interface LastLogData {
    log_date: string;
    mortality_count: number;
    feed_consumed_kg: number;
    water_consumed_liters: number | null;
    temperature_celsius: number | null;
    humidity_percent: number | null;
}

interface LiveSaleData {
    canSell: boolean;
    liveBirdPrice: number | null;
    customers: { id: number; name: string }[];
}

interface Props {
    batch: BatchData;
    stats: BatchStats;
    dailyLogs: DailyLogData[];
    lastLog: LastLogData | null;
    suggestedDate: string;
    liveSale: LiveSaleData;
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
            <div
                className={`rounded-full p-2 ${alert ? 'bg-red-100 dark:bg-red-900/30' : 'bg-gray-100 dark:bg-gray-800'}`}
            >
                <Icon
                    className={`h-4 w-4 ${alert ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400'}`}
                />
            </div>
            <div className="min-w-0 flex-1">
                <p className="text-xs text-gray-500 dark:text-gray-400">
                    {label}
                </p>
                <p
                    className={`truncate font-semibold ${alert ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100'}`}
                >
                    {value ?? '-'}
                    {unit && value !== null ? ` ${unit}` : ''}
                </p>
            </div>
        </div>
    );
}

export default function Show({
    batch,
    stats,
    dailyLogs,
    lastLog,
    suggestedDate,
    liveSale,
}: Props) {
    const isHighMortality = stats.mortalityRate > 5;
    const [isCreateOpen, setIsCreateOpen] = React.useState(false);
    const [isLiveSaleOpen, setIsLiveSaleOpen] = React.useState(false);
    const [editingLog, setEditingLog] = React.useState<DailyLogData | null>(
        null,
    );

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Batches', href: '/batches' },
        { title: batch.name, href: `/batches/${batch.id}` },
    ];

    const handleEditLog = (log: DailyLogData) => {
        setEditingLog(log);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={batch.name} />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Batch Header */}
                <Card>
                    <CardHeader className="pb-2">
                        <div className="flex items-start justify-between">
                            <div>
                                <CardTitle className="text-xl">
                                    {batch.name}
                                </CardTitle>
                                <CardDescription className="mt-1 flex items-center gap-2">
                                    <Calendar className="h-3.5 w-3.5" />
                                    Started{' '}
                                    {new Date(
                                        batch.start_date,
                                    ).toLocaleDateString()}
                                    {batch.supplier &&
                                        ` • ${batch.supplier.name}`}
                                </CardDescription>
                            </div>
                            <Badge>{batch.statusLabel}</Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <StatCard
                                icon={Calendar}
                                label="Age"
                                value={batch.age_in_days}
                                unit="days"
                            />
                            <StatCard
                                icon={Bird}
                                label="Birds"
                                value={batch.current_bird_count.toLocaleString()}
                            />
                            <StatCard
                                icon={TrendingUp}
                                label="FCR"
                                value={stats.fcr?.toFixed(2) ?? null}
                            />
                            <StatCard
                                icon={Activity}
                                label="Mortality"
                                value={stats.mortalityRate.toFixed(1)}
                                unit="%"
                                alert={isHighMortality}
                            />
                        </div>

                        {/* Quick Actions */}
                        {(batch.status === 'active' ||
                            batch.status === 'harvesting') && (
                            <div className="mt-4 flex flex-wrap gap-2 border-t border-gray-200 pt-4 dark:border-gray-700">
                                {liveSale.canSell && (
                                    <Sheet
                                        open={isLiveSaleOpen}
                                        onOpenChange={setIsLiveSaleOpen}
                                    >
                                        <SheetTrigger asChild>
                                            <Button variant="outline" size="sm">
                                                <DollarSign className="mr-1.5 h-4 w-4" />
                                                Sell Live Birds
                                            </Button>
                                        </SheetTrigger>
                                        <SheetContent size="md">
                                            <SheetHeader
                                                title="Sell Live Birds"
                                                description={`${batch.name} • ${batch.current_bird_count.toLocaleString()} birds available`}
                                                icon={
                                                    <DollarSign className="h-5 w-5" />
                                                }
                                            />
                                            <SheetBody>
                                                <LiveSaleForm
                                                    batchId={batch.id}
                                                    currentBirdCount={
                                                        batch.current_bird_count
                                                    }
                                                    liveBirdPrice={
                                                        liveSale.liveBirdPrice
                                                    }
                                                    customers={
                                                        liveSale.customers
                                                    }
                                                    suggestedDate={
                                                        suggestedDate
                                                    }
                                                    compact
                                                />
                                            </SheetBody>
                                        </SheetContent>
                                    </Sheet>
                                )}
                                <Link href="/slaughter/create">
                                    <Button variant="outline" size="sm">
                                        <Scissors className="mr-1.5 h-4 w-4" />
                                        Process Slaughter
                                    </Button>
                                </Link>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Performance Stats */}
                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Performance</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <StatCard
                                icon={Scale}
                                label="Avg Daily Gain"
                                value={stats.avgDailyGain?.toFixed(1) ?? null}
                                unit="g"
                            />
                            <StatCard
                                icon={Utensils}
                                label="Total Feed"
                                value={Math.round(
                                    stats.totalFeedConsumed,
                                ).toLocaleString()}
                                unit="kg"
                            />
                            <StatCard
                                icon={TrendingUp}
                                label="EPEF"
                                value={stats.epef?.toFixed(0) ?? null}
                            />
                        </div>
                    </CardContent>
                </Card>

                {/* Daily Logs Calendar */}
                <Card>
                    <CardHeader className="flex-row items-center justify-between space-y-0">
                        <div className="flex items-center gap-2">
                            <ClipboardList className="h-5 w-5 text-gray-500" />
                            <CardTitle className="text-base">
                                Daily Logs
                            </CardTitle>
                            <span className="text-sm text-gray-500 dark:text-gray-400">
                                ({dailyLogs.length} logged)
                            </span>
                        </div>
                        {batch.status === 'active' && (
                            <Sheet
                                open={isCreateOpen}
                                onOpenChange={setIsCreateOpen}
                            >
                                <SheetTrigger>
                                    <Button size="sm">
                                        <Plus className="h-4 w-4" />
                                        Add Log
                                    </Button>
                                </SheetTrigger>
                                <SheetContent size="lg">
                                    <SheetHeader
                                        title="Record Daily Log"
                                        description={`${batch.name} • Day ${batch.age_in_days} • ${batch.current_bird_count.toLocaleString()} birds`}
                                        icon={
                                            <ClipboardList className="h-5 w-5" />
                                        }
                                    />
                                    <SheetBody>
                                        <DailyLogForm
                                            batchId={batch.id}
                                            lastLog={lastLog}
                                            suggestedDate={suggestedDate}
                                            compact
                                        />
                                    </SheetBody>
                                </SheetContent>
                            </Sheet>
                        )}
                    </CardHeader>
                    <CardContent>
                        <DailyLogCalendar
                            logs={dailyLogs}
                            batchStartDate={batch.start_date}
                            batchAgeInDays={batch.age_in_days}
                            onEditLog={handleEditLog}
                        />
                        {dailyLogs.length === 0 &&
                            batch.status === 'active' && (
                                <div className="mt-4 flex flex-col items-center justify-center border-t border-gray-200 py-4 dark:border-gray-700">
                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                        No daily logs recorded yet. Start by
                                        adding your first log.
                                    </p>
                                </div>
                            )}
                    </CardContent>
                </Card>

                {/* Edit Log Sheet */}
                <Sheet
                    open={!!editingLog}
                    onOpenChange={(open) => !open && setEditingLog(null)}
                >
                    <SheetContent size="lg">
                        <SheetHeader
                            title="Edit Daily Log"
                            description={
                                editingLog
                                    ? `Log for ${new Date(editingLog.log_date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}`
                                    : ''
                            }
                            icon={<Edit className="h-5 w-5" />}
                        />
                        <SheetBody>
                            {editingLog && (
                                <DailyLogForm
                                    batchId={batch.id}
                                    dailyLog={{
                                        ...editingLog,
                                        ammonia_ppm:
                                            editingLog.ammonia_ppm ?? null,
                                        notes: null,
                                    }}
                                    isEdit
                                    compact
                                />
                            )}
                        </SheetBody>
                    </SheetContent>
                </Sheet>
            </div>
        </AppLayout>
    );
}
