import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet';
import AppLayout from '@/layouts/app-layout';
import { Head, usePage } from '@inertiajs/react';
import { useState } from 'react';
import CreateProduct from './Create';

export default function ProductsIndex() {
    const [open, setOpen] = useState(false);
    // Get products from Inertia props
    const { products } = usePage().props as { products: any[] };

    return (
        <AppLayout>
            <Head title="Products" />
            <div className="mx-auto max-w-4xl p-8">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Products</h1>
                    <Sheet open={open} onOpenChange={setOpen}>
                        <SheetTrigger asChild>
                            <Button onClick={() => setOpen(true)}>
                                Create Product
                            </Button>
                        </SheetTrigger>
                        <SheetContent
                            side="right"
                            className="w-[400px] sm:w-[500px]"
                        >
                            <CreateProduct />
                        </SheetContent>
                    </Sheet>
                </div>
                {/* Product list */}
                {!products || products.length === 0 ? (
                    <div className="text-muted-foreground">
                        No products found.
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="min-w-full rounded-lg bg-background shadow">
                            <thead>
                                <tr>
                                    <th className="px-4 py-2 text-left">
                                        Name
                                    </th>
                                    <th className="px-4 py-2 text-left">
                                        Type
                                    </th>
                                    <th className="px-4 py-2 text-left">
                                        Unit
                                    </th>
                                    <th className="px-4 py-2 text-left">
                                        Price
                                    </th>
                                    <th className="px-4 py-2 text-left">
                                        Quantity
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {products.map((product) => (
                                    <tr
                                        key={product.id}
                                        className="border-b border-muted"
                                    >
                                        <td className="px-4 py-2 font-medium">
                                            {product.name}
                                        </td>
                                        <td className="px-4 py-2">
                                            {product.type}
                                        </td>
                                        <td className="px-4 py-2">
                                            {product.unit}
                                        </td>
                                        <td className="px-4 py-2">
                                            {product.selling_price_cents
                                                ? `₱${(product.selling_price_cents / 100).toFixed(2)}`
                                                : '-'}
                                        </td>
                                        <td className="px-4 py-2">
                                            {product.quantity_on_hand ?? '-'}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
