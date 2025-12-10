import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowDownCircle,
    ArrowLeft,
    ArrowUpCircle,
    Calendar,
    FileText,
    Package,
    RefreshCw,
    User,
    Warehouse,
} from 'lucide-react';

interface StockMovement {
    id: number;
    product_name: string;
    product_id: number;
    warehouse_name: string;
    warehouse_id: number;
    quantity: number;
    movement_type: string;
    reason: string;
    notes: string | null;
    recorded_by: string;
    created_at: string;
}

interface Props {
    stockMovement: StockMovement;
}

const movementTypeConfig: Record<
    string,
    {
        label: string;
        variant: 'default' | 'secondary' | 'destructive' | 'outline';
        icon: typeof ArrowUpCircle;
        description: string;
    }
> = {
    in: {
        label: 'Stock In',
        variant: 'default',
        icon: ArrowDownCircle,
        description: 'Inventory received into warehouse',
    },
    out: {
        label: 'Stock Out',
        variant: 'destructive',
        icon: ArrowUpCircle,
        description: 'Inventory issued or sold',
    },
    adjustment: {
        label: 'Adjustment',
        variant: 'secondary',
        icon: RefreshCw,
        description: 'Stock level adjustment',
    },
    transfer: {
        label: 'Transfer',
        variant: 'outline',
        icon: RefreshCw,
        description: 'Transfer between warehouses',
    },
};

export default function StockMovementsShow({ stockMovement }: Props) {
    const config = movementTypeConfig[stockMovement.movement_type];
    const Icon = config.icon;

    return (
        <AppLayout>
            <Head title={`Stock Movement - ${stockMovement.product_name}`} />

            <div className="mx-auto max-w-4xl space-y-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={route('stock-movements.index')}>
                                <ArrowLeft className="h-5 w-5" />
                            </Link>
                        </Button>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">
                                Stock Movement
                            </h1>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {stockMovement.created_at}
                            </p>
                        </div>
                    </div>
                </div>

                {/* Summary Card */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <CardTitle>Movement Details</CardTitle>
                            <Badge variant={config.variant} className="gap-1">
                                <Icon className="h-3 w-3" />
                                {config.label}
                            </Badge>
                        </div>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {config.description}
                        </p>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        {/* Product & Warehouse Info */}
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                    <Package className="h-4 w-4 text-muted-foreground" />
                                    Product
                                </div>
                                <div className="text-base font-medium">
                                    {stockMovement.product_name}
                                </div>
                            </div>

                            <div>
                                <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                    <Warehouse className="h-4 w-4 text-muted-foreground" />
                                    Warehouse
                                </div>
                                <div className="text-base">
                                    {stockMovement.warehouse_name}
                                </div>
                            </div>
                        </div>

                        {/* Quantity */}
                        <div className="border-t pt-4">
                            <div className="mb-2 text-sm font-medium">
                                Quantity
                            </div>
                            <div className="text-4xl font-bold">
                                {stockMovement.movement_type === 'out'
                                    ? '-'
                                    : '+'}
                                {stockMovement.quantity}
                            </div>
                        </div>

                        {/* Reason */}
                        <div className="border-t pt-4">
                            <div className="mb-2 text-sm font-medium">
                                Reason
                            </div>
                            <div className="text-base">
                                {stockMovement.reason}
                            </div>
                        </div>

                        {/* Notes */}
                        {stockMovement.notes && (
                            <div className="border-t pt-4">
                                <div className="mb-2 flex items-center gap-2 text-sm font-medium">
                                    <FileText className="h-4 w-4 text-muted-foreground" />
                                    Notes
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    {stockMovement.notes}
                                </p>
                            </div>
                        )}

                        {/* Metadata */}
                        <div className="space-y-3 border-t pt-4">
                            <div>
                                <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                    <User className="h-4 w-4 text-muted-foreground" />
                                    Recorded By
                                </div>
                                <div className="text-base">
                                    {stockMovement.recorded_by}
                                </div>
                            </div>

                            <div>
                                <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                    Date & Time
                                </div>
                                <div className="text-base">
                                    {stockMovement.created_at}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

StockMovementsShow.layout = (page: React.ReactNode) => page;
