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
import { Calendar, DollarSign, Eye, ShoppingCart, User } from 'lucide-react';

interface LiveSaleRecord {
    id: number;
    sale_date: string;
    sale_date_formatted: string;
    batch_name: string;
    quantity_sold: number;
    unit_price: number;
    total_amount: number;
    customer_name: string;
    recorded_by: string;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    liveSaleRecords: LiveSaleRecord[];
    pagination: Pagination;
}

export default function LiveSalesIndex({ liveSaleRecords, pagination }: Props) {
    const handlePageChange = (page: number) => {
        router.get(
            route('live-sales.index'),
            { page },
            { preserveState: true },
        );
    };

    // Ensure liveSaleRecords is an array
    const recordList = Array.isArray(liveSaleRecords) ? liveSaleRecords : [];
    const totalQuantity = recordList.reduce(
        (sum, r) => sum + r.quantity_sold,
        0,
    );
    const totalRevenue = recordList.reduce((sum, r) => sum + r.total_amount, 0);

    return (
        <AppLayout>
            <Head title="Live Sales Records" />

            <div className="mx-auto max-w-7xl space-y-6 p-6">
                {/* Header */}
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">
                        Live Sales Records
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Track live bird sales to customers
                    </p>
                </div>

                {/* Stats */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total Records
                            </CardTitle>
                            <ShoppingCart className="h-4 w-4 text-muted-foreground" />
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
                                Birds Sold
                            </CardTitle>
                            <ShoppingCart className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {totalQuantity.toLocaleString()}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total Revenue
                            </CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                P {totalRevenue.toFixed(2)}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Sales Transactions</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {recordList.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <ShoppingCart className="mb-4 h-12 w-12 text-muted-foreground/50" />
                                <h3 className="mb-2 text-lg font-semibold">
                                    No live sales yet
                                </h3>
                                <p className="max-w-sm text-sm text-muted-foreground">
                                    Live sales will appear here after you sell
                                    birds through the batch detail pages.
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
                                                    Quantity
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    Unit Price
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    Total
                                                </TableHead>
                                                <TableHead>Customer</TableHead>
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
                                                                    'live-sales.show',
                                                                    record.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <div className="flex items-center gap-2">
                                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                                            <span className="font-medium">
                                                                {
                                                                    record.sale_date_formatted
                                                                }
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'live-sales.show',
                                                                    record.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <span className="font-medium">
                                                            {record.batch_name}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell
                                                        className="text-right"
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'live-sales.show',
                                                                    record.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <span className="font-medium">
                                                            {
                                                                record.quantity_sold
                                                            }
                                                        </span>
                                                    </TableCell>
                                                    <TableCell
                                                        className="text-right"
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'live-sales.show',
                                                                    record.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <span className="text-muted-foreground">
                                                            P{' '}
                                                            {record.unit_price.toFixed(
                                                                2,
                                                            )}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell
                                                        className="text-right"
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'live-sales.show',
                                                                    record.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <span className="font-bold">
                                                            P{' '}
                                                            {record.total_amount.toFixed(
                                                                2,
                                                            )}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'live-sales.show',
                                                                    record.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <div className="flex items-center gap-2">
                                                            <User className="h-4 w-4 text-muted-foreground" />
                                                            <span className="text-sm">
                                                                {
                                                                    record.customer_name
                                                                }
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'live-sales.show',
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
                                                                        'live-sales.show',
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

LiveSalesIndex.layout = (page: React.ReactNode) => page;
