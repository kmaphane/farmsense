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
import { router } from '@inertiajs/react';
import { Loader2 } from 'lucide-react';
import { useState } from 'react';

interface Supplier {
    id: number;
    name: string;
}

interface BatchFormProps {
    suppliers: Supplier[];
    suggestedBatchNumber: string;
    suggestedStartDate: string;
    compact?: boolean;
}

export function BatchForm({
    suppliers,
    suggestedBatchNumber,
    suggestedStartDate,
    compact = false,
}: BatchFormProps) {
    const [name, setName] = useState('');
    const [batchNumber, setBatchNumber] = useState(suggestedBatchNumber);
    const [startDate, setStartDate] = useState(suggestedStartDate);
    const [initialQuantity, setInitialQuantity] = useState<number | ''>('');
    const [supplierId, setSupplierId] = useState<number | ''>('');
    const [targetWeight, setTargetWeight] = useState<number | ''>('');
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [processing, setProcessing] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        if (processing) return;

        // Clear previous errors
        setErrors({});

        // Client-side validation
        const newErrors: Record<string, string> = {};
        if (!name.trim()) newErrors.name = 'Name is required';
        if (!batchNumber.trim())
            newErrors.batch_number = 'Batch number is required';
        if (!startDate) newErrors.start_date = 'Start date is required';
        if (!initialQuantity || initialQuantity <= 0)
            newErrors.initial_quantity = 'Initial quantity must be greater than 0';

        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            return;
        }

        setProcessing(true);

        const formData = {
            name: name.trim(),
            batch_number: batchNumber.trim(),
            start_date: startDate,
            initial_quantity: initialQuantity,
            supplier_id: supplierId || null,
            target_weight_kg: targetWeight || null,
        };

        try {
            const response = await fetch('/api/batches/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || '',
                },
                body: JSON.stringify(formData),
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    setErrors(data.errors);
                } else {
                    setErrors({ form: data.message || 'Failed to create batch' });
                }
                setProcessing(false);
                return;
            }

            // Success - redirect to batch show page
            router.visit(`/batches/${data.batch_id}`);
        } catch (error) {
            console.error('Error creating batch:', error);
            setErrors({ form: 'An unexpected error occurred' });
            setProcessing(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4 p-4">
            {errors.form && (
                <div className="rounded-md bg-red-50 p-3 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400">
                    {errors.form}
                </div>
            )}

            {/* Name */}
            <div className="space-y-2">
                <Label htmlFor="name" className="text-xs">
                    Batch Name <span className="text-red-500">*</span>
                </Label>
                <Input
                    id="name"
                    type="text"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    placeholder="e.g., Spring 2025 Batch"
                    className={`h-9 text-sm ${errors.name ? 'border-red-500' : ''}`}
                />
                {errors.name && (
                    <p className="text-xs text-red-500">{errors.name}</p>
                )}
            </div>

            {/* Batch Number */}
            <div className="space-y-2">
                <Label htmlFor="batch_number" className="text-xs">
                    Batch Number <span className="text-red-500">*</span>
                </Label>
                <Input
                    id="batch_number"
                    type="text"
                    value={batchNumber}
                    onChange={(e) => setBatchNumber(e.target.value)}
                    placeholder="e.g., B-2025-001"
                    className={`h-9 text-sm ${errors.batch_number ? 'border-red-500' : ''}`}
                />
                {errors.batch_number && (
                    <p className="text-xs text-red-500">{errors.batch_number}</p>
                )}
            </div>

            {/* Start Date */}
            <div className="space-y-2">
                <Label htmlFor="start_date" className="text-xs">
                    Start Date <span className="text-red-500">*</span>
                </Label>
                <Input
                    id="start_date"
                    type="date"
                    value={startDate}
                    onChange={(e) => setStartDate(e.target.value)}
                    className={`h-9 text-sm ${errors.start_date ? 'border-red-500' : ''}`}
                />
                {errors.start_date && (
                    <p className="text-xs text-red-500">{errors.start_date}</p>
                )}
            </div>

            {/* Initial Quantity */}
            <div className="space-y-2">
                <Label htmlFor="initial_quantity" className="text-xs">
                    Initial Quantity (Chicks) <span className="text-red-500">*</span>
                </Label>
                <Input
                    id="initial_quantity"
                    type="number"
                    min={1}
                    value={initialQuantity}
                    onChange={(e) =>
                        setInitialQuantity(
                            e.target.value ? parseInt(e.target.value) : '',
                        )
                    }
                    placeholder="Number of chicks"
                    className={`h-9 text-sm ${errors.initial_quantity ? 'border-red-500' : ''}`}
                />
                {errors.initial_quantity && (
                    <p className="text-xs text-red-500">
                        {errors.initial_quantity}
                    </p>
                )}
            </div>

            {/* Supplier */}
            <div className="space-y-2">
                <Label htmlFor="supplier_id" className="text-xs">
                    Chick Supplier
                </Label>
                <Select
                    value={supplierId ? supplierId.toString() : ''}
                    onValueChange={(value) =>
                        setSupplierId(value ? parseInt(value) : '')
                    }
                >
                    <SelectTrigger
                        id="supplier_id"
                        className={`h-9 text-sm ${errors.supplier_id ? 'border-red-500' : ''}`}
                    >
                        <SelectValue placeholder="Select supplier (optional)" />
                    </SelectTrigger>
                    <SelectContent>
                        {suppliers.map((supplier) => (
                            <SelectItem
                                key={supplier.id}
                                value={supplier.id.toString()}
                            >
                                {supplier.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {errors.supplier_id && (
                    <p className="text-xs text-red-500">{errors.supplier_id}</p>
                )}
            </div>

            {/* Target Weight */}
            <div className="space-y-2">
                <Label htmlFor="target_weight_kg" className="text-xs">
                    Target Weight (kg)
                </Label>
                <Input
                    id="target_weight_kg"
                    type="number"
                    min={0}
                    step={0.01}
                    value={targetWeight}
                    onChange={(e) =>
                        setTargetWeight(
                            e.target.value ? parseFloat(e.target.value) : '',
                        )
                    }
                    placeholder="Target harvest weight per bird"
                    className={`h-9 text-sm ${errors.target_weight_kg ? 'border-red-500' : ''}`}
                />
                {errors.target_weight_kg && (
                    <p className="text-xs text-red-500">
                        {errors.target_weight_kg}
                    </p>
                )}
                <p className="text-xs text-gray-500">
                    Typical: 1.8-2.5 kg for broilers
                </p>
            </div>

            {/* Submit Button */}
            <div className="flex justify-end gap-2 pt-2">
                <Button
                    type="submit"
                    disabled={processing}
                    className="w-full"
                    size={compact ? 'sm' : 'default'}
                >
                    {processing ? (
                        <>
                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            Creating...
                        </>
                    ) : (
                        'Create Batch'
                    )}
                </Button>
            </div>
        </form>
    );
}
