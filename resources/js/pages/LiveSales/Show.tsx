import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Calendar,
    DollarSign,
    FileText,
    Package,
    ShoppingCart,
    User,
} from 'lucide-react';

interface LiveSaleRecord {
    id: number;
    sale_date: string;
    sale_date_formatted: string;
    batch_name: string;
    batch_id: number;
    quantity_sold: number;
    unit_price: number;
    total_amount: number;
    customer_name: string;
    customer_id: number | null;
    notes: string | null;
    recorded_by: string;
    created_at: string;
}

interface Props {
    liveSaleRecord: LiveSaleRecord;
}

export default function LiveSalesShow({ liveSaleRecord }: Props) {
    return (
        <AppLayout>
            <Head title={`Live Sale - ${liveSaleRecord.sale_date_formatted}`} />

            <div className="mx-auto max-w-4xl space-y-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={route('live-sales.index')}>
                                <ArrowLeft className="h-5 w-5" />
                            </Link>
                        </Button>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">
                                Live Sale Record
                            </h1>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {liveSaleRecord.sale_date_formatted}
                            </p>
                        </div>
                    </div>
                </div>

                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Quantity Sold
                            </CardTitle>
                            <ShoppingCart className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {liveSaleRecord.quantity_sold}
                            </div>
                            <p className="mt-1 text-xs text-muted-foreground">
                                live birds
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Unit Price
                            </CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                P {liveSaleRecord.unit_price.toFixed(2)}
                            </div>
                            <p className="mt-1 text-xs text-muted-foreground">
                                per bird
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total Amount
                            </CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                P {liveSaleRecord.total_amount.toFixed(2)}
                            </div>
                            <p className="mt-1 text-xs text-muted-foreground">
                                total revenue
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Details Card */}
                <Card>
                    <CardHeader>
                        <CardTitle>Sale Details</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                    Sale Date
                                </div>
                                <div className="text-base">
                                    {liveSaleRecord.sale_date_formatted}
                                </div>
                            </div>

                            <div>
                                <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                    <Package className="h-4 w-4 text-muted-foreground" />
                                    Batch
                                </div>
                                <div className="text-base">
                                    <Link
                                        href={route(
                                            'batches.show',
                                            liveSaleRecord.batch_id,
                                        )}
                                        className="text-blue-600 hover:underline"
                                    >
                                        {liveSaleRecord.batch_name}
                                    </Link>
                                </div>
                            </div>

                            <div>
                                <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                    <User className="h-4 w-4 text-muted-foreground" />
                                    Customer
                                </div>
                                <div className="text-base">
                                    {liveSaleRecord.customer_name}
                                </div>
                            </div>

                            <div>
                                <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                    <User className="h-4 w-4 text-muted-foreground" />
                                    Recorded By
                                </div>
                                <div className="text-base">
                                    {liveSaleRecord.recorded_by}
                                </div>
                            </div>
                        </div>

                        <div className="border-t pt-4">
                            <div className="mb-2 text-sm font-medium">
                                Transaction Summary
                            </div>
                            <div className="grid gap-2 text-sm">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Quantity:
                                    </span>
                                    <span className="font-medium">
                                        {liveSaleRecord.quantity_sold} birds
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Unit Price:
                                    </span>
                                    <span className="font-medium">
                                        P {liveSaleRecord.unit_price.toFixed(2)}
                                    </span>
                                </div>
                                <div className="flex justify-between border-t pt-2">
                                    <span className="font-medium text-muted-foreground">
                                        Total Amount:
                                    </span>
                                    <span className="text-lg font-bold">
                                        P{' '}
                                        {liveSaleRecord.total_amount.toFixed(2)}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {liveSaleRecord.notes && (
                            <div className="border-t pt-4">
                                <div className="mb-2 flex items-center gap-2 text-sm font-medium">
                                    <FileText className="h-4 w-4 text-muted-foreground" />
                                    Notes
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    {liveSaleRecord.notes}
                                </p>
                            </div>
                        )}

                        <div className="border-t pt-4 text-xs text-muted-foreground">
                            Recorded on {liveSaleRecord.created_at}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

LiveSalesShow.layout = (page: React.ReactNode) => page;
