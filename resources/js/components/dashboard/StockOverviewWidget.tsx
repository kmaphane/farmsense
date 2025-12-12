import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { DollarSign, Package } from 'lucide-react';

interface ProcessedProduct {
    name: string;
    type: string;
    quantity: number;
    value: number;
}

interface Props {
    stockValue: number;
    carcassPrice: number | null;
    processedProducts: ProcessedProduct[];
}

function formatCurrency(cents: number): string {
    return `P${(cents / 100).toLocaleString('en-BW', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

interface StockSummaryProps {
    stockValue: number;
    carcassPrice: number | null;
}

export function StockSummary({ stockValue, carcassPrice }: StockSummaryProps) {
    const stats = [
        {
            title: 'Processed Stock Value',
            value: formatCurrency(stockValue),
            description: 'Carcass, cuts & offal ready for sale',
            icon: DollarSign,
        },
        {
            title: 'Carcass Price',
            value: carcassPrice
                ? formatCurrency(carcassPrice)
                : '-',
            description: 'Current whole chicken price',
            icon: Package,
        },
    ];

    return (
        <div className="grid gap-4 md:grid-cols-2">
            {stats.map((stat) => (
                <div key={stat.title} className="card-metric card-metric-brand">
                    <div className="flex flex-row items-center justify-between space-y-0 p-6 pb-2">
                        <span className="text-sm font-medium label">
                            {stat.title}
                        </span>
                        <stat.icon className="h-4 w-4 icon" />
                    </div>
                    <div className="p-6 pt-0">
                        <div className="text-2xl font-bold">
                            {stat.value}
                        </div>
                        {stat.description && (
                            <p className="text-xs opacity-80 mt-1">
                                {stat.description}
                            </p>
                        )}
                    </div>
                </div>
            ))}
        </div>
    );
}

export function StockBreakdown({ processedProducts }: { processedProducts: ProcessedProduct[] }) {
    if (processedProducts.length === 0) return null;

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-sm font-medium">
                    Stock Breakdown
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div className="space-y-2">
                    {processedProducts.map((product, i) => (
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
    );
}

export function StockOverviewWidget({ stockValue, carcassPrice, processedProducts }: Props) {
    return (
        <div className="space-y-4">
            <StockSummary stockValue={stockValue} carcassPrice={carcassPrice} />
            <StockBreakdown processedProducts={processedProducts} />
        </div>
    );
}
