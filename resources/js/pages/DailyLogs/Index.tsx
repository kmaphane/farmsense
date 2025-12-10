import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { Calendar, ClipboardList, Eye, Skull, Utensils } from 'lucide-react';

interface DailyLog {
    id: number;
    log_date: string;
    batch_name: string;
    batch_id: number;
    mortality_count: number;
    feed_consumed_kg: number;
    water_consumed_liters: number | null;
    temperature_celsius: number | null;
    recorded_by: string;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    dailyLogs: DailyLog[];
    pagination: Pagination;
}

export default function DailyLogsIndex({ dailyLogs, pagination }: Props) {
    const handlePageChange = (page: number) => {
        router.get(
            route('daily-logs.index'),
            { page },
            { preserveState: true },
        );
    };

    // Ensure dailyLogs is an array
    const logList = Array.isArray(dailyLogs) ? dailyLogs : [];
    const totalMortality = logList.reduce(
        (sum, log) => sum + log.mortality_count,
        0,
    );
    const totalFeed = logList.reduce(
        (sum, log) => sum + log.feed_consumed_kg,
        0,
    );

    return (
        <AppLayout>
            <Head title="Daily Logs" />

            <div className="mx-auto max-w-7xl space-y-6 p-6">
                {/* Header */}
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">
                        Daily Logs
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        View all daily log entries across all batches
                    </p>
                </div>

                {/* Stats */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total Entries
                            </CardTitle>
                            <ClipboardList className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {pagination.total}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total Mortality (Current Page)
                            </CardTitle>
                            <Skull className="h-4 w-4 text-red-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {totalMortality}
                            </div>
                            <p className="mt-1 text-xs text-muted-foreground">
                                birds across visible logs
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total Feed (Current Page)
                            </CardTitle>
                            <Utensils className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {totalFeed.toFixed(1)} kg
                            </div>
                            <p className="mt-1 text-xs text-muted-foreground">
                                consumed across visible logs
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Daily Log Entries</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {logList.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <ClipboardList className="mb-4 h-12 w-12 text-muted-foreground/50" />
                                <h3 className="mb-2 text-lg font-semibold">
                                    No daily logs yet
                                </h3>
                                <p className="max-w-sm text-sm text-muted-foreground">
                                    Daily logs will appear here after field
                                    workers record batch data.
                                </p>
                            </div>
                        ) : (
                            <>
                                <div className="overflow-x-auto">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Batch</TableHead>
                                                <TableHead className="text-right">
                                                    Mortality
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    Feed (kg)
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    Water (L)
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    Temp (°C)
                                                </TableHead>
                                                <TableHead>
                                                    Recorded By
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    Actions
                                                </TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {logList.map((log) => (
                                                <TableRow
                                                    key={log.id}
                                                    className="cursor-pointer hover:bg-muted/50"
                                                >
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'batches.show',
                                                                    log.batch_id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <div className="flex items-center gap-2">
                                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                                            <span className="font-medium">
                                                                {log.log_date}
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'batches.show',
                                                                    log.batch_id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <Badge variant="outline">
                                                            {log.batch_name}
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell
                                                        className="text-right"
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'batches.show',
                                                                    log.batch_id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <span
                                                            className={
                                                                log.mortality_count >
                                                                0
                                                                    ? 'font-medium text-red-600'
                                                                    : ''
                                                            }
                                                        >
                                                            {
                                                                log.mortality_count
                                                            }
                                                        </span>
                                                    </TableCell>
                                                    <TableCell
                                                        className="text-right"
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'batches.show',
                                                                    log.batch_id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <span className="text-sm font-medium">
                                                            {log.feed_consumed_kg.toFixed(
                                                                1,
                                                            )}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell
                                                        className="text-right"
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'batches.show',
                                                                    log.batch_id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        {log.water_consumed_liters ? (
                                                            <span className="text-sm">
                                                                {log.water_consumed_liters.toFixed(
                                                                    1,
                                                                )}
                                                            </span>
                                                        ) : (
                                                            <span className="text-sm text-muted-foreground">
                                                                —
                                                            </span>
                                                        )}
                                                    </TableCell>
                                                    <TableCell
                                                        className="text-right"
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'batches.show',
                                                                    log.batch_id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        {log.temperature_celsius ? (
                                                            <span className="text-sm">
                                                                {log.temperature_celsius.toFixed(
                                                                    1,
                                                                )}
                                                            </span>
                                                        ) : (
                                                            <span className="text-sm text-muted-foreground">
                                                                —
                                                            </span>
                                                        )}
                                                    </TableCell>
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'batches.show',
                                                                    log.batch_id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <span className="text-sm text-muted-foreground">
                                                            {log.recorded_by}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'batches.show',
                                                                        log.batch_id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            <Eye className="mr-1 h-4 w-4" />
                                                            View Batch
                                                        </Button>
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>

                                {/* Pagination */}
                                {pagination.last_page > 1 && (
                                    <div className="mt-4 flex items-center justify-between border-t pt-4">
                                        <div className="text-sm text-muted-foreground">
                                            Showing page{' '}
                                            {pagination.current_page} of{' '}
                                            {pagination.last_page}(
                                            {pagination.total} total records)
                                        </div>
                                        <div className="flex gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                disabled={
                                                    pagination.current_page ===
                                                    1
                                                }
                                                onClick={() =>
                                                    handlePageChange(
                                                        pagination.current_page -
                                                            1,
                                                    )
                                                }
                                            >
                                                Previous
                                            </Button>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                disabled={
                                                    pagination.current_page ===
                                                    pagination.last_page
                                                }
                                                onClick={() =>
                                                    handlePageChange(
                                                        pagination.current_page +
                                                            1,
                                                    )
                                                }
                                            >
                                                Next
                                            </Button>
                                        </div>
                                    </div>
                                )}
                            </>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

DailyLogsIndex.layout = (page: React.ReactNode) => page;
