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
    CheckCircle2,
    Eye,
    MapPin,
    Package,
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
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    warehouses: Warehouse[];
    pagination: Pagination;
}

export default function WarehousesIndex({ warehouses, pagination }: Props) {
    const handlePageChange = (page: number) => {
        router.get(
            route('warehouses.index'),
            { page },
            { preserveState: true },
        );
    };

    // Ensure warehouses is an array
    const warehouseList = Array.isArray(warehouses) ? warehouses : [];
    const activeCount = warehouseList.filter((w) => w.is_active).length;
    const inactiveCount = warehouseList.filter((w) => !w.is_active).length;

    return (
        <AppLayout>
            <Head title="Warehouses" />

            <div className="mx-auto max-w-7xl space-y-6 p-6">
                {/* Header */}
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">
                        Warehouses
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Manage storage locations and inventory
                    </p>
                </div>

                {/* Stats */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total Warehouses
                            </CardTitle>
                            <WarehouseIcon className="h-4 w-4 text-muted-foreground" />
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
                                Active
                            </CardTitle>
                            <CheckCircle2 className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {activeCount}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Inactive
                            </CardTitle>
                            <XCircle className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {inactiveCount}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Storage Locations</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {warehouseList.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <WarehouseIcon className="mb-4 h-12 w-12 text-muted-foreground/50" />
                                <h3 className="mb-2 text-lg font-semibold">
                                    No warehouses yet
                                </h3>
                                <p className="max-w-sm text-sm text-muted-foreground">
                                    Warehouses will appear here after you create
                                    storage locations in the admin panel.
                                </p>
                            </div>
                        ) : (
                            <>
                                <div className="overflow-x-auto">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Name</TableHead>
                                                <TableHead>Location</TableHead>
                                                <TableHead className="text-right">
                                                    Capacity
                                                </TableHead>
                                                <TableHead>Status</TableHead>
                                                <TableHead className="text-right">
                                                    Movements
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    Actions
                                                </TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {warehouseList.map((warehouse) => (
                                                <TableRow
                                                    key={warehouse.id}
                                                    className="cursor-pointer hover:bg-muted/50"
                                                >
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'warehouses.show',
                                                                    warehouse.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <div className="flex items-center gap-2">
                                                            <WarehouseIcon className="h-4 w-4 text-muted-foreground" />
                                                            <span className="font-medium">
                                                                {warehouse.name}
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'warehouses.show',
                                                                    warehouse.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <div className="flex items-center gap-2">
                                                            <MapPin className="h-4 w-4 text-muted-foreground" />
                                                            <span className="text-sm">
                                                                {
                                                                    warehouse.location
                                                                }
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell
                                                        className="text-right"
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'warehouses.show',
                                                                    warehouse.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        {warehouse.capacity ? (
                                                            <span className="text-sm">
                                                                {warehouse.capacity.toLocaleString()}
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
                                                                    'warehouses.show',
                                                                    warehouse.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        {warehouse.is_active ? (
                                                            <Badge
                                                                variant="default"
                                                                className="gap-1"
                                                            >
                                                                <CheckCircle2 className="h-3 w-3" />
                                                                Active
                                                            </Badge>
                                                        ) : (
                                                            <Badge
                                                                variant="secondary"
                                                                className="gap-1"
                                                            >
                                                                <XCircle className="h-3 w-3" />
                                                                Inactive
                                                            </Badge>
                                                        )}
                                                    </TableCell>
                                                    <TableCell
                                                        className="text-right"
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'warehouses.show',
                                                                    warehouse.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <div className="flex items-center justify-end gap-1">
                                                            <Package className="h-3 w-3 text-muted-foreground" />
                                                            <span className="text-sm">
                                                                {
                                                                    warehouse.stock_movements_count
                                                                }
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'warehouses.show',
                                                                        warehouse.id,
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

WarehousesIndex.layout = (page: React.ReactNode) => page;
