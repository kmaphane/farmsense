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
import { AlertCircle, Bird, Calendar, Eye, Package } from 'lucide-react';

interface SlaughterRecord {
    id: number;
    slaughter_date: string;
    slaughter_date_formatted: string;
    total_birds_slaughtered: number;
    batches_count: number;
    batches_names: string;
    yields_count: number;
    has_discrepancies: boolean;
    recorded_by: string;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    slaughterRecords: SlaughterRecord[];
    pagination: Pagination;
}

export default function SlaughterIndex({
    slaughterRecords,
    pagination,
}: Props) {
    const handlePageChange = (page: number) => {
        router.get(route('slaughter.index'), { page }, { preserveState: true });
    };

    // Ensure slaughterRecords is an array
    const recordList = Array.isArray(slaughterRecords) ? slaughterRecords : [];

    return (
        <AppLayout>
            <Head title="Slaughter Records" />

            <div className="mx-auto max-w-7xl space-y-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            Slaughter Records
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            View all slaughter sessions and their yields
                        </p>
                    </div>
                </div>

                {/* Stats */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total Records
                            </CardTitle>
                            <Package className="h-4 w-4 text-muted-foreground" />
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
                                Total Birds
                            </CardTitle>
                            <Bird className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {recordList
                                    .reduce(
                                        (sum, r) =>
                                            sum + r.total_birds_slaughtered,
                                        0,
                                    )
                                    .toLocaleString()}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                With Discrepancies
                            </CardTitle>
                            <AlertCircle className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {
                                    recordList.filter(
                                        (r) => r.has_discrepancies,
                                    ).length
                                }
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Slaughter Sessions</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {recordList.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <Package className="mb-4 h-12 w-12 text-muted-foreground/50" />
                                <h3 className="mb-2 text-lg font-semibold">
                                    No slaughter records yet
                                </h3>
                                <p className="max-w-sm text-sm text-muted-foreground">
                                    Slaughter records will appear here after you
                                    process birds through the Quick Actions
                                    menu.
                                </p>
                            </div>
                        ) : (
                            <>
                                <div className="overflow-x-auto">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Birds</TableHead>
                                                <TableHead>Batches</TableHead>
                                                <TableHead>Yields</TableHead>
                                                <TableHead>Status</TableHead>
                                                <TableHead>
                                                    Recorded By
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    Actions
                                                </TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {recordList.map((record) => (
                                                <TableRow
                                                    key={record.id}
                                                    className="cursor-pointer hover:bg-muted/50"
                                                >
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'slaughter.show',
                                                                    record.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <div className="flex items-center gap-2">
                                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                                            <span className="font-medium">
                                                                {
                                                                    record.slaughter_date_formatted
                                                                }
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'slaughter.show',
                                                                    record.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <div className="flex items-center gap-2">
                                                            <Bird className="h-4 w-4 text-muted-foreground" />
                                                            <span>
                                                                {record.total_birds_slaughtered.toLocaleString()}
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'slaughter.show',
                                                                    record.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <div className="flex flex-col">
                                                            <span className="font-medium">
                                                                {
                                                                    record.batches_count
                                                                }{' '}
                                                                batch
                                                                {record.batches_count !==
                                                                1
                                                                    ? 'es'
                                                                    : ''}
                                                            </span>
                                                            <span className="max-w-[200px] truncate text-xs text-muted-foreground">
                                                                {
                                                                    record.batches_names
                                                                }
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'slaughter.show',
                                                                    record.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <span>
                                                            {
                                                                record.yields_count
                                                            }{' '}
                                                            products
                                                        </span>
                                                    </TableCell>
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'slaughter.show',
                                                                    record.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        {record.has_discrepancies ? (
                                                            <Badge
                                                                variant="destructive"
                                                                className="gap-1"
                                                            >
                                                                <AlertCircle className="h-3 w-3" />
                                                                Discrepancy
                                                            </Badge>
                                                        ) : (
                                                            <Badge variant="secondary">
                                                                Normal
                                                            </Badge>
                                                        )}
                                                    </TableCell>
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'slaughter.show',
                                                                    record.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <span className="text-sm text-muted-foreground">
                                                            {record.recorded_by}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'slaughter.show',
                                                                        record.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            <Eye className="mr-1 h-4 w-4" />
                                                            View
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

SlaughterIndex.layout = (page: React.ReactNode) => page;
