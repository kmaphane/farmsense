import { Activity, Bird, Scale, TrendingUp, Utensils } from 'lucide-react';
import * as React from 'react';

interface StatCardProps {
    icon: React.ElementType;
    label: string;
    value: string | number | null;
    unit?: string;
    alert?: boolean;
}

export function StatCard({
    icon: Icon,
    label,
    value,
    unit,
    alert,
}: StatCardProps) {
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

interface MetricsDisplayProps {
    fcr: number | null;
    epef: number | null;
    mortalityRate: number;
    avgDailyGain: number | null;
    totalFeedConsumed: number;
    currentBirdCount: number;
    ageInDays: number;
}

export function MetricsDisplay({
    fcr,
    epef,
    mortalityRate,
    avgDailyGain,
    totalFeedConsumed,
    currentBirdCount,
    ageInDays,
}: MetricsDisplayProps) {
    const isHighMortality = mortalityRate > 5;
    const isBadFCR = fcr !== null && fcr > 2.0;
    const isLowEPEF = epef !== null && epef < 300;

    return (
        <div className="space-y-4">
            {/* Primary Metrics */}
            <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <StatCard
                    icon={TrendingUp}
                    label="FCR"
                    value={fcr?.toFixed(2) ?? null}
                    alert={isBadFCR}
                />
                <StatCard
                    icon={Activity}
                    label="EPEF"
                    value={epef?.toFixed(0) ?? null}
                    alert={isLowEPEF}
                />
                <StatCard
                    icon={Activity}
                    label="Mortality"
                    value={mortalityRate.toFixed(1)}
                    unit="%"
                    alert={isHighMortality}
                />
                <StatCard
                    icon={Scale}
                    label="Avg Daily Gain"
                    value={avgDailyGain?.toFixed(1) ?? null}
                    unit="g"
                />
            </div>

            {/* Secondary Metrics */}
            <div className="grid grid-cols-3 gap-3">
                <StatCard
                    icon={Utensils}
                    label="Total Feed"
                    value={Math.round(totalFeedConsumed).toLocaleString()}
                    unit="kg"
                />
                <StatCard
                    icon={Bird}
                    label="Birds"
                    value={currentBirdCount.toLocaleString()}
                />
                <StatCard
                    icon={Activity}
                    label="Age"
                    value={ageInDays}
                    unit="days"
                />
            </div>

            {/* Metric Explanations */}
            <div className="space-y-1 text-xs text-gray-500 dark:text-gray-400">
                <p>
                    <strong>FCR</strong> (Feed Conversion Ratio): Total feed ÷
                    weight gain. Lower is better (target: 1.6-1.9)
                </p>
                <p>
                    <strong>EPEF</strong> (European Production Efficiency
                    Factor): Higher is better (target: 300-400)
                </p>
            </div>
        </div>
    );
}
