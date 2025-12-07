import { store } from '@/actions/App/Http/Controllers/LiveSales/LiveSaleController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Form } from '@inertiajs/react';
import { Bird, Calendar, DollarSign, Save, User } from 'lucide-react';
import * as React from 'react';

interface Customer {
    id: number;
    name: string;
}

interface LiveSaleFormProps {
    batchId: number;
    currentBirdCount: number;
    liveBirdPrice: number | null;
    customers: Customer[];
    suggestedDate?: string;
    compact?: boolean;
    onSuccess?: () => void;
}

export function LiveSaleForm({
    batchId,
    currentBirdCount,
    liveBirdPrice,
    customers,
    suggestedDate,
    compact = false,
}: LiveSaleFormProps) {
    const [quantity, setQuantity] = React.useState<number>(1);
    const [unitPrice, setUnitPrice] = React.useState<number>(
        liveBirdPrice ? liveBirdPrice / 100 : 82,
    );
    const [customerId, setCustomerId] = React.useState<string>('');

    const totalAmount = quantity * unitPrice;

    const formUrl = store.url(batchId);

    return (
        <Form action={formUrl} method="post">
            {({ errors, processing }) => (
                <div className={compact ? 'space-y-4' : 'space-y-6'}>
                    {/* Sale Date */}
                    <div className="space-y-2">
                        <Label
                            htmlFor="sale_date"
                            className="flex items-center gap-2"
                        >
                            <Calendar className="h-4 w-4 text-gray-500" />
                            Sale Date
                            <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="sale_date"
                            name="sale_date"
                            type="date"
                            required
                            defaultValue={
                                suggestedDate ??
                                new Date().toISOString().split('T')[0]
                            }
                            className={errors.sale_date ? 'border-red-500' : ''}
                        />
                        {errors.sale_date && (
                            <p className="text-xs text-red-500">
                                {errors.sale_date}
                            </p>
                        )}
                    </div>

                    {/* Quantity */}
                    <div className="space-y-2">
                        <Label
                            htmlFor="quantity_sold"
                            className="flex items-center gap-2"
                        >
                            <Bird className="h-4 w-4 text-gray-500" />
                            Quantity
                            <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="quantity_sold"
                            name="quantity_sold"
                            type="number"
                            required
                            min={1}
                            max={currentBirdCount}
                            value={quantity}
                            onChange={(e) =>
                                setQuantity(
                                    Math.max(1, parseInt(e.target.value) || 1),
                                )
                            }
                            className={
                                errors.quantity_sold ? 'border-red-500' : ''
                            }
                        />
                        <p className="text-xs text-gray-500 dark:text-gray-400">
                            Max available: {currentBirdCount.toLocaleString()}{' '}
                            birds
                        </p>
                        {errors.quantity_sold && (
                            <p className="text-xs text-red-500">
                                {errors.quantity_sold}
                            </p>
                        )}
                    </div>

                    {/* Unit Price */}
                    <div className="space-y-2">
                        <Label
                            htmlFor="unit_price"
                            className="flex items-center gap-2"
                        >
                            <DollarSign className="h-4 w-4 text-gray-500" />
                            Price per Bird (BWP)
                            <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="unit_price"
                            name="unit_price_cents"
                            type="hidden"
                            value={Math.round(unitPrice * 100)}
                        />
                        <Input
                            type="number"
                            required
                            min={0}
                            step="0.01"
                            value={unitPrice}
                            onChange={(e) =>
                                setUnitPrice(
                                    Math.max(
                                        0,
                                        parseFloat(e.target.value) || 0,
                                    ),
                                )
                            }
                            className={
                                errors.unit_price_cents ? 'border-red-500' : ''
                            }
                        />
                        {errors.unit_price_cents && (
                            <p className="text-xs text-red-500">
                                {errors.unit_price_cents}
                            </p>
                        )}
                    </div>

                    {/* Customer (Optional) */}
                    <div className="space-y-2">
                        <Label
                            htmlFor="customer_id"
                            className="flex items-center gap-2"
                        >
                            <User className="h-4 w-4 text-gray-500" />
                            Customer (Optional)
                        </Label>
                        <input
                            type="hidden"
                            name="customer_id"
                            value={customerId || ''}
                        />
                        <Select
                            value={customerId}
                            onValueChange={setCustomerId}
                        >
                            <SelectTrigger
                                className={
                                    errors.customer_id ? 'border-red-500' : ''
                                }
                            >
                                <SelectValue placeholder="Select a customer" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">No customer</SelectItem>
                                {customers.map((customer) => (
                                    <SelectItem
                                        key={customer.id}
                                        value={customer.id.toString()}
                                    >
                                        {customer.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.customer_id && (
                            <p className="text-xs text-red-500">
                                {errors.customer_id}
                            </p>
                        )}
                    </div>

                    {/* Notes */}
                    <div className="space-y-2">
                        <Label
                            htmlFor="notes"
                            className="flex items-center gap-2"
                        >
                            Notes (Optional)
                        </Label>
                        <Input
                            id="notes"
                            name="notes"
                            type="text"
                            placeholder="Any additional notes..."
                        />
                    </div>

                    {/* Hidden team_id field */}
                    <input type="hidden" name="team_id" value="" />

                    {/* Total Amount Preview */}
                    <div className="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-950/30">
                        <div className="flex items-center justify-between">
                            <span className="text-sm font-medium text-green-800 dark:text-green-200">
                                Total Amount
                            </span>
                            <span className="text-lg font-bold text-green-700 dark:text-green-300">
                                P {totalAmount.toFixed(2)}
                            </span>
                        </div>
                        <p className="mt-1 text-xs text-green-600 dark:text-green-400">
                            {quantity} bird{quantity !== 1 ? 's' : ''} × P
                            {unitPrice.toFixed(2)}
                        </p>
                    </div>

                    {/* Submit Button */}
                    <Button
                        type="submit"
                        className="w-full"
                        disabled={
                            processing ||
                            quantity > currentBirdCount ||
                            quantity < 1
                        }
                    >
                        {processing ? (
                            <>Processing...</>
                        ) : (
                            <>
                                <Save className="mr-2 h-4 w-4" />
                                Record Sale
                            </>
                        )}
                    </Button>

                    {quantity > currentBirdCount && (
                        <p className="text-center text-sm text-red-500">
                            Quantity exceeds available birds in batch
                        </p>
                    )}
                </div>
            )}
        </Form>
    );
}
