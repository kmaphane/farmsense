import { Card } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import {
    Activity,
    AlertTriangle,
    Bird,
    LucideIcon,
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

interface ProductionStatCardProps {
    title: string;
    value: string | number;
    description?: string;
    icon: LucideIcon;
    alert?: boolean;
    variant?: 'default' | 'brand' | 'success' | 'warning' | 'danger' | 'info';
}

function ProductionStatCard({
    title,
    value,
    description,
    icon: Icon,
    alert,
    variant = 'brand', // Default to brand as requested for this section header
}: ProductionStatCardProps) {
    // Determine the class based on variant or alert override
    const metricClass = alert ? 'card-metric-danger' : `card-metric-${variant}`;

    return (
        <div className={cn('card-metric', metricClass)}>
            <div className="flex flex-row items-center justify-between space-y-0 p-6 pb-2">
                <span className="text-sm font-medium label">{title}</span>
                <Icon className="h-4 w-4 icon" />
            </div>
            <div className="p-6 pt-0">
                <div className="text-2xl font-bold">{value}</div>
                {description && (
                    <p className="mt-1 text-xs opacity-80">{description}</p>
                )}
            </div>
        </div>
    );
}

export function ProductionStats({ stats }: { stats: DashboardStats }) {
    return (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            {/* Active Batches */}
            <ProductionStatCard
                title="Active Batches"
                value={stats.activeBatches}
                description="Currently running"
                icon={Bird}
            />

            {/* Total Birds */}
            <ProductionStatCard
                title="Total Birds"
                value={stats.totalBirds.toLocaleString()}
                description="Across all batches"
                icon={Bird}
            />

            {/* Average FCR */}
            <ProductionStatCard
                title="Average FCR"
                value={stats.avgFCR?.toFixed(2) ?? '-'}
                description="Feed Conversion Ratio"
                icon={Utensils}
                variant="info" // Maybe info for less critical technical stats? Or keep brand? Let's try info to break monotony if brand is too much. Actually, stick to Brand for consistency with section header unless it's a specific 'health' metric.
                // Let's stick to 'brand' to match the user's preference for the header color association, or use 'info' for neutral.
                // Given the header is 'yellow' (brand), sticking to brand is safe.
            />

            {/* Avg Mortality Rate */}
            <ProductionStatCard
                title="Avg Mortality Rate"
                value={`${stats.avgMortalityRate.toFixed(1)}%`}
                description={
                    stats.avgMortalityRate > 5 ? 'Above target' : 'Within target'
                }
                icon={Activity}
                alert={stats.avgMortalityRate > 5}
                variant="brand" // Will be overridden by alert if true
            />

            {/* Today's Logs */}
            <ProductionStatCard
                title="Today's Logs"
                value={stats.todayLogs}
                description="Daily logs recorded today"
                icon={TrendingUp}
            />

            {/* Pending Alerts */}
            <ProductionStatCard
                title="Pending Alerts"
                value={stats.pendingAlerts}
                description={
                    stats.pendingAlerts > 0 ? 'Requires attention' : 'All clear'
                }
                icon={AlertTriangle}
                alert={stats.pendingAlerts > 0}
            />
        </div>
    );
}
