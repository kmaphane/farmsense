import { update } from '@/actions/App/Http/Controllers/Products/ProductPricingController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { FieldLayout } from '@/layouts/app/field-layout';
import { Head, router } from '@inertiajs/react';
import {
    ChevronDown,
    ChevronUp,
    DollarSign,
    History,
    Pencil,
    Tag,
} from 'lucide-react';
import { useState } from 'react';

interface PriceHistory {
    id: number;
    price_cents: number;
    price_formatted: string;
    effective_from: string;
    effective_until: string | null;
    changed_by: string | null;
    reason: string | null;
}

interface Product {
    id: number;
    name: string;
    local_name: string | null;
    type: string;
    type_label: string;
    selling_price_cents: number | null;
    selling_price_formatted: string | null;
    units_per_package: number;
    package_unit: string | null;
    package_unit_label: string | null;
    price_history: PriceHistory[];
}

interface Props {
    products: Product[];
}

function ProductTypeIcon({ type }: { type: string }) {
    const iconClass = 'h-4 w-4';
    switch (type) {
        case 'live_bird':
            return <span className={iconClass}>🐔</span>;
        case 'whole_chicken':
            return <span className={iconClass}>🍗</span>;
        case 'chicken_pieces':
            return <span className={iconClass}>🍖</span>;
        case 'offal':
            return <span className={iconClass}>🫀</span>;
        default:
            return <Tag className={iconClass} />;
    }
}

function ProductTypeBadge({ type, label }: { type: string; label: string }) {
    const colorMap: Record<string, string> = {
        live_bird:
            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        whole_chicken:
            'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
        chicken_pieces:
            'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
        offal: 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400',
        by_product:
            'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
    };

    return (
        <Badge
            className={`${colorMap[type] || colorMap.by_product} text-xs font-medium`}
        >
            <ProductTypeIcon type={type} />
            <span className="ml-1">{label}</span>
        </Badge>
    );
}

export default function Pricing({ products }: Props) {
    const [selectedProduct, setSelectedProduct] = useState<Product | null>(
        null,
    );
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
    const [expandedHistory, setExpandedHistory] = useState<number | null>(null);
    const [newPrice, setNewPrice] = useState('');
    const [reason, setReason] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);

    const openEditDialog = (product: Product) => {
        setSelectedProduct(product);
        setNewPrice(
            product.selling_price_cents
                ? (product.selling_price_cents / 100).toFixed(2)
                : '',
        );
        setReason('');
        setIsEditDialogOpen(true);
    };

    const closeEditDialog = () => {
        setIsEditDialogOpen(false);
        setSelectedProduct(null);
        setNewPrice('');
        setReason('');
    };

    const handlePriceUpdate = () => {
        if (!selectedProduct || !newPrice) return;

        setIsSubmitting(true);
        const priceInCents = Math.round(parseFloat(newPrice) * 100);

        router.put(
            update.url(selectedProduct.id),
            {
                new_price_cents: priceInCents,
                reason: reason || null,
            },
            {
                onSuccess: () => {
                    closeEditDialog();
                },
                onFinish: () => {
                    setIsSubmitting(false);
                },
            },
        );
    };

    const toggleHistory = (productId: number) => {
        setExpandedHistory(expandedHistory === productId ? null : productId);
    };

    return (
        <FieldLayout title="Pricing" backHref="/batches" backLabel="Back">
            <Head title="Product Pricing" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-3">
                    <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        <DollarSign className="h-6 w-6" />
                    </div>
                    <div>
                        <h1 className="text-xl font-bold text-gray-900 dark:text-gray-100">
                            Product Pricing
                        </h1>
                        <p className="text-sm text-gray-500 dark:text-gray-400">
                            Manage selling prices for all products
                        </p>
                    </div>
                </div>

                {/* Products List */}
                {products.length === 0 ? (
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-12">
                            <Tag className="h-12 w-12 text-gray-400" />
                            <h3 className="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                                No Products Found
                            </h3>
                            <p className="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Create products in the admin panel to set
                                prices.
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="space-y-4">
                        {products.map((product) => (
                            <Card key={product.id}>
                                <CardHeader className="pb-2">
                                    <div className="flex items-start justify-between">
                                        <div className="space-y-1">
                                            <CardTitle className="text-base">
                                                {product.name}
                                                {product.local_name && (
                                                    <span className="ml-2 text-sm font-normal text-gray-500 dark:text-gray-400">
                                                        ({product.local_name})
                                                    </span>
                                                )}
                                            </CardTitle>
                                            <CardDescription className="flex items-center gap-2">
                                                <ProductTypeBadge
                                                    type={product.type}
                                                    label={product.type_label}
                                                />
                                                {product.package_unit_label && (
                                                    <span className="text-xs">
                                                        {
                                                            product.units_per_package
                                                        }{' '}
                                                        {
                                                            product.package_unit_label
                                                        }
                                                    </span>
                                                )}
                                            </CardDescription>
                                        </div>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() =>
                                                openEditDialog(product)
                                            }
                                        >
                                            <Pencil className="mr-1 h-3 w-3" />
                                            Edit
                                        </Button>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                {product.selling_price_formatted ||
                                                    'Not set'}
                                            </p>
                                            <p className="text-xs text-gray-500 dark:text-gray-400">
                                                Current price
                                            </p>
                                        </div>

                                        {product.price_history.length > 0 && (
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() =>
                                                    toggleHistory(product.id)
                                                }
                                                className="text-xs"
                                            >
                                                <History className="mr-1 h-3 w-3" />
                                                History
                                                {expandedHistory ===
                                                product.id ? (
                                                    <ChevronUp className="ml-1 h-3 w-3" />
                                                ) : (
                                                    <ChevronDown className="ml-1 h-3 w-3" />
                                                )}
                                            </Button>
                                        )}
                                    </div>

                                    {/* Price History Table */}
                                    {expandedHistory === product.id &&
                                        product.price_history.length > 0 && (
                                            <div className="mt-4 rounded-lg border">
                                                <Table>
                                                    <TableHeader>
                                                        <TableRow>
                                                            <TableHead>
                                                                Price
                                                            </TableHead>
                                                            <TableHead>
                                                                Effective
                                                            </TableHead>
                                                            <TableHead>
                                                                Changed By
                                                            </TableHead>
                                                            <TableHead>
                                                                Reason
                                                            </TableHead>
                                                        </TableRow>
                                                    </TableHeader>
                                                    <TableBody>
                                                        {product.price_history.map(
                                                            (history) => (
                                                                <TableRow
                                                                    key={
                                                                        history.id
                                                                    }
                                                                >
                                                                    <TableCell className="font-medium">
                                                                        {
                                                                            history.price_formatted
                                                                        }
                                                                    </TableCell>
                                                                    <TableCell className="text-xs">
                                                                        {new Date(
                                                                            history.effective_from,
                                                                        ).toLocaleDateString()}
                                                                    </TableCell>
                                                                    <TableCell className="text-xs text-gray-500 dark:text-gray-400">
                                                                        {history.changed_by ||
                                                                            '-'}
                                                                    </TableCell>
                                                                    <TableCell className="max-w-[150px] truncate text-xs text-gray-500 dark:text-gray-400">
                                                                        {history.reason ||
                                                                            '-'}
                                                                    </TableCell>
                                                                </TableRow>
                                                            ),
                                                        )}
                                                    </TableBody>
                                                </Table>
                                            </div>
                                        )}
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}
            </div>

            {/* Edit Price Dialog */}
            <Dialog open={isEditDialogOpen} onOpenChange={setIsEditDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Update Price</DialogTitle>
                        <DialogDescription>
                            Set a new price for{' '}
                            <strong>{selectedProduct?.name}</strong>
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4 py-4">
                        {/* Current Price Info */}
                        {selectedProduct?.selling_price_formatted && (
                            <div className="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-900">
                                <p className="text-xs text-gray-500 dark:text-gray-400">
                                    Current Price
                                </p>
                                <p className="text-lg font-bold text-gray-900 dark:text-gray-100">
                                    {selectedProduct.selling_price_formatted}
                                </p>
                            </div>
                        )}

                        {/* New Price Input */}
                        <div className="space-y-2">
                            <Label htmlFor="new_price">New Price (BWP)</Label>
                            <div className="relative">
                                <span className="absolute top-1/2 left-3 -translate-y-1/2 text-gray-500">
                                    P
                                </span>
                                <Input
                                    id="new_price"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    value={newPrice}
                                    onChange={(e) =>
                                        setNewPrice(e.target.value)
                                    }
                                    className="pl-8"
                                    placeholder="0.00"
                                    autoFocus
                                />
                            </div>
                        </div>

                        {/* Reason Input */}
                        <div className="space-y-2">
                            <Label htmlFor="reason">
                                Reason for Change (optional)
                            </Label>
                            <Textarea
                                id="reason"
                                value={reason}
                                onChange={(e) => setReason(e.target.value)}
                                placeholder="e.g., Seasonal adjustment, cost increase"
                                rows={2}
                            />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button variant="outline" onClick={closeEditDialog}>
                            Cancel
                        </Button>
                        <Button
                            onClick={handlePriceUpdate}
                            disabled={
                                isSubmitting ||
                                !newPrice ||
                                parseFloat(newPrice) <= 0
                            }
                            className="bg-green-600 hover:bg-green-700"
                        >
                            {isSubmitting ? 'Updating...' : 'Update Price'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </FieldLayout>
    );
}
