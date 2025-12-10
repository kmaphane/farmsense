import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { AlertTriangle, Package } from 'lucide-react';
import { Link } from '@inertiajs/react';

interface LowStockAlert {
    id: number;
    name: string;
    type: string;
    type_label: string;
    quantity_on_hand: number;
    reorder_level: number;
    unit: string;
    days_remaining: number | null;
    is_critical: boolean;
}

interface Props {
    alerts: LowStockAlert[];
}

export function LowStockAlertWidget({ alerts }: Props) {
    if (alerts.length === 0) {
        return (
            <Card>
                <CardHeader>
                    <CardTitle>Stock Alerts</CardTitle>
                    <CardDescription>
                        Products below reorder level
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="flex flex-col items-center justify-center py-8">
                        <Package className="h-12 w-12 text-green-500" />
                        <p className="mt-4 text-sm font-medium text-green-600 dark:text-green-400">
                            All stock levels adequate
                        </p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            No items below reorder level
                        </p>
                    </div>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card>
            <CardHeader>
                <div className="flex items-center justify-between">
                    <div>
                        <CardTitle>Stock Alerts</CardTitle>
                        <CardDescription>
                            {alerts.length} item{alerts.length !== 1 ? 's' : ''}{' '}
                            below reorder level
                        </CardDescription>
                    </div>
                    <Link
                        href="/inventory/products"
                        className="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    >
                        View All
                    </Link>
                </div>
            </CardHeader>
            <CardContent>
                <div className="space-y-3">
                    {alerts.map((alert) => (
                        <div
                            key={alert.id}
                            className={`flex items-center justify-between rounded-lg border p-3 ${
                                alert.is_critical
                                    ? 'border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-950'
                                    : 'border-yellow-200 bg-yellow-50 dark:border-yellow-900 dark:bg-yellow-950'
                            }`}
                        >
                            <div className="flex items-center gap-3">
                                <div
                                    className={`flex h-8 w-8 items-center justify-center rounded-full ${
                                        alert.is_critical
                                            ? 'bg-red-100 dark:bg-red-900'
                                            : 'bg-yellow-100 dark:bg-yellow-900'
                                    }`}
                                >
                                    <AlertTriangle
                                        className={`h-4 w-4 ${
                                            alert.is_critical
                                                ? 'text-red-600 dark:text-red-400'
                                                : 'text-yellow-600 dark:text-yellow-400'
                                        }`}
                                    />
                                </div>
                                <div>
                                    <p className="text-sm font-medium">
                                        {alert.name}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        {alert.quantity_on_hand} {alert.unit}{' '}
                                        left
                                        {alert.days_remaining !== null &&
                                            ` • Est. ${alert.days_remaining} day${alert.days_remaining !== 1 ? 's' : ''}`}
                                    </p>
                                </div>
                            </div>
                            <Badge
                                variant={
                                    alert.is_critical ? 'destructive' : 'outline'
                                }
                            >
                                {alert.type_label}
                            </Badge>
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
