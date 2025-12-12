import { BatchCard, type BatchCardData } from '@/components/broiler/BatchCard';
import {
    CashflowChartWidget,
    CashflowSummaryCards,
} from '@/components/dashboard/CashflowChartWidget';
import { CollapsibleSection } from '@/components/dashboard/CollapsibleSection';
import { LowStockAlertWidget } from '@/components/dashboard/LowStockAlertWidget';
import { PlannedBatchTimelineWidget } from '@/components/dashboard/PlannedBatchTimelineWidget';
import { ProductionStats } from '@/components/dashboard/ProductionStats';
import {
    StockBreakdown,
    StockSummary,
} from '@/components/dashboard/StockOverviewWidget';
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
import { Bird } from 'lucide-react';

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
                <CollapsibleSection
                    title="Cash Flow"
                    summary={<CashflowSummaryCards history={cashflowHistory} />}
                >
                    {/* 7-Day Cash Flow Tracker */}
                    <div className="mb-4">
                        <CashflowChartWidget history={cashflowHistory} />
                    </div>
                </CollapsibleSection>

                {/* Zone B: Processed Stock Breakdown */}
                <CollapsibleSection
                    title="Stock & Inventory"
                    summary={
                        <StockSummary
                            stockValue={cashflow.stockValue}
                            carcassPrice={cashflow.carcassPrice}
                        />
                    }
                >
                    <div className="space-y-4">
                        <StockSummary
                            stockValue={cashflow.stockValue}
                            carcassPrice={cashflow.carcassPrice}
                        />
                        <StockBreakdown
                            processedProducts={cashflow.processedProducts}
                        />
                    </div>
                </CollapsibleSection>

                {/* Zone C: Live Pulse (Production) */}
                <CollapsibleSection
                    title="Production Pulse"
                    summary={<ProductionStats stats={stats} />}
                >
                    <div className="space-y-4">
                        <ProductionStats stats={stats} />
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
                    </div>
                </CollapsibleSection>

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
