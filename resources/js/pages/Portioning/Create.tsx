import { PortioningForm } from '@/components/broiler/PortioningForm';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { FieldLayout } from '@/layouts/app/field-layout';
import { Head } from '@inertiajs/react';
import { Scissors } from 'lucide-react';

interface Props {
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
}

export default function Create({
    wholeChickenStock,
    chickenPiecesProduct,
    suggestedDate,
    defaultPackWeight,
}: Props) {
    return (
        <FieldLayout title="Portioning" backHref="/batches" backLabel="Back">
            <Head title="Record Portioning" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-3">
                    <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                        <Scissors className="h-6 w-6" />
                    </div>
                    <div>
                        <h1 className="text-xl font-bold text-gray-900 dark:text-gray-100">
                            Record Portioning
                        </h1>
                        <p className="text-sm text-gray-500 dark:text-gray-400">
                            Convert whole chickens into packaged pieces
                        </p>
                    </div>
                </div>

                {/* Form Card */}
                <Card>
                    <CardHeader>
                        <CardTitle>Portioning Details</CardTitle>
                        <CardDescription>
                            Enter the number of whole chickens used and packs
                            produced.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <PortioningForm
                            wholeChickenStock={wholeChickenStock}
                            chickenPiecesProduct={chickenPiecesProduct}
                            suggestedDate={suggestedDate}
                            defaultPackWeight={defaultPackWeight}
                        />
                    </CardContent>
                </Card>
            </div>
        </FieldLayout>
    );
}
