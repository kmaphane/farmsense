import { store } from '@/actions/App/Http/Controllers/Slaughter/SlaughterController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { FieldLayout } from '@/layouts/app/field-layout';
import { Head, router } from '@inertiajs/react';
import {
    AlertCircle,
    Bird,
    Minus,
    Package,
    Plus,
    Scissors,
} from 'lucide-react';
import { useCallback, useMemo, useState } from 'react';

interface Batch {
    id: number;
    name: string;
    batch_number: string;
    current_quantity: number;
    age_in_days: number;
}

interface Product {
    id: number;
    name: string;
    type: string;
    yield_per_bird: number;
    units_per_package: number;
    package_unit: string | null;
}

interface DiscrepancyReason {
    value: string;
    label: string;
}

interface Props {
    batches: Batch[];
    products: Product[];
    discrepancyReasons: DiscrepancyReason[];
    suggestedDate: string;
}

interface BatchSource {
    id: string;
    batch_id: number | null;
    expected_quantity: number;
    actual_quantity: number;
    discrepancy_reason: string | null;
    discrepancy_notes: string;
}

interface ProductYield {
    product_id: number;
    product_name: string;
    estimated_quantity: number;
    actual_quantity: number;
}

function generateId(): string {
    return Math.random().toString(36).substring(2, 9);
}

export default function Create({
    batches,
    products,
    discrepancyReasons,
    suggestedDate,
}: Props) {
    const [slaughterDate, setSlaughterDate] = useState(suggestedDate);
    const [notes, setNotes] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});

    // Batch sources (repeater)
    const [batchSources, setBatchSources] = useState<BatchSource[]>([
        {
            id: generateId(),
            batch_id: null,
            expected_quantity: 0,
            actual_quantity: 0,
            discrepancy_reason: null,
            discrepancy_notes: '',
        },
    ]);

    // Product yields
    const [productYields, setProductYields] = useState<ProductYield[]>(
        products.map((p) => ({
            product_id: p.id,
            product_name: p.name,
            estimated_quantity: 0,
            actual_quantity: 0,
        })),
    );

    // Calculate total birds from all sources
    const totalBirds = useMemo(() => {
        return batchSources.reduce(
            (sum, source) => sum + (source.actual_quantity || 0),
            0,
        );
    }, [batchSources]);

    // Update yields when total birds changes
    const updateYieldsFromBirds = useCallback(
        (birds: number) => {
            setProductYields((prev) =>
                prev.map((y) => {
                    const product = products.find((p) => p.id === y.product_id);
                    const estimated = product
                        ? Math.round(birds * product.yield_per_bird)
                        : 0;
                    return {
                        ...y,
                        estimated_quantity: estimated,
                        // Auto-fill actual with estimated if not yet set
                        actual_quantity:
                            y.actual_quantity === 0
                                ? estimated
                                : y.actual_quantity,
                    };
                }),
            );
        },
        [products],
    );

    // Get available batches (excluding already selected)
    const getAvailableBatches = (currentSourceId: string) => {
        const selectedBatchIds = batchSources
            .filter((s) => s.id !== currentSourceId && s.batch_id !== null)
            .map((s) => s.batch_id);
        return batches.filter((b) => !selectedBatchIds.includes(b.id));
    };

    // Add batch source row
    const addBatchSource = () => {
        setBatchSources((prev) => [
            ...prev,
            {
                id: generateId(),
                batch_id: null,
                expected_quantity: 0,
                actual_quantity: 0,
                discrepancy_reason: null,
                discrepancy_notes: '',
            },
        ]);
    };

    // Remove batch source row
    const removeBatchSource = (id: string) => {
        if (batchSources.length <= 1) return;
        setBatchSources((prev) => prev.filter((s) => s.id !== id));
        // Recalculate yields
        const remaining = batchSources.filter((s) => s.id !== id);
        const newTotal = remaining.reduce(
            (sum, s) => sum + (s.actual_quantity || 0),
            0,
        );
        updateYieldsFromBirds(newTotal);
    };

    // Update batch source
    const updateBatchSource = (
        id: string,
        field: keyof BatchSource,
        value: unknown,
    ) => {
        setBatchSources((prev) => {
            const updated = prev.map((s) => {
                if (s.id !== id) return s;

                const newSource = { ...s, [field]: value };

                // If batch is selected, set expected quantity from batch's current quantity
                if (field === 'batch_id' && value) {
                    const batch = batches.find((b) => b.id === value);
                    if (batch) {
                        newSource.expected_quantity = batch.current_quantity;
                        // Default actual to expected
                        newSource.actual_quantity = batch.current_quantity;
                    }
                }

                return newSource;
            });

            // Recalculate yields based on new total
            const newTotal = updated.reduce(
                (sum, s) => sum + (s.actual_quantity || 0),
                0,
            );
            setTimeout(() => updateYieldsFromBirds(newTotal), 0);

            return updated;
        });
    };

    // Update product yield actual quantity
    const updateYieldActual = (productId: number, value: number) => {
        setProductYields((prev) =>
            prev.map((y) =>
                y.product_id === productId
                    ? { ...y, actual_quantity: value }
                    : y,
            ),
        );
    };

    // Check if a batch source has discrepancy
    const hasDiscrepancy = (source: BatchSource) =>
        source.batch_id !== null &&
        source.actual_quantity < source.expected_quantity;

    // Form validation
    const validateForm = (): boolean => {
        const newErrors: Record<string, string> = {};

        if (!slaughterDate) {
            newErrors.slaughter_date = 'Slaughter date is required';
        }

        const validSources = batchSources.filter((s) => s.batch_id !== null);
        if (validSources.length === 0) {
            newErrors.batch_sources = 'At least one batch source is required';
        }

        validSources.forEach((source, idx) => {
            if (source.actual_quantity < 1) {
                newErrors[`batch_sources.${idx}.actual_quantity`] =
                    'Quantity must be at least 1';
            }
            if (hasDiscrepancy(source) && !source.discrepancy_reason) {
                newErrors[`batch_sources.${idx}.discrepancy_reason`] =
                    'Reason required for discrepancy';
            }
        });

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    // Handle form submission
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        if (!validateForm()) return;

        setIsSubmitting(true);

        const validSources = batchSources.filter((s) => s.batch_id !== null);

        const formData = {
            slaughter_date: slaughterDate,
            batch_sources: validSources.map((s) => ({
                batch_id: s.batch_id,
                expected_quantity: s.expected_quantity,
                actual_quantity: s.actual_quantity,
                discrepancy_reason: s.discrepancy_reason,
                discrepancy_notes: s.discrepancy_notes || null,
            })),
            yields: productYields.map((y) => ({
                product_id: y.product_id,
                estimated_quantity: y.estimated_quantity,
                actual_quantity: y.actual_quantity,
            })),
            notes: notes || null,
        };

        router.post(store.url(), formData, {
            onError: (serverErrors) => {
                setErrors(serverErrors as Record<string, string>);
            },
            onFinish: () => {
                setIsSubmitting(false);
            },
        });
    };

    if (batches.length === 0) {
        return (
            <FieldLayout title="Slaughter" backHref="/batches" backLabel="Back">
                <Head title="Record Slaughter" />
                <Card>
                    <CardContent className="flex flex-col items-center justify-center py-12">
                        <Bird className="h-12 w-12 text-gray-400" />
                        <h3 className="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                            No Batches Available
                        </h3>
                        <p className="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            There are no active batches with birds available for
                            slaughter.
                        </p>
                    </CardContent>
                </Card>
            </FieldLayout>
        );
    }

    return (
        <FieldLayout title="Slaughter" backHref="/batches" backLabel="Back">
            <Head title="Record Slaughter" />

            <form onSubmit={handleSubmit} className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-3">
                    <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                        <Scissors className="h-6 w-6" />
                    </div>
                    <div>
                        <h1 className="text-xl font-bold text-gray-900 dark:text-gray-100">
                            Record Slaughter
                        </h1>
                        <p className="text-sm text-gray-500 dark:text-gray-400">
                            Process birds from one or more batches
                        </p>
                    </div>
                </div>

                {/* Date */}
                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-base">
                            Slaughter Date
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Input
                            type="date"
                            value={slaughterDate}
                            onChange={(e) => setSlaughterDate(e.target.value)}
                            max={new Date().toISOString().split('T')[0]}
                            required
                        />
                        {errors.slaughter_date && (
                            <p className="mt-1 text-sm text-red-500">
                                {errors.slaughter_date}
                            </p>
                        )}
                    </CardContent>
                </Card>

                {/* Batch Sources (Repeater) */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-base">
                                    Batch Sources
                                </CardTitle>
                                <CardDescription>
                                    Select batches and quantities to process
                                </CardDescription>
                            </div>
                            <Badge variant="secondary" className="text-lg">
                                <Bird className="mr-1 h-4 w-4" />
                                {totalBirds} birds
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {errors.batch_sources && (
                            <p className="text-sm text-red-500">
                                {errors.batch_sources}
                            </p>
                        )}

                        {batchSources.map((source, index) => {
                            const selectedBatch = batches.find(
                                (b) => b.id === source.batch_id,
                            );
                            const showDiscrepancy = hasDiscrepancy(source);

                            return (
                                <div
                                    key={source.id}
                                    className="rounded-lg border bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900"
                                >
                                    <div className="mb-3 flex items-center justify-between">
                                        <Label className="font-medium">
                                            Batch {index + 1}
                                        </Label>
                                        {batchSources.length > 1 && (
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                onClick={() =>
                                                    removeBatchSource(source.id)
                                                }
                                                className="h-8 w-8 p-0 text-red-500 hover:text-red-700"
                                            >
                                                <Minus className="h-4 w-4" />
                                            </Button>
                                        )}
                                    </div>

                                    <div className="grid gap-4 sm:grid-cols-2">
                                        {/* Batch Select */}
                                        <div className="space-y-2">
                                            <Label>Select Batch</Label>
                                            <Select
                                                value={
                                                    source.batch_id?.toString() ||
                                                    ''
                                                }
                                                onValueChange={(val) =>
                                                    updateBatchSource(
                                                        source.id,
                                                        'batch_id',
                                                        parseInt(val),
                                                    )
                                                }
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Choose batch..." />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {getAvailableBatches(
                                                        source.id,
                                                    ).map((batch) => (
                                                        <SelectItem
                                                            key={batch.id}
                                                            value={batch.id.toString()}
                                                        >
                                                            {batch.name} (
                                                            {
                                                                batch.current_quantity
                                                            }{' '}
                                                            birds,{' '}
                                                            {batch.age_in_days}{' '}
                                                            days)
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>

                                        {/* Expected Quantity (readonly) */}
                                        <div className="space-y-2">
                                            <Label>Expected</Label>
                                            <Input
                                                type="number"
                                                value={
                                                    source.expected_quantity ||
                                                    ''
                                                }
                                                readOnly
                                                className="bg-gray-100 dark:bg-gray-800"
                                            />
                                        </div>

                                        {/* Actual Quantity */}
                                        <div className="space-y-2">
                                            <Label>
                                                Actual Birds Processed
                                            </Label>
                                            <Input
                                                type="number"
                                                min={0}
                                                max={
                                                    source.expected_quantity ||
                                                    undefined
                                                }
                                                value={
                                                    source.actual_quantity || ''
                                                }
                                                onChange={(e) =>
                                                    updateBatchSource(
                                                        source.id,
                                                        'actual_quantity',
                                                        parseInt(
                                                            e.target.value,
                                                        ) || 0,
                                                    )
                                                }
                                            />
                                            {errors[
                                                `batch_sources.${index}.actual_quantity`
                                            ] && (
                                                <p className="text-sm text-red-500">
                                                    {
                                                        errors[
                                                            `batch_sources.${index}.actual_quantity`
                                                        ]
                                                    }
                                                </p>
                                            )}
                                        </div>

                                        {/* Show batch info */}
                                        {selectedBatch && (
                                            <div className="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                                <span>
                                                    Age:{' '}
                                                    {selectedBatch.age_in_days}{' '}
                                                    days
                                                </span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Discrepancy Section */}
                                    {showDiscrepancy && (
                                        <div className="mt-4 rounded-lg border border-yellow-200 bg-yellow-50 p-3 dark:border-yellow-900 dark:bg-yellow-950">
                                            <div className="mb-2 flex items-center gap-2 text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                                <AlertCircle className="h-4 w-4" />
                                                Discrepancy:{' '}
                                                {source.expected_quantity -
                                                    source.actual_quantity}{' '}
                                                birds missing
                                            </div>
                                            <div className="grid gap-3 sm:grid-cols-2">
                                                <div className="space-y-2">
                                                    <Label>Reason</Label>
                                                    <Select
                                                        value={
                                                            source.discrepancy_reason ||
                                                            ''
                                                        }
                                                        onValueChange={(val) =>
                                                            updateBatchSource(
                                                                source.id,
                                                                'discrepancy_reason',
                                                                val,
                                                            )
                                                        }
                                                    >
                                                        <SelectTrigger>
                                                            <SelectValue placeholder="Select reason..." />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            {discrepancyReasons.map(
                                                                (reason) => (
                                                                    <SelectItem
                                                                        key={
                                                                            reason.value
                                                                        }
                                                                        value={
                                                                            reason.value
                                                                        }
                                                                    >
                                                                        {
                                                                            reason.label
                                                                        }
                                                                    </SelectItem>
                                                                ),
                                                            )}
                                                        </SelectContent>
                                                    </Select>
                                                    {errors[
                                                        `batch_sources.${index}.discrepancy_reason`
                                                    ] && (
                                                        <p className="text-sm text-red-500">
                                                            {
                                                                errors[
                                                                    `batch_sources.${index}.discrepancy_reason`
                                                                ]
                                                            }
                                                        </p>
                                                    )}
                                                </div>
                                                <div className="space-y-2">
                                                    <Label>Notes</Label>
                                                    <Input
                                                        type="text"
                                                        value={
                                                            source.discrepancy_notes
                                                        }
                                                        onChange={(e) =>
                                                            updateBatchSource(
                                                                source.id,
                                                                'discrepancy_notes',
                                                                e.target.value,
                                                            )
                                                        }
                                                        placeholder="Additional details..."
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            );
                        })}

                        {/* Add Batch Button */}
                        {getAvailableBatches(
                            batchSources[batchSources.length - 1]?.id || '',
                        ).length > 0 && (
                            <Button
                                type="button"
                                variant="outline"
                                onClick={addBatchSource}
                                className="w-full"
                            >
                                <Plus className="mr-2 h-4 w-4" />
                                Add Another Batch
                            </Button>
                        )}
                    </CardContent>
                </Card>

                {/* Product Yields */}
                {totalBirds > 0 && products.length > 0 && (
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="text-base">
                                        Product Yields
                                    </CardTitle>
                                    <CardDescription>
                                        Review estimated yields and enter actual
                                        quantities
                                    </CardDescription>
                                </div>
                                <Package className="h-5 w-5 text-gray-400" />
                            </div>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {productYields.map((y) => {
                                const variance =
                                    y.actual_quantity - y.estimated_quantity;
                                const variancePercent =
                                    y.estimated_quantity > 0
                                        ? (
                                              (variance /
                                                  y.estimated_quantity) *
                                              100
                                          ).toFixed(1)
                                        : '0';

                                return (
                                    <div
                                        key={y.product_id}
                                        className="grid items-center gap-4 rounded-lg border p-3 sm:grid-cols-4"
                                    >
                                        <div>
                                            <p className="font-medium text-gray-900 dark:text-gray-100">
                                                {y.product_name}
                                            </p>
                                        </div>
                                        <div className="text-center">
                                            <p className="text-xs text-gray-500 dark:text-gray-400">
                                                Estimated
                                            </p>
                                            <p className="font-medium">
                                                {y.estimated_quantity}
                                            </p>
                                        </div>
                                        <div className="space-y-1">
                                            <Label className="text-xs">
                                                Actual
                                            </Label>
                                            <Input
                                                type="number"
                                                min={0}
                                                value={y.actual_quantity || ''}
                                                onChange={(e) =>
                                                    updateYieldActual(
                                                        y.product_id,
                                                        parseInt(
                                                            e.target.value,
                                                        ) || 0,
                                                    )
                                                }
                                            />
                                        </div>
                                        <div className="text-center">
                                            <p className="text-xs text-gray-500 dark:text-gray-400">
                                                Variance
                                            </p>
                                            <p
                                                className={`font-medium ${
                                                    variance < 0
                                                        ? 'text-red-600 dark:text-red-400'
                                                        : variance > 0
                                                          ? 'text-green-600 dark:text-green-400'
                                                          : ''
                                                }`}
                                            >
                                                {variance > 0 ? '+' : ''}
                                                {variance} ({variancePercent}%)
                                            </p>
                                        </div>
                                    </div>
                                );
                            })}

                            {/* Household Usage Note */}
                            {productYields.some(
                                (y) => y.actual_quantity < y.estimated_quantity,
                            ) && (
                                <p className="text-xs text-gray-500 dark:text-gray-400">
                                    <em>
                                        Note: Negative variance is recorded as
                                        household consumption.
                                    </em>
                                </p>
                            )}
                        </CardContent>
                    </Card>
                )}

                {/* Notes */}
                <Card>
                    <CardHeader className="pb-2">
                        <CardTitle className="text-base">
                            Notes (optional)
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Textarea
                            value={notes}
                            onChange={(e) => setNotes(e.target.value)}
                            placeholder="Any additional observations about the slaughter..."
                            rows={3}
                        />
                    </CardContent>
                </Card>

                {/* Submit */}
                <Button
                    type="submit"
                    className="w-full bg-red-600 hover:bg-red-700"
                    disabled={isSubmitting || totalBirds === 0}
                    size="lg"
                >
                    {isSubmitting
                        ? 'Recording...'
                        : `Record Slaughter (${totalBirds} birds)`}
                </Button>
            </form>
        </FieldLayout>
    );
}
