import { store } from '@/actions/App/Http/Controllers/Slaughter/SlaughterController';
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
import { Textarea } from '@/components/ui/textarea';
import { router } from '@inertiajs/react';
import { AlertCircle, Bird, Minus, Plus, Scissors } from 'lucide-react';
import { useState } from 'react';

interface Batch {
    id: number;
    name: string;
    batch_number: string;
    current_quantity: number;
    age_in_days: number;
}

interface DiscrepancyReason {
    value: string;
    label: string;
}

interface BatchSource {
    id: string;
    batch_id: number | null;
    expected_quantity: number;
    actual_quantity: number;
    discrepancy_reason: string | null;
    discrepancy_notes: string;
}

interface SlaughterFormProps {
    batches: Batch[];
    discrepancyReasons: DiscrepancyReason[];
    suggestedDate: string;
}

function generateId(): string {
    return Math.random().toString(36).substring(2, 9);
}

export function SlaughterForm({
    batches,
    discrepancyReasons,
    suggestedDate,
}: SlaughterFormProps) {
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

    // Calculate total birds from all sources
    const totalBirds = batchSources.reduce(
        (sum, source) => sum + (source.actual_quantity || 0),
        0,
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
    };

    // Update batch source
    const updateBatchSource = (
        id: string,
        field: keyof BatchSource,
        value: unknown,
    ) => {
        setBatchSources((prev) =>
            prev.map((s) => {
                if (s.id !== id) return s;

                const newSource = { ...s, [field]: value };

                if (field === 'batch_id' && value) {
                    const batch = batches.find((b) => b.id === value);
                    if (batch) {
                        // Don't auto-fill quantities - let user specify how many to slaughter
                        newSource.expected_quantity = 0;
                        newSource.actual_quantity = 0;
                    }
                }

                // When expected quantity changes, update actual to match (user can adjust if needed)
                if (field === 'expected_quantity') {
                    newSource.actual_quantity = value as number;
                }

                return newSource;
            }),
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
            <div className="flex flex-col items-center justify-center py-8 text-center">
                <Bird className="h-12 w-12 text-gray-400" />
                <h3 className="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                    No Batches Available
                </h3>
                <p className="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    There are no active batches with birds available for
                    slaughter.
                </p>
            </div>
        );
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            {/* Date */}
            <div className="space-y-2">
                <Label>Slaughter Date</Label>
                <Input
                    type="date"
                    value={slaughterDate}
                    onChange={(e) => setSlaughterDate(e.target.value)}
                    max={new Date().toISOString().split('T')[0]}
                    required
                />
                {errors.slaughter_date && (
                    <p className="text-sm text-red-500">
                        {errors.slaughter_date}
                    </p>
                )}
            </div>

            {/* Batch Sources - Repeater */}
            <div className="space-y-3">
                <div className="flex items-center justify-between">
                    <Label className="text-base">Batch Sources</Label>
                    <div className="flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                        <Bird className="h-4 w-4" />
                        {totalBirds} birds
                    </div>
                </div>

                {errors.batch_sources && (
                    <p className="text-sm text-red-500">
                        {errors.batch_sources}
                    </p>
                )}

                <div className="space-y-3">
                    {batchSources.map((source, index) => {
                        const selectedBatch = batches.find(
                            (b) => b.id === source.batch_id,
                        );
                        const showDiscrepancy = hasDiscrepancy(source);

                        return (
                            <div
                                key={source.id}
                                className="space-y-3 rounded-lg border bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900"
                            >
                                {/* Repeater Item Header */}
                                <div className="flex items-center justify-between">
                                    <span className="text-xs font-medium text-gray-500">
                                        Batch {index + 1}
                                    </span>
                                    {batchSources.length > 1 && (
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            onClick={() =>
                                                removeBatchSource(source.id)
                                            }
                                            className="h-6 w-6 p-0 text-red-500 hover:text-red-700"
                                        >
                                            <Minus className="h-3 w-3" />
                                        </Button>
                                    )}
                                </div>

                                {/* Batch Selection */}
                                <div className="space-y-2">
                                    <Label className="text-sm">
                                        Select Batch
                                    </Label>
                                    <Select
                                        value={
                                            source.batch_id?.toString() || ''
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
                                            {getAvailableBatches(source.id).map(
                                                (batch) => (
                                                    <SelectItem
                                                        key={batch.id}
                                                        value={batch.id.toString()}
                                                    >
                                                        {batch.name} (
                                                        {batch.current_quantity}{' '}
                                                        birds,{' '}
                                                        {batch.age_in_days}{' '}
                                                        days)
                                                    </SelectItem>
                                                ),
                                            )}
                                        </SelectContent>
                                    </Select>
                                </div>

                                {/* Expected/Actual Grid */}
                                {source.batch_id && (
                                    <div className="space-y-3">
                                        <div className="space-y-2">
                                            <Label className="text-xs">
                                                Birds to Slaughter
                                            </Label>
                                            <Input
                                                type="number"
                                                min={0}
                                                max={
                                                    selectedBatch?.current_quantity
                                                }
                                                value={
                                                    source.expected_quantity ||
                                                    ''
                                                }
                                                onChange={(e) =>
                                                    updateBatchSource(
                                                        source.id,
                                                        'expected_quantity',
                                                        parseInt(
                                                            e.target.value,
                                                        ) || 0,
                                                    )
                                                }
                                                placeholder="Enter quantity"
                                                className="h-9 text-sm"
                                            />
                                            {selectedBatch && (
                                                <p className="text-xs text-gray-500">
                                                    Available:{' '}
                                                    {
                                                        selectedBatch.current_quantity
                                                    }{' '}
                                                    birds
                                                </p>
                                            )}
                                        </div>
                                        <div className="space-y-2">
                                            <Label className="text-xs">
                                                Actual Slaughtered
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
                                                className="h-9 text-sm"
                                            />
                                            {errors[
                                                `batch_sources.${index}.actual_quantity`
                                            ] && (
                                                <p className="text-xs text-red-500">
                                                    {
                                                        errors[
                                                            `batch_sources.${index}.actual_quantity`
                                                        ]
                                                    }
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                )}

                                {/* Discrepancy Alert */}
                                {showDiscrepancy && (
                                    <div className="space-y-2 rounded border border-yellow-200 bg-yellow-50 p-2 dark:border-yellow-900 dark:bg-yellow-950">
                                        <div className="flex items-center gap-2 text-xs font-medium text-yellow-800 dark:text-yellow-200">
                                            <AlertCircle className="h-3 w-3" />
                                            Missing:{' '}
                                            {source.expected_quantity -
                                                source.actual_quantity}{' '}
                                            birds
                                        </div>
                                        <div className="space-y-2">
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
                                                <SelectTrigger className="h-8 text-xs">
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
                                                                {reason.label}
                                                            </SelectItem>
                                                        ),
                                                    )}
                                                </SelectContent>
                                            </Select>
                                            {errors[
                                                `batch_sources.${index}.discrepancy_reason`
                                            ] && (
                                                <p className="text-xs text-red-500">
                                                    {
                                                        errors[
                                                            `batch_sources.${index}.discrepancy_reason`
                                                        ]
                                                    }
                                                </p>
                                            )}
                                            <Input
                                                type="text"
                                                value={source.discrepancy_notes}
                                                onChange={(e) =>
                                                    updateBatchSource(
                                                        source.id,
                                                        'discrepancy_notes',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="Additional notes..."
                                                className="h-8 text-xs"
                                            />
                                        </div>
                                    </div>
                                )}
                            </div>
                        );
                    })}
                </div>

                {/* Add Batch Button */}
                {getAvailableBatches(
                    batchSources[batchSources.length - 1]?.id || '',
                ).length > 0 && (
                    <Button
                        type="button"
                        variant="outline"
                        onClick={addBatchSource}
                        size="sm"
                        className="w-full"
                    >
                        <Plus className="mr-2 h-3 w-3" />
                        Add Another Batch
                    </Button>
                )}
            </div>

            {/* Notes */}
            <div className="space-y-2">
                <Label>Notes (optional)</Label>
                <Textarea
                    value={notes}
                    onChange={(e) => setNotes(e.target.value)}
                    placeholder="Any additional observations..."
                    rows={3}
                    className="text-sm"
                />
            </div>

            {/* Submit */}
            <Button
                type="submit"
                className="w-full bg-red-600 hover:bg-red-700"
                disabled={isSubmitting || totalBirds === 0}
            >
                <Scissors className="mr-2 h-4 w-4" />
                {isSubmitting
                    ? 'Recording...'
                    : `Record Slaughter (${totalBirds} birds)`}
            </Button>
        </form>
    );
}
