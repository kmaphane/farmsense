import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { useEffect, useState } from 'react';

interface ChartData {
    labels: string[];
    datasets: Array<{
        label: string;
        data: number[];
    }>;
}

interface FeedConsumptionChartProps {
    batchId: number;
    apiUrl?: string;
}

export function FeedConsumptionChart({
    batchId,
    apiUrl,
}: FeedConsumptionChartProps) {
    const [data, setData] = useState<ChartData | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const url = apiUrl ?? `/batches/${batchId}/analytics/feed`;
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error('Failed to fetch feed data');
                }
                const chartData = await response.json();
                setData(chartData);
            } catch (err) {
                setError(err instanceof Error ? err.message : 'Unknown error');
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, [batchId, apiUrl]);

    if (loading) {
        return (
            <Card>
                <CardHeader>
                    <CardTitle className="text-base">
                        Feed Consumption
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <Skeleton className="h-48 w-full" />
                </CardContent>
            </Card>
        );
    }

    if (error || !data) {
        return (
            <Card>
                <CardHeader>
                    <CardTitle className="text-base">
                        Feed Consumption
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p className="text-sm text-gray-500">
                        Unable to load feed data
                    </p>
                </CardContent>
            </Card>
        );
    }

    const dailyData =
        data.datasets.find((d) => d.label === 'Daily Feed (kg)')?.data ?? [];
    const cumulativeData =
        data.datasets.find((d) => d.label === 'Cumulative Feed (kg)')?.data ??
        [];
    const maxDaily = Math.max(...dailyData, 1);
    const totalFeed = cumulativeData[cumulativeData.length - 1] ?? 0;

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-base">Feed Consumption</CardTitle>
            </CardHeader>
            <CardContent>
                {data.labels.length === 0 ? (
                    <p className="py-8 text-center text-sm text-gray-500">
                        No feed data available yet
                    </p>
                ) : (
                    <div className="space-y-4">
                        {/* Simple Bar Chart */}
                        <div className="space-y-2">
                            <p className="text-xs text-gray-500 dark:text-gray-400">
                                Daily Feed (kg)
                            </p>
                            <div className="flex h-24 items-end gap-1">
                                {dailyData.map((value, index) => {
                                    const height = (value / maxDaily) * 100;
                                    return (
                                        <div
                                            key={index}
                                            className="flex flex-1 flex-col items-center justify-end"
                                            title={`${data.labels[index]}: ${value}kg`}
                                        >
                                            <div
                                                className="w-full rounded-t bg-green-500"
                                                style={{
                                                    height: `${Math.max(height, 2)}%`,
                                                }}
                                            />
                                        </div>
                                    );
                                })}
                            </div>
                            <div className="flex justify-between text-xs text-gray-400">
                                <span>{data.labels[0]}</span>
                                <span>
                                    {data.labels[data.labels.length - 1]}
                                </span>
                            </div>
                        </div>

                        {/* Summary Stats */}
                        <div className="grid grid-cols-2 gap-4 border-t border-gray-200 pt-2 dark:border-gray-800">
                            <div>
                                <p className="text-xs text-gray-500 dark:text-gray-400">
                                    Total Consumed
                                </p>
                                <p className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {totalFeed.toLocaleString()} kg
                                </p>
                            </div>
                            <div>
                                <p className="text-xs text-gray-500 dark:text-gray-400">
                                    Avg Daily
                                </p>
                                <p className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {dailyData.length > 0
                                        ? (
                                              dailyData.reduce(
                                                  (a, b) => a + b,
                                                  0,
                                              ) / dailyData.length
                                          ).toFixed(1)
                                        : 0}{' '}
                                    kg
                                </p>
                            </div>
                        </div>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
