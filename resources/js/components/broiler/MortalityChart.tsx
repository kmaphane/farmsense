import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import * as React from 'react';
import { useEffect, useState } from 'react';

interface ChartData {
    labels: string[];
    datasets: Array<{
        label: string;
        data: number[];
    }>;
}

interface MortalityChartProps {
    batchId: number;
    apiUrl?: string;
}

export function MortalityChart({ batchId, apiUrl }: MortalityChartProps) {
    const [data, setData] = useState<ChartData | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const url = apiUrl ?? `/batches/${batchId}/analytics/mortality`;
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error('Failed to fetch mortality data');
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
                    <CardTitle className="text-base">Mortality Trend</CardTitle>
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
                    <CardTitle className="text-base">Mortality Trend</CardTitle>
                </CardHeader>
                <CardContent>
                    <p className="text-sm text-gray-500">Unable to load mortality data</p>
                </CardContent>
            </Card>
        );
    }

    const dailyData = data.datasets.find(d => d.label === 'Daily Mortality')?.data ?? [];
    const rateData = data.datasets.find(d => d.label === 'Mortality Rate (%)')?.data ?? [];
    const maxDaily = Math.max(...dailyData, 1);

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-base">Mortality Trend</CardTitle>
            </CardHeader>
            <CardContent>
                {data.labels.length === 0 ? (
                    <p className="text-sm text-gray-500 text-center py-8">No mortality data available yet</p>
                ) : (
                    <div className="space-y-4">
                        {/* Simple Bar Chart */}
                        <div className="space-y-2">
                            <p className="text-xs text-gray-500 dark:text-gray-400">Daily Mortality</p>
                            <div className="flex items-end gap-1 h-24">
                                {dailyData.map((value, index) => {
                                    const height = (value / maxDaily) * 100;
                                    const isHigh = value > 10;
                                    return (
                                        <div
                                            key={index}
                                            className="flex-1 flex flex-col items-center justify-end"
                                            title={`${data.labels[index]}: ${value}`}
                                        >
                                            <div
                                                className={`w-full rounded-t ${isHigh ? 'bg-red-500' : 'bg-blue-500'}`}
                                                style={{ height: `${Math.max(height, 2)}%` }}
                                            />
                                        </div>
                                    );
                                })}
                            </div>
                            <div className="flex justify-between text-xs text-gray-400">
                                <span>{data.labels[0]}</span>
                                <span>{data.labels[data.labels.length - 1]}</span>
                            </div>
                        </div>

                        {/* Rate Summary */}
                        <div className="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-800">
                            <span className="text-sm text-gray-600 dark:text-gray-400">Current Rate:</span>
                            <span className={`text-sm font-semibold ${rateData[rateData.length - 1] > 5 ? 'text-red-600' : 'text-gray-900 dark:text-gray-100'}`}>
                                {rateData[rateData.length - 1]?.toFixed(2) ?? 0}%
                            </span>
                        </div>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
