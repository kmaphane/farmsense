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
import {
    ArrowDownCircle,
    ArrowUpCircle,
    Eye,
    Package,
    RefreshCw,
} from 'lucide-react';

interface StockMovement {
    id: number;
    product_name: string;
    warehouse_name: string;
    quantity: number;
    movement_type: string;
    reason: string;
    recorded_by: string;
    created_at: string;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    stockMovements: StockMovement[];
    pagination: Pagination;
}

const movementTypeConfig: Record<
    string,
    {
        label: string;
        variant: 'default' | 'secondary' | 'destructive' | 'outline';
        icon: typeof ArrowUpCircle;
    }
> = {
    in: { label: 'Stock In', variant: 'default', icon: ArrowDownCircle },
    out: { label: 'Stock Out', variant: 'destructive', icon: ArrowUpCircle },
    adjustment: { label: 'Adjustment', variant: 'secondary', icon: RefreshCw },
    transfer: { label: 'Transfer', variant: 'outline', icon: RefreshCw },
};

export default function StockMovementsIndex({
    stockMovements,
    pagination,
}: Props) {
    const handlePageChange = (page: number) => {
        router.get(
            route('stock-movements.index'),
            { page },
            { preserveState: true },
        );
    };

    // Ensure stockMovements is an array
    const movementList = Array.isArray(stockMovements) ? stockMovements : [];

    return (
        <AppLayout>
            <Head title="Stock Movements" />

            <div className="mx-auto max-w-7xl space-y-6 p-6">
                {/* Header */}
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">
                        Stock Movements
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Track all inventory movements and adjustments
                    </p>
                </div>

                {/* Stats */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total Movements
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
                                Stock In
                            </CardTitle>
                            <ArrowDownCircle className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {
                                    movementList.filter(
                                        (m) => m.movement_type === 'in',
                                    ).length
                                }
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Stock Out
                            </CardTitle>
                            <ArrowUpCircle className="h-4 w-4 text-red-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {
                                    movementList.filter(
                                        (m) => m.movement_type === 'out',
                                    ).length
                                }
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Movement History</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {movementList.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <Package className="mb-4 h-12 w-12 text-muted-foreground/50" />
                                <h3 className="mb-2 text-lg font-semibold">
                                    No stock movements yet
                                </h3>
                                <p className="max-w-sm text-sm text-muted-foreground">
                                    Stock movements will appear here when
                                    inventory is received, issued, or adjusted.
                                </p>
                            </div>
                        ) : (
                            <>
                                <div className="overflow-x-auto">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>
                                                    Date & Time
                                                </TableHead>
                                                <TableHead>Product</TableHead>
                                                <TableHead>Warehouse</TableHead>
                                                <TableHead>Type</TableHead>
                                                <TableHead className="text-right">
                                                    Quantity
                                                </TableHead>
                                                <TableHead>Reason</TableHead>
                                                <TableHead>
                                                    Recorded By
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    Actions
                                                </TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {movementList.map((movement) => {
                                                const config =
                                                    movementTypeConfig[
                                                        movement.movement_type
                                                    ];
                                                const Icon = config.icon;

                                                return (
                                                    <TableRow
                                                        key={movement.id}
                                                        className="cursor-pointer hover:bg-muted/50"
                                                    >
                                                        <TableCell
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'stock-movements.show',
                                                                        movement.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            <span className="text-sm">
                                                                {
                                                                    movement.created_at
                                                                }
                                                            </span>
                                                        </TableCell>
                                                        <TableCell
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'stock-movements.show',
                                                                        movement.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            <span className="font-medium">
                                                                {
                                                                    movement.product_name
                                                                }
                                                            </span>
                                                        </TableCell>
                                                        <TableCell
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'stock-movements.show',
                                                                        movement.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            <span className="text-sm">
                                                                {
                                                                    movement.warehouse_name
                                                                }
                                                            </span>
                                                        </TableCell>
                                                        <TableCell
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'stock-movements.show',
                                                                        movement.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            <Badge
                                                                variant={
                                                                    config.variant
                                                                }
                                                                className="gap-1"
                                                            >
                                                                <Icon className="h-3 w-3" />
                                                                {config.label}
                                                            </Badge>
                                                        </TableCell>
                                                        <TableCell
                                                            className="text-right"
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'stock-movements.show',
                                                                        movement.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            <span className="font-medium">
                                                                {
                                                                    movement.quantity
                                                                }
                                                            </span>
                                                        </TableCell>
                                                        <TableCell
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'stock-movements.show',
                                                                        movement.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            <span className="text-sm text-muted-foreground">
                                                                {
                                                                    movement.reason
                                                                }
                                                            </span>
                                                        </TableCell>
                                                        <TableCell
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'stock-movements.show',
                                                                        movement.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            <span className="text-sm text-muted-foreground">
                                                                {
                                                                    movement.recorded_by
                                                                }
                                                            </span>
                                                        </TableCell>
                                                        <TableCell className="text-right">
                                                            <Button
                                                                variant="ghost"
                                                                size="sm"
                                                                onClick={() =>
                                                                    router.visit(
                                                                        route(
                                                                            'stock-movements.show',
                                                                            movement.id,
                                                                        ),
                                                                    )
                                                                }
                                                            >
                                                                <Eye className="mr-1 h-4 w-4" />
                                                                View
                                                            </Button>
                                                        </TableCell>
                                                    </TableRow>
                                                );
                                            })}
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

StockMovementsIndex.layout = (page: React.ReactNode) => page;
