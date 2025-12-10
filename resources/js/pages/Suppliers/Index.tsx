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
    DollarSign,
    Eye,
    Mail,
    Package,
    Phone,
    Star,
    Truck,
    XCircle,
} from 'lucide-react';

interface Supplier {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    category: string;
    category_label: string;
    performance_rating: number | null;
    current_price_per_unit: number | null;
    is_active: boolean;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    suppliers: Supplier[];
    pagination: Pagination;
}

const categoryConfig: Record<
    string,
    { variant: 'default' | 'secondary' | 'outline' }
> = {
    feed: { variant: 'default' },
    chicks: { variant: 'secondary' },
    medication: { variant: 'outline' },
    equipment: { variant: 'outline' },
};

export default function SuppliersIndex({ suppliers, pagination }: Props) {
    const handlePageChange = (page: number) => {
        router.get(route('suppliers.index'), { page }, { preserveState: true });
    };

    // Ensure suppliers is an array
    const supplierList = Array.isArray(suppliers) ? suppliers : [];
    const activeCount = supplierList.filter((s) => s.is_active).length;
    const inactiveCount = supplierList.filter((s) => !s.is_active).length;

    const renderRating = (rating: number | null) => {
        if (!rating)
            return (
                <span className="text-sm text-muted-foreground">Not rated</span>
            );

        return (
            <div className="flex items-center gap-1">
                {Array.from({ length: 5 }, (_, i) => (
                    <Star
                        key={i}
                        className={`h-3 w-3 ${i < rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'}`}
                    />
                ))}
                <span className="ml-1 text-sm text-muted-foreground">
                    {rating.toFixed(1)}
                </span>
            </div>
        );
    };

    return (
        <AppLayout>
            <Head title="Suppliers" />

            <div className="mx-auto max-w-7xl space-y-6 p-6">
                {/* Header */}
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">
                        Suppliers
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Manage supplier relationships and track performance
                    </p>
                </div>

                {/* Stats */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total Suppliers
                            </CardTitle>
                            <Truck className="h-4 w-4 text-muted-foreground" />
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
                        <CardTitle>Supplier List</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {supplierList.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <Truck className="mb-4 h-12 w-12 text-muted-foreground/50" />
                                <h3 className="mb-2 text-lg font-semibold">
                                    No suppliers yet
                                </h3>
                                <p className="max-w-sm text-sm text-muted-foreground">
                                    Suppliers will appear here after you add
                                    them through the admin panel.
                                </p>
                            </div>
                        ) : (
                            <>
                                <div className="overflow-x-auto">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Name</TableHead>
                                                <TableHead>Category</TableHead>
                                                <TableHead>Contact</TableHead>
                                                <TableHead>Rating</TableHead>
                                                <TableHead>
                                                    Price/Unit
                                                </TableHead>
                                                <TableHead>Status</TableHead>
                                                <TableHead className="text-right">
                                                    Actions
                                                </TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {supplierList.map((supplier) => {
                                                const config = categoryConfig[
                                                    supplier.category
                                                ] || { variant: 'default' };

                                                return (
                                                    <TableRow
                                                        key={supplier.id}
                                                        className="cursor-pointer hover:bg-muted/50"
                                                    >
                                                        <TableCell
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'suppliers.show',
                                                                        supplier.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            <div className="flex items-center gap-2">
                                                                <Package className="h-4 w-4 text-muted-foreground" />
                                                                <span className="font-medium">
                                                                    {
                                                                        supplier.name
                                                                    }
                                                                </span>
                                                            </div>
                                                        </TableCell>
                                                        <TableCell
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'suppliers.show',
                                                                        supplier.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            <Badge
                                                                variant={
                                                                    config.variant
                                                                }
                                                            >
                                                                {
                                                                    supplier.category_label
                                                                }
                                                            </Badge>
                                                        </TableCell>
                                                        <TableCell
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'suppliers.show',
                                                                        supplier.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            <div className="space-y-1">
                                                                {supplier.email && (
                                                                    <div className="flex items-center gap-1 text-sm">
                                                                        <Mail className="h-3 w-3 text-muted-foreground" />
                                                                        <span>
                                                                            {
                                                                                supplier.email
                                                                            }
                                                                        </span>
                                                                    </div>
                                                                )}
                                                                {supplier.phone && (
                                                                    <div className="flex items-center gap-1 text-sm">
                                                                        <Phone className="h-3 w-3 text-muted-foreground" />
                                                                        <span>
                                                                            {
                                                                                supplier.phone
                                                                            }
                                                                        </span>
                                                                    </div>
                                                                )}
                                                                {!supplier.email &&
                                                                    !supplier.phone && (
                                                                        <span className="text-sm text-muted-foreground">
                                                                            —
                                                                        </span>
                                                                    )}
                                                            </div>
                                                        </TableCell>
                                                        <TableCell
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'suppliers.show',
                                                                        supplier.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            {renderRating(
                                                                supplier.performance_rating,
                                                            )}
                                                        </TableCell>
                                                        <TableCell
                                                            className="text-right"
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'suppliers.show',
                                                                        supplier.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            {supplier.current_price_per_unit ? (
                                                                <div className="flex items-center justify-end gap-1">
                                                                    <DollarSign className="h-3 w-3 text-muted-foreground" />
                                                                    <span className="text-sm font-medium">
                                                                        P{' '}
                                                                        {supplier.current_price_per_unit.toFixed(
                                                                            2,
                                                                        )}
                                                                    </span>
                                                                </div>
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
                                                                        'suppliers.show',
                                                                        supplier.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            {supplier.is_active ? (
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
                                                        <TableCell className="text-right">
                                                            <Button
                                                                variant="ghost"
                                                                size="sm"
                                                                onClick={() =>
                                                                    router.visit(
                                                                        route(
                                                                            'suppliers.show',
                                                                            supplier.id,
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

SuppliersIndex.layout = (page: React.ReactNode) => page;
