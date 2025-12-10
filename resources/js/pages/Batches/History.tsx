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
import { Calendar, Eye, Package, TrendingDown, TrendingUp } from 'lucide-react';

interface Batch {
    id: number;
    name: string;
    start_date: string;
    end_date: string | null;
    age_in_days: number;
    initial_quantity: number;
    current_quantity: number;
    status: string;
    statusLabel: string;
    statusColor: string;
    supplier_name: string | null;
    fcr: number | null;
    mortality_rate: number;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    batches: Batch[];
    pagination: Pagination;
}

const statusColorMap: Record<string, string> = {
    green: 'bg-green-100 text-green-800 border-green-200',
    blue: 'bg-blue-100 text-blue-800 border-blue-200',
    yellow: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    red: 'bg-red-100 text-red-800 border-red-200',
    gray: 'bg-gray-100 text-gray-800 border-gray-200',
};

export default function BatchesHistory({ batches, pagination }: Props) {
    const handlePageChange = (page: number) => {
        router.get(route('batches.history'), { page }, { preserveState: true });
    };

    // Ensure batches is an array
    const batchList = Array.isArray(batches) ? batches : [];
    const activeBatches = batchList.filter(
        (b) => b.status === 'active' || b.status === 'harvesting',
    );
    const closedBatches = batchList.filter((b) => b.status === 'closed');

    return (
        <AppLayout>
            <Head title="Batch History" />

            <div className="mx-auto max-w-7xl space-y-6 p-6">
                {/* Header */}
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">
                        Batch History
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        View all batch records and performance metrics
                    </p>
                </div>

                {/* Stats */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total Batches
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
                                Active Batches
                            </CardTitle>
                            <TrendingUp className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {activeBatches.length}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Closed Batches
                            </CardTitle>
                            <TrendingDown className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {closedBatches.length}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>All Batches</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {batchList.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <Package className="mb-4 h-12 w-12 text-muted-foreground/50" />
                                <h3 className="mb-2 text-lg font-semibold">
                                    No batches yet
                                </h3>
                                <p className="max-w-sm text-sm text-muted-foreground">
                                    Batch history will appear here after you
                                    create batches through Quick Actions.
                                </p>
                            </div>
                        ) : (
                            <>
                                <div className="overflow-x-auto">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>
                                                    Batch Name
                                                </TableHead>
                                                <TableHead>Status</TableHead>
                                                <TableHead>Started</TableHead>
                                                <TableHead>Age</TableHead>
                                                <TableHead className="text-right">
                                                    Birds
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    FCR
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    Mortality
                                                </TableHead>
                                                <TableHead>Supplier</TableHead>
                                                <TableHead className="text-right">
                                                    Actions
                                                </TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {batchList.map((batch) => (
                                                <TableRow
                                                    key={batch.id}
                                                    className="cursor-pointer hover:bg-muted/50"
                                                >
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'batches.show',
                                                                    batch.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <div className="flex items-center gap-2">
                                                            <Package className="h-4 w-4 text-muted-foreground" />
                                                            <span className="font-medium">
                                                                {batch.name}
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'batches.show',
                                                                    batch.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <Badge
                                                            className={
                                                                statusColorMap[
                                                                    batch
                                                                        .statusColor
                                                                ] ||
                                                                statusColorMap.gray
                                                            }
                                                            variant="outline"
                                                        >
                                                            {batch.statusLabel}
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'batches.show',
                                                                    batch.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <div className="flex items-center gap-1">
                                                            <Calendar className="h-3 w-3 text-muted-foreground" />
                                                            <span className="text-sm">
                                                                {
                                                                    batch.start_date
                                                                }
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'batches.show',
                                                                    batch.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <span className="text-sm">
                                                            {batch.age_in_days}{' '}
                                                            days
                                                        </span>
                                                    </TableCell>
                                                    <TableCell
                                                        className="text-right"
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'batches.show',
                                                                    batch.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <div className="text-sm">
                                                            <span className="font-medium">
                                                                {
                                                                    batch.current_quantity
                                                                }
                                                            </span>
                                                            <span className="text-muted-foreground">
                                                                {' '}
                                                                /{' '}
                                                                {
                                                                    batch.initial_quantity
                                                                }
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell
                                                        className="text-right"
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'batches.show',
                                                                    batch.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        {batch.fcr ? (
                                                            <span className="text-sm font-medium">
                                                                {batch.fcr.toFixed(
                                                                    2,
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
                                                                    batch.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <span
                                                            className={`text-sm ${batch.mortality_rate > 5 ? 'font-medium text-red-600' : ''}`}
                                                        >
                                                            {batch.mortality_rate.toFixed(
                                                                1,
                                                            )}
                                                            %
                                                        </span>
                                                    </TableCell>
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'batches.show',
                                                                    batch.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        {batch.supplier_name ? (
                                                            <span className="text-sm">
                                                                {
                                                                    batch.supplier_name
                                                                }
                                                            </span>
                                                        ) : (
                                                            <span className="text-sm text-muted-foreground">
                                                                —
                                                            </span>
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'batches.show',
                                                                        batch.id,
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
                                            {pagination.total} total batches)
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

BatchesHistory.layout = (page: React.ReactNode) => page;
