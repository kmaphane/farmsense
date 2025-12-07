import { store } from '@/actions/App/Http/Controllers/Portioning/PortioningController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Form } from '@inertiajs/react';
import { useMemo, useState } from 'react';

interface PortioningFormProps {
    wholeChickenStock: {
        id: number;
        name: string;
        quantity_on_hand: number;
    } | null;
    chickenPiecesProduct: {
        id: number;
        name: string;
        quantity_on_hand: number;
        units_per_package: number;
        package_unit: string | null;
    } | null;
    suggestedDate: string;
    defaultPackWeight: number;
    compact?: boolean;
}

export function PortioningForm({
    wholeChickenStock,
    chickenPiecesProduct,
    suggestedDate,
    defaultPackWeight,
    compact = false,
}: PortioningFormProps) {
    const [wholeBirdsUsed, setWholeBirdsUsed] = useState<number>(0);
    const [packsProduced, setPacksProduced] = useState<number>(0);
    const [packWeight, setPackWeight] = useState<number>(defaultPackWeight);

    const availableBirds = wholeChickenStock?.quantity_on_hand ?? 0;

    const calculations = useMemo(() => {
        const totalWeight = packsProduced * packWeight;
        const avgYieldPerBird =
            wholeBirdsUsed > 0 ? totalWeight / wholeBirdsUsed : 0;
        const expectedYield = wholeBirdsUsed * 0.7; // ~70% yield expected
        const yieldEfficiency =
            expectedYield > 0 ? (totalWeight / expectedYield) * 100 : 0;

        return {
            totalWeight: totalWeight.toFixed(2),
            avgYieldPerBird: avgYieldPerBird.toFixed(2),
            yieldEfficiency: yieldEfficiency.toFixed(1),
        };
    }, [wholeBirdsUsed, packsProduced, packWeight]);

    if (!wholeChickenStock || availableBirds === 0) {
        return (
            <div className="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-center dark:border-yellow-900 dark:bg-yellow-950">
                <p className="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                    No whole chickens available
                </p>
                <p className="mt-1 text-xs text-yellow-600 dark:text-yellow-400">
                    Complete a slaughter operation first to add whole chickens
                    to inventory.
                </p>
            </div>
        );
    }

    return (
        <Form
            action={store.url()}
            method="post"
            className={compact ? 'space-y-4' : 'space-y-6'}
        >
            {({ processing, errors }) => (
                <>
                    {/* Stock Info Card */}
                    <div className="rounded-lg border border-green-200 bg-green-50 p-3 dark:border-green-900 dark:bg-green-950">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-green-800 dark:text-green-200">
                                    Whole Chickens Available
                                </p>
                                <p className="text-xl font-bold text-green-700 dark:text-green-300">
                                    {availableBirds}
                                </p>
                            </div>
                            {chickenPiecesProduct && (
                                <div className="text-right">
                                    <p className="text-xs font-medium text-green-800 dark:text-green-200">
                                        Current Packs Stock
                                    </p>
                                    <p className="text-xl font-bold text-green-700 dark:text-green-300">
                                        {chickenPiecesProduct.quantity_on_hand}
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Date */}
                    <div className="space-y-2">
                        <Label htmlFor="portioning_date">Portioning Date</Label>
                        <Input
                            id="portioning_date"
                            name="portioning_date"
                            type="date"
                            defaultValue={suggestedDate}
                            required
                        />
                        {errors.portioning_date && (
                            <p className="text-sm text-red-500">
                                {errors.portioning_date}
                            </p>
                        )}
                    </div>

                    {/* Whole Birds Used */}
                    <div className="space-y-2">
                        <Label htmlFor="whole_birds_used">
                            Whole Birds Used
                        </Label>
                        <Input
                            id="whole_birds_used"
                            name="whole_birds_used"
                            type="number"
                            min={1}
                            max={availableBirds}
                            value={wholeBirdsUsed || ''}
                            onChange={(e) =>
                                setWholeBirdsUsed(Number(e.target.value))
                            }
                            placeholder={`Max ${availableBirds}`}
                            required
                        />
                        {errors.whole_birds_used && (
                            <p className="text-sm text-red-500">
                                {errors.whole_birds_used}
                            </p>
                        )}
                        {wholeBirdsUsed > availableBirds && (
                            <p className="text-sm text-yellow-600 dark:text-yellow-400">
                                Exceeds available stock ({availableBirds})
                            </p>
                        )}
                    </div>

                    {/* Pack Weight */}
                    <div className="space-y-2">
                        <Label htmlFor="pack_weight_kg">Pack Weight (kg)</Label>
                        <Input
                            id="pack_weight_kg"
                            name="pack_weight_kg"
                            type="number"
                            step="0.1"
                            min={0.1}
                            value={packWeight || ''}
                            onChange={(e) =>
                                setPackWeight(Number(e.target.value))
                            }
                            required
                        />
                        {errors.pack_weight_kg && (
                            <p className="text-sm text-red-500">
                                {errors.pack_weight_kg}
                            </p>
                        )}
                    </div>

                    {/* Packs Produced */}
                    <div className="space-y-2">
                        <Label htmlFor="packs_produced">Packs Produced</Label>
                        <Input
                            id="packs_produced"
                            name="packs_produced"
                            type="number"
                            min={1}
                            value={packsProduced || ''}
                            onChange={(e) =>
                                setPacksProduced(Number(e.target.value))
                            }
                            required
                        />
                        {errors.packs_produced && (
                            <p className="text-sm text-red-500">
                                {errors.packs_produced}
                            </p>
                        )}
                    </div>

                    {/* Calculations Card */}
                    {wholeBirdsUsed > 0 && packsProduced > 0 && (
                        <div className="rounded-lg border bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-900">
                            <h4 className="mb-2 text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                Yield Summary
                            </h4>
                            <div className="grid grid-cols-3 gap-2 text-center">
                                <div>
                                    <p className="text-lg font-bold text-gray-900 dark:text-gray-100">
                                        {calculations.totalWeight} kg
                                    </p>
                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                        Total Weight
                                    </p>
                                </div>
                                <div>
                                    <p className="text-lg font-bold text-gray-900 dark:text-gray-100">
                                        {calculations.avgYieldPerBird} kg
                                    </p>
                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                        Per Bird
                                    </p>
                                </div>
                                <div>
                                    <p
                                        className={`text-lg font-bold ${
                                            Number(
                                                calculations.yieldEfficiency,
                                            ) >= 90
                                                ? 'text-green-600 dark:text-green-400'
                                                : Number(
                                                        calculations.yieldEfficiency,
                                                    ) >= 70
                                                  ? 'text-yellow-600 dark:text-yellow-400'
                                                  : 'text-red-600 dark:text-red-400'
                                        }`}
                                    >
                                        {calculations.yieldEfficiency}%
                                    </p>
                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                        Efficiency
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Notes */}
                    <div className="space-y-2">
                        <Label htmlFor="notes">Notes (optional)</Label>
                        <Textarea
                            id="notes"
                            name="notes"
                            rows={2}
                            placeholder="Any observations about quality, issues, etc."
                        />
                        {errors.notes && (
                            <p className="text-sm text-red-500">
                                {errors.notes}
                            </p>
                        )}
                    </div>

                    {/* Submit */}
                    <Button
                        type="submit"
                        className="w-full bg-green-600 hover:bg-green-700"
                        disabled={
                            processing ||
                            wholeBirdsUsed > availableBirds ||
                            wholeBirdsUsed < 1
                        }
                    >
                        {processing ? 'Recording...' : 'Record Portioning'}
                    </Button>
                </>
            )}
        </Form>
    );
}
