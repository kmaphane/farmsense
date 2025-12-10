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
import { Eye, Mail, Phone, User, Users } from 'lucide-react';

interface Customer {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    type: string;
    type_label: string;
    credit_limit: number | null;
    payment_terms: string | null;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    customers: Customer[];
    pagination: Pagination;
}

const typeConfig: Record<
    string,
    { variant: 'default' | 'secondary' | 'outline' }
> = {
    wholesale: { variant: 'default' },
    retail: { variant: 'secondary' },
    distributor: { variant: 'outline' },
};

export default function CustomersIndex({ customers, pagination }: Props) {
    const handlePageChange = (page: number) => {
        router.get(route('customers.index'), { page }, { preserveState: true });
    };

    // Ensure customers is an array
    const customerList = Array.isArray(customers) ? customers : [];
    const wholesaleCount = customerList.filter(
        (c) => c.type === 'wholesale',
    ).length;
    const retailCount = customerList.filter((c) => c.type === 'retail').length;

    return (
        <AppLayout>
            <Head title="Customers" />

            <div className="mx-auto max-w-7xl space-y-6 p-6">
                {/* Header */}
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">
                        Customers
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Manage your customer relationships
                    </p>
                </div>

                {/* Stats */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total Customers
                            </CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
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
                                Wholesale
                            </CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {wholesaleCount}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Retail
                            </CardTitle>
                            <User className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {retailCount}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Customer List</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {customerList.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <Users className="mb-4 h-12 w-12 text-muted-foreground/50" />
                                <h3 className="mb-2 text-lg font-semibold">
                                    No customers yet
                                </h3>
                                <p className="max-w-sm text-sm text-muted-foreground">
                                    Customers will appear here after you add
                                    them through Quick Actions or the admin
                                    panel.
                                </p>
                            </div>
                        ) : (
                            <>
                                <div className="overflow-x-auto">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Name</TableHead>
                                                <TableHead>Type</TableHead>
                                                <TableHead>Contact</TableHead>
                                                <TableHead className="text-right">
                                                    Credit Limit
                                                </TableHead>
                                                <TableHead>
                                                    Payment Terms
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    Actions
                                                </TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {customerList.map((customer) => {
                                                const config = typeConfig[
                                                    customer.type
                                                ] || { variant: 'default' };

                                                return (
                                                    <TableRow
                                                        key={customer.id}
                                                        className="cursor-pointer hover:bg-muted/50"
                                                    >
                                                        <TableCell
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'customers.show',
                                                                        customer.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            <div className="flex items-center gap-2">
                                                                <User className="h-4 w-4 text-muted-foreground" />
                                                                <span className="font-medium">
                                                                    {
                                                                        customer.name
                                                                    }
                                                                </span>
                                                            </div>
                                                        </TableCell>
                                                        <TableCell
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'customers.show',
                                                                        customer.id,
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
                                                                    customer.type_label
                                                                }
                                                            </Badge>
                                                        </TableCell>
                                                        <TableCell
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'customers.show',
                                                                        customer.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            <div className="space-y-1">
                                                                {customer.email && (
                                                                    <div className="flex items-center gap-1 text-sm">
                                                                        <Mail className="h-3 w-3 text-muted-foreground" />
                                                                        <span>
                                                                            {
                                                                                customer.email
                                                                            }
                                                                        </span>
                                                                    </div>
                                                                )}
                                                                {customer.phone && (
                                                                    <div className="flex items-center gap-1 text-sm">
                                                                        <Phone className="h-3 w-3 text-muted-foreground" />
                                                                        <span>
                                                                            {
                                                                                customer.phone
                                                                            }
                                                                        </span>
                                                                    </div>
                                                                )}
                                                                {!customer.email &&
                                                                    !customer.phone && (
                                                                        <span className="text-sm text-muted-foreground">
                                                                            —
                                                                        </span>
                                                                    )}
                                                            </div>
                                                        </TableCell>
                                                        <TableCell
                                                            className="text-right"
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'customers.show',
                                                                        customer.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            {customer.credit_limit ? (
                                                                <span className="text-sm font-medium">
                                                                    P{' '}
                                                                    {customer.credit_limit.toFixed(
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
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'customers.show',
                                                                        customer.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            {customer.payment_terms ? (
                                                                <span className="text-sm">
                                                                    {
                                                                        customer.payment_terms
                                                                    }
                                                                </span>
                                                            ) : (
                                                                <span className="text-sm text-muted-foreground">
                                                                    —
                                                                </span>
                                                            )}
                                                        </TableCell>
                                                        <TableCell className="text-right">
                                                            <div className="flex justify-end gap-2">
                                                                <Button
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    onClick={() =>
                                                                        router.visit(
                                                                            route(
                                                                                'customers.show',
                                                                                customer.id,
                                                                            ),
                                                                        )
                                                                    }
                                                                >
                                                                    <Eye className="mr-1 h-4 w-4" />
                                                                    View
                                                                </Button>
                                                            </div>
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

CustomersIndex.layout = (page: React.ReactNode) => page;
