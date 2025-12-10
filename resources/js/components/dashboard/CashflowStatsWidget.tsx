import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { DollarSign, Package, ShoppingCart } from 'lucide-react';

interface ProcessedProduct {
    name: string;
    type: string;
    quantity: number;
    value: number;
}

interface CashflowMetrics {
    stockValue: number;
    monthlySales: number;
    carcassPrice: number | null;
    processedProducts: ProcessedProduct[];
}

interface Props {
    cashflow: CashflowMetrics;
}

function formatCurrency(cents: number): string {
    return `P${(cents / 100).toLocaleString('en-BW', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

export function CashflowStatsWidget({ cashflow }: Props) {
    const stats = [
        {
            title: 'Processed Stock Value',
            value: formatCurrency(cashflow.stockValue),
            description: 'Carcass, cuts & offal ready for sale',
            icon: DollarSign,
            trend: null,
        },
        {
            title: 'Monthly Sales',
            value: formatCurrency(cashflow.monthlySales),
            description: 'Revenue this month',
            icon: ShoppingCart,
            trend: null,
        },
        {
            title: 'Carcass Price',
            value: cashflow.carcassPrice
                ? formatCurrency(cashflow.carcassPrice)
                : '-',
            description: 'Current whole chicken price',
            icon: Package,
            trend: null,
        },
    ];

    return (
        <div className="space-y-4">
            <div className="grid gap-4 md:grid-cols-3">
                {stats.map((stat) => (
                    <Card key={stat.title}>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                {stat.title}
                            </CardTitle>
                            <stat.icon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {stat.value}
                            </div>
                            {stat.description && (
                                <p className="text-xs text-muted-foreground">
                                    {stat.description}
                                </p>
                            )}
                        </CardContent>
                    </Card>
                ))}
            </div>

            {/* Stock Breakdown */}
            {cashflow.processedProducts.length > 0 && (
                <Card>
                    <CardHeader>
                        <CardTitle className="text-sm font-medium">
                            Stock Breakdown
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-2">
                            {cashflow.processedProducts.map((product, i) => (
                                <div
                                    key={i}
                                    className="flex items-center justify-between rounded-md border p-2"
                                >
                                    <div className="flex items-center gap-2">
                                        <Badge variant="outline">
                                            {product.type}
                                        </Badge>
                                        <span className="text-sm font-medium">
                                            {product.name}
                                        </span>
                                    </div>
                                    <div className="text-right">
                                        <p className="text-sm font-semibold">
                                            {formatCurrency(product.value)}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            {product.quantity} units
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            )}
        </div>
    );
}
