import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { FieldLayout } from '@/layouts/app/field-layout';
import { Head } from '@inertiajs/react';
import {
    AlertTriangle,
    Bird,
    Calendar,
    CheckCircle2,
    Package,
    Scissors,
    User,
} from 'lucide-react';

interface SlaughterRecord {
    id: number;
    slaughter_date: string;
    total_birds_processed: number;
    notes: string | null;
    recorded_by: string | null;
    created_at: string;
}

interface BatchSource {
    id: number;
    batch_name: string;
    batch_number: string;
    expected_quantity: number;
    actual_quantity: number;
    discrepancy_reason: string | null;
    discrepancy_notes: string | null;
    has_discrepancy: boolean;
}

interface ProductYield {
    id: number;
    product_name: string;
    estimated_quantity: number;
    actual_quantity: number;
    household_consumed: number;
}

interface Props {
    record: SlaughterRecord;
    batchSources: BatchSource[];
    yields: ProductYield[];
}

export default function Show({ record, batchSources, yields }: Props) {
    const totalDiscrepancy = batchSources.reduce(
        (sum, s) => sum + (s.expected_quantity - s.actual_quantity),
        0,
    );

    const totalHouseholdConsumed = yields.reduce(
        (sum, y) => sum + y.household_consumed,
        0,
    );

    return (
        <FieldLayout
            title="Slaughter Record"
            backHref="/batches"
            backLabel="Back"
        >
            <Head title={`Slaughter #${record.id}`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-start justify-between">
                    <div className="flex items-center gap-3">
                        <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                            <Scissors className="h-6 w-6" />
                        </div>
                        <div>
                            <h1 className="text-xl font-bold text-gray-900 dark:text-gray-100">
                                Slaughter #{record.id}
                            </h1>
                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                {new Date(
                                    record.slaughter_date,
                                ).toLocaleDateString('en-ZA', {
                                    weekday: 'long',
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                })}
                            </p>
                        </div>
                    </div>
                    <Badge variant="secondary" className="text-lg">
                        <Bird className="mr-1 h-4 w-4" />
                        {record.total_birds_processed}
                    </Badge>
                </div>

                {/* Summary Stats */}
                <div className="grid gap-4 sm:grid-cols-3">
                    <Card>
                        <CardContent className="pt-4">
                            <div className="flex items-center gap-3">
                                <Bird className="h-8 w-8 text-green-600" />
                                <div>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        {record.total_birds_processed}
                                    </p>
                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                        Birds Processed
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-4">
                            <div className="flex items-center gap-3">
                                {totalDiscrepancy > 0 ? (
                                    <AlertTriangle className="h-8 w-8 text-yellow-600" />
                                ) : (
                                    <CheckCircle2 className="h-8 w-8 text-green-600" />
                                )}
                                <div>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        {totalDiscrepancy}
                                    </p>
                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                        Total Discrepancy
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="pt-4">
                            <div className="flex items-center gap-3">
                                <Package className="h-8 w-8 text-purple-600" />
                                <div>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        {totalHouseholdConsumed}
                                    </p>
                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                        Household Use
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Batch Sources */}
                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">
                            Batch Sources
                        </CardTitle>
                        <CardDescription>
                            Birds processed from each batch
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-lg border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Batch</TableHead>
                                        <TableHead className="text-center">
                                            Expected
                                        </TableHead>
                                        <TableHead className="text-center">
                                            Actual
                                        </TableHead>
                                        <TableHead>Discrepancy</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {batchSources.map((source) => (
                                        <TableRow key={source.id}>
                                            <TableCell>
                                                <div>
                                                    <p className="font-medium">
                                                        {source.batch_name}
                                                    </p>
                                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                                        {source.batch_number}
                                                    </p>
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-center">
                                                {source.expected_quantity}
                                            </TableCell>
                                            <TableCell className="text-center">
                                                {source.actual_quantity}
                                            </TableCell>
                                            <TableCell>
                                                {source.has_discrepancy ? (
                                                    <div className="space-y-1">
                                                        <Badge
                                                            variant="destructive"
                                                            className="text-xs"
                                                        >
                                                            -
                                                            {source.expected_quantity -
                                                                source.actual_quantity}
                                                        </Badge>
                                                        {source.discrepancy_reason && (
                                                            <p className="text-xs text-gray-500 dark:text-gray-400">
                                                                {
                                                                    source.discrepancy_reason
                                                                }
                                                            </p>
                                                        )}
                                                        {source.discrepancy_notes && (
                                                            <p className="text-xs text-gray-400 italic dark:text-gray-500">
                                                                {
                                                                    source.discrepancy_notes
                                                                }
                                                            </p>
                                                        )}
                                                    </div>
                                                ) : (
                                                    <Badge
                                                        variant="outline"
                                                        className="text-xs"
                                                    >
                                                        <CheckCircle2 className="mr-1 h-3 w-3 text-green-600" />
                                                        None
                                                    </Badge>
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>

                {/* Product Yields */}
                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">
                            Product Yields
                        </CardTitle>
                        <CardDescription>
                            Products added to inventory
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-lg border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Product</TableHead>
                                        <TableHead className="text-center">
                                            Estimated
                                        </TableHead>
                                        <TableHead className="text-center">
                                            Actual
                                        </TableHead>
                                        <TableHead className="text-center">
                                            Household
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {yields.map((y) => (
                                        <TableRow key={y.id}>
                                            <TableCell className="font-medium">
                                                {y.product_name}
                                            </TableCell>
                                            <TableCell className="text-center">
                                                {y.estimated_quantity}
                                            </TableCell>
                                            <TableCell className="text-center font-medium">
                                                {y.actual_quantity}
                                            </TableCell>
                                            <TableCell className="text-center">
                                                {y.household_consumed > 0 ? (
                                                    <Badge
                                                        variant="secondary"
                                                        className="text-xs"
                                                    >
                                                        {y.household_consumed}
                                                    </Badge>
                                                ) : (
                                                    <span className="text-gray-400">
                                                        -
                                                    </span>
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>

                {/* Notes */}
                {record.notes && (
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-base">Notes</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm text-gray-700 dark:text-gray-300">
                                {record.notes}
                            </p>
                        </CardContent>
                    </Card>
                )}

                {/* Meta Info */}
                <div className="flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                    {record.recorded_by && (
                        <span className="flex items-center gap-1">
                            <User className="h-3 w-3" />
                            Recorded by {record.recorded_by}
                        </span>
                    )}
                    <span className="flex items-center gap-1">
                        <Calendar className="h-3 w-3" />
                        {new Date(record.created_at).toLocaleString('en-ZA')}
                    </span>
                </div>
            </div>
        </FieldLayout>
    );
}
