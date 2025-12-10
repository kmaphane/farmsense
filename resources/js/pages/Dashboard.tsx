import { BatchCard, type BatchCardData } from '@/components/broiler/BatchCard';
import { CashflowChartWidget } from '@/components/dashboard/CashflowChartWidget';
import { LowStockAlertWidget } from '@/components/dashboard/LowStockAlertWidget';
import { PlannedBatchTimelineWidget } from '@/components/dashboard/PlannedBatchTimelineWidget';
import { StockOverviewWidget } from '@/components/dashboard/StockOverviewWidget';
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

interface ProcessedProduct {
    name: string;
    type: string;
    quantity: number;
    value: number;
}

interface CashflowMetrics {
    stockValue: number;
    monthlySales: number;
    carcassPrice: number | null;
    processedProducts: ProcessedProduct[];
}

interface DailyCashflow {
    date: string;
    date_label: string;
    cash_in: number;
    cash_out: number;
    net: number;
}

interface CashflowHistory {
    daily: DailyCashflow[];
    totals: {
        cash_in: number;
        cash_out: number;
        net: number;
    };
    period: {
        start: string;
        end: string;
        days: number;
    };
}

interface LowStockAlert {
    id: number;
    name: string;
    type: string;
    type_label: string;
    quantity_on_hand: number;
    reorder_level: number;
    unit: string;
    days_remaining: number | null;
    is_critical: boolean;
}

interface PlannedBatch {
    id: number;
    name: string;
    batch_number: string;
    start_date: string;
    days_until_start: number;
    initial_quantity: number;
    supplier_name: string | null;
    estimated_feed_cost: number;
    status_color: 'red' | 'yellow' | 'green';
}

interface Props {
    stats: DashboardStats;
    recentBatches: BatchCardData[];
    cashflow: CashflowMetrics;
    cashflowHistory: CashflowHistory;
    lowStockAlerts: LowStockAlert[];
    plannedBatches: PlannedBatch[];
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

export default function Dashboard({
    stats,
    recentBatches,
    cashflow,
    cashflowHistory,
    lowStockAlerts,
    plannedBatches,
}: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                {/* Zone A: Cashflow Snapshot */}
                <section>
                    <h2 className="mb-3 flex items-center gap-2 border-l-4 border-yellow-500 pl-3 text-lg font-bold uppercase tracking-wide text-yellow-600 dark:text-yellow-400">
                        Cash Flow
                    </h2>

                    {/* 7-Day Cash Flow Tracker */}
                    <div className="mb-4">
                        <CashflowChartWidget history={cashflowHistory} />
                    </div>
                </section>

                {/* Zone B: Processed Stock Breakdown */}
                <section>
                    <h2 className="mb-3 flex items-center gap-2 border-l-4 border-yellow-500 pl-3 text-lg font-bold uppercase tracking-wide text-yellow-600 dark:text-yellow-400">
                        Stock & Inventory
                    </h2>
                     <StockOverviewWidget
                        stockValue={cashflow.stockValue}
                        carcassPrice={cashflow.carcassPrice}
                        processedProducts={cashflow.processedProducts}
                    />
                </section>

                {/* Zone C: Live Pulse (Production) */}
                <section>
                    <h2 className="mb-3 flex items-center gap-2 border-l-4 border-yellow-500 pl-3 text-lg font-bold uppercase tracking-wide text-yellow-600 dark:text-yellow-400">
                        Production Pulse
                    </h2>

                    {/* Quick Stats */}
                    <div className="mb-4 grid gap-4 md:grid-cols-4">
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

                    {/* Additional Quick Stats */}
                    <div className="mb-4 grid gap-4 md:grid-cols-2">
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

                    {/* Active Batches Display */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle>Active Batches</CardTitle>
                                    <CardDescription>
                                        Your broiler batches currently in
                                        production
                                    </CardDescription>
                                </div>
                                <Link
                                    href="/batches"
                                    className="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
                                >
                                    View All
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
                                        Create a new batch to get started.
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
                </section>

                {/* Zone C & D: Inventory & Planning */}
                <section className="grid gap-6 lg:grid-cols-2">
                    {/* Zone C: Stockpile (Inventory) */}
                    <div>
                        <h2 className="mb-3 text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Inventory Status
                        </h2>
                        <LowStockAlertWidget alerts={lowStockAlerts} />
                    </div>

                    {/* Zone D: Horizon (Planning & Budget) */}
                    <div>
                        <h2 className="mb-3 text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Planning & Budget
                        </h2>
                        <PlannedBatchTimelineWidget batches={plannedBatches} />
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}
