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
import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowDownCircle,
    ArrowLeft,
    ArrowUpCircle,
    CheckCircle2,
    MapPin,
    Package,
    RefreshCw,
    Warehouse as WarehouseIcon,
    XCircle,
} from 'lucide-react';

interface Warehouse {
    id: number;
    name: string;
    location: string;
    capacity: number | null;
    is_active: boolean;
    stock_movements_count: number;
    created_at: string;
}

interface StockMovement {
    id: number;
    product_name: string;
    quantity: number;
    movement_type: string;
    reason: string;
    recorded_by: string;
    created_at: string;
}

interface Props {
    warehouse: Warehouse;
    recentMovements: StockMovement[];
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

export default function WarehousesShow({ warehouse, recentMovements }: Props) {
    return (
        <AppLayout>
            <Head title={`Warehouse - ${warehouse.name}`} />

            <div className="mx-auto max-w-7xl space-y-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={route('warehouses.index')}>
                                <ArrowLeft className="h-5 w-5" />
                            </Link>
                        </Button>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">
                                {warehouse.name}
                            </h1>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {warehouse.location}
                            </p>
                        </div>
                    </div>
                    <div>
                        {warehouse.is_active ? (
                            <Badge variant="default" className="gap-1">
                                <CheckCircle2 className="h-3 w-3" />
                                Active
                            </Badge>
                        ) : (
                            <Badge variant="secondary" className="gap-1">
                                <XCircle className="h-3 w-3" />
                                Inactive
                            </Badge>
                        )}
                    </div>
                </div>

                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Capacity
                            </CardTitle>
                            <WarehouseIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            {warehouse.capacity ? (
                                <>
                                    <div className="text-2xl font-bold">
                                        {warehouse.capacity.toLocaleString()}
                                    </div>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        units
                                    </p>
                                </>
                            ) : (
                                <div className="text-lg text-muted-foreground">
                                    Not specified
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total Movements
                            </CardTitle>
                            <Package className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {warehouse.stock_movements_count}
                            </div>
                            <p className="mt-1 text-xs text-muted-foreground">
                                transactions
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Location
                            </CardTitle>
                            <MapPin className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-base font-medium">
                                {warehouse.location}
                            </div>
                            <p className="mt-1 text-xs text-muted-foreground">
                                address
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Recent Stock Movements */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <CardTitle>Recent Stock Movements</CardTitle>
                            {recentMovements.length > 0 && (
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={route('stock-movements.index')}>
                                        View All
                                    </Link>
                                </Button>
                            )}
                        </div>
                    </CardHeader>
                    <CardContent>
                        {recentMovements.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-8 text-center">
                                <Package className="mb-2 h-8 w-8 text-muted-foreground/50" />
                                <p className="text-sm text-muted-foreground">
                                    No stock movements recorded yet for this
                                    warehouse
                                </p>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Date & Time</TableHead>
                                            <TableHead>Product</TableHead>
                                            <TableHead>Type</TableHead>
                                            <TableHead className="text-right">
                                                Quantity
                                            </TableHead>
                                            <TableHead>Reason</TableHead>
                                            <TableHead>Recorded By</TableHead>
                                            <TableHead className="text-right">
                                                Actions
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {recentMovements.map((movement) => {
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
                                                            {movement.quantity}
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
                                                            {movement.reason}
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
                                                            View
                                                        </Button>
                                                    </TableCell>
                                                </TableRow>
                                            );
                                        })}
                                    </TableBody>
                                </Table>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Warehouse Info */}
                <Card>
                    <CardHeader>
                        <CardTitle>Warehouse Information</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <div className="mb-1 text-sm font-medium">
                                    Name
                                </div>
                                <div className="text-base">
                                    {warehouse.name}
                                </div>
                            </div>

                            <div>
                                <div className="mb-1 text-sm font-medium">
                                    Location
                                </div>
                                <div className="text-base">
                                    {warehouse.location}
                                </div>
                            </div>

                            <div>
                                <div className="mb-1 text-sm font-medium">
                                    Status
                                </div>
                                <div className="text-base">
                                    {warehouse.is_active
                                        ? 'Active'
                                        : 'Inactive'}
                                </div>
                            </div>

                            <div>
                                <div className="mb-1 text-sm font-medium">
                                    Created
                                </div>
                                <div className="text-base">
                                    {warehouse.created_at}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

WarehousesShow.layout = (page: React.ReactNode) => page;
