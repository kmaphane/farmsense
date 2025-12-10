import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { ArrowDown, ArrowUp, TrendingUp } from 'lucide-react';

interface DailyCashflow {
    date: string;
    date_label: string;
    cash_in: number;
    cash_out: number;
    net: number;
}

interface CashflowTotals {
    cash_in: number;
    cash_out: number;
    net: number;
}

interface CashflowPeriod {
    start: string;
    end: string;
    days: number;
}

interface CashflowHistory {
    daily: DailyCashflow[];
    totals: CashflowTotals;
    period: CashflowPeriod;
}

interface Props {
    history: CashflowHistory;
}

function formatCurrency(cents: number): string {
    const absValue = Math.abs(cents) / 100;
    return `P${absValue.toLocaleString('en-BW', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

export function CashflowChartWidget({ history }: Props) {
    const maxValue = Math.max(
        ...history.daily.map((d) => Math.max(d.cash_in, d.cash_out)),
    );

    const getBarHeight = (value: number) => {
        if (maxValue === 0) return 0;
        return (value / maxValue) * 100;
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>Cash Flow Tracker</CardTitle>
                <CardDescription>
                    Last {history.period.days} days ({history.period.start} -{' '}
                    {history.period.end})
                </CardDescription>
            </CardHeader>
            <CardContent>
                {/* Summary Cards */}
                <div className="mb-6 grid gap-4 md:grid-cols-3">
                    <div className="rounded-lg border bg-green-50 p-3 dark:bg-green-950">
                        <div className="flex items-center gap-2">
                            <ArrowDown className="h-4 w-4 text-green-600 dark:text-green-400" />
                            <p className="text-xs font-medium text-green-700 dark:text-green-300">
                                Cash In
                            </p>
                        </div>
                        <p className="mt-1 text-lg font-bold text-green-800 dark:text-green-200">
                            {formatCurrency(history.totals.cash_in)}
                        </p>
                    </div>

                    <div className="rounded-lg border bg-red-50 p-3 dark:bg-red-950">
                        <div className="flex items-center gap-2">
                            <ArrowUp className="h-4 w-4 text-red-600 dark:text-red-400" />
                            <p className="text-xs font-medium text-red-700 dark:text-red-300">
                                Cash Out
                            </p>
                        </div>
                        <p className="mt-1 text-lg font-bold text-red-800 dark:text-red-200">
                            {formatCurrency(history.totals.cash_out)}
                        </p>
                    </div>

                    <div
                        className={`rounded-lg border p-3 ${
                            history.totals.net >= 0
                                ? 'bg-blue-50 dark:bg-blue-950'
                                : 'bg-orange-50 dark:bg-orange-950'
                        }`}
                    >
                        <div className="flex items-center gap-2">
                            <TrendingUp
                                className={`h-4 w-4 ${
                                    history.totals.net >= 0
                                        ? 'text-blue-600 dark:text-blue-400'
                                        : 'text-orange-600 dark:text-orange-400'
                                }`}
                            />
                            <p
                                className={`text-xs font-medium ${
                                    history.totals.net >= 0
                                        ? 'text-blue-700 dark:text-blue-300'
                                        : 'text-orange-700 dark:text-orange-300'
                                }`}
                            >
                                Net Flow
                            </p>
                        </div>
                        <p
                            className={`mt-1 text-lg font-bold ${
                                history.totals.net >= 0
                                    ? 'text-blue-800 dark:text-blue-200'
                                    : 'text-orange-800 dark:text-orange-200'
                            }`}
                        >
                            {history.totals.net >= 0 ? '+' : ''}
                            {formatCurrency(history.totals.net)}
                        </p>
                    </div>
                </div>

                {/* Bar Chart */}
                <div className="space-y-4">
                    <div className="flex h-48 items-end justify-between gap-2">
                        {history.daily.map((day) => (
                            <div
                                key={day.date}
                                className="flex flex-1 flex-col items-center gap-1"
                            >
                                {/* Bars */}
                                <div className="flex w-full items-end justify-center gap-0.5">
                                    {/* Cash In Bar */}
                                    <div
                                        className="w-1/2 rounded-t bg-green-500 dark:bg-green-600"
                                        style={{
                                            height: `${getBarHeight(day.cash_in)}%`,
                                            minHeight: day.cash_in > 0 ? '4px' : '0',
                                        }}
                                        title={`Cash In: ${formatCurrency(day.cash_in)}`}
                                    />
                                    {/* Cash Out Bar */}
                                    <div
                                        className="w-1/2 rounded-t bg-red-500 dark:bg-red-600"
                                        style={{
                                            height: `${getBarHeight(day.cash_out)}%`,
                                            minHeight: day.cash_out > 0 ? '4px' : '0',
                                        }}
                                        title={`Cash Out: ${formatCurrency(day.cash_out)}`}
                                    />
                                </div>

                                {/* Date Label */}
                                <p className="text-xs text-muted-foreground">
                                    {day.date_label}
                                </p>
                            </div>
                        ))}
                    </div>

                    {/* Legend */}
                    <div className="flex items-center justify-center gap-4 text-xs">
                        <div className="flex items-center gap-1.5">
                            <div className="h-3 w-3 rounded bg-green-500" />
                            <span>Cash In</span>
                        </div>
                        <div className="flex items-center gap-1.5">
                            <div className="h-3 w-3 rounded bg-red-500" />
                            <span>Cash Out</span>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
