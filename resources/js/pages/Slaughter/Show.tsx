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
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import {
    AlertCircle,
    ArrowLeft,
    Bird,
    Package,
    Scale,
    User,
} from 'lucide-react';

interface SlaughterRecord {
    id: number;
    slaughter_date: string;
    total_birds_slaughtered: number;
    total_live_weight_kg: number | null;
    total_dressed_weight_kg: number | null;
    household_consumption_notes: string | null;
    notes: string | null;
    recorded_by: string;
}

interface BatchSource {
    batch_name: string;
    expected_quantity: number;
    actual_quantity: number;
    discrepancy_reason: string | null;
    discrepancy_notes: string | null;
    has_discrepancy: boolean;
}

interface Yield {
    product_name: string;
    estimated_quantity: number;
    actual_quantity: number;
    household_consumed: number;
}

interface Props {
    slaughterRecord: SlaughterRecord;
    batchSources: BatchSource[];
    yields: Yield[];
}

export default function SlaughterShow({
    slaughterRecord,
    batchSources,
    yields,
}: Props) {
    const hasDiscrepancies = batchSources.some(
        (source) => source.has_discrepancy,
    );
    const dressedPercentage =
        slaughterRecord.total_live_weight_kg &&
        slaughterRecord.total_dressed_weight_kg
            ? (
                  (slaughterRecord.total_dressed_weight_kg /
                      slaughterRecord.total_live_weight_kg) *
                  100
              ).toFixed(1)
            : null;

    return (
        <AppLayout>
            <Head
                title={`Slaughter Record - ${slaughterRecord.slaughter_date}`}
            />

            <div className="mx-auto max-w-7xl space-y-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={route('slaughter.index')}>
                                <ArrowLeft className="h-5 w-5" />
                            </Link>
                        </Button>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">
                                Slaughter Record
                            </h1>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {new Date(
                                    slaughterRecord.slaughter_date,
                                ).toLocaleDateString('en-US', {
                                    weekday: 'long',
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                })}
                            </p>
                        </div>
                    </div>
                    {hasDiscrepancies && (
                        <Badge variant="destructive" className="gap-2">
                            <AlertCircle className="h-4 w-4" />
                            Contains Discrepancies
                        </Badge>
                    )}
                </div>

                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Birds Slaughtered
                            </CardTitle>
                            <Bird className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {slaughterRecord.total_birds_slaughtered.toLocaleString()}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Live Weight
                            </CardTitle>
                            <Scale className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {slaughterRecord.total_live_weight_kg
                                    ? `${slaughterRecord.total_live_weight_kg} kg`
                                    : 'N/A'}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Dressed Weight
                            </CardTitle>
                            <Package className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {slaughterRecord.total_dressed_weight_kg
                                    ? `${slaughterRecord.total_dressed_weight_kg} kg`
                                    : 'N/A'}
                            </div>
                            {dressedPercentage && (
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {dressedPercentage}% yield
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Recorded By
                            </CardTitle>
                            <User className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-base font-medium">
                                {slaughterRecord.recorded_by}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Batch Sources */}
                <Card>
                    <CardHeader>
                        <CardTitle>Batch Sources</CardTitle>
                        <CardDescription>
                            Birds taken from each batch
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Batch</TableHead>
                                    <TableHead className="text-right">
                                        Expected
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Actual
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Difference
                                    </TableHead>
                                    <TableHead>Notes</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {batchSources.map((source, index) => (
                                    <TableRow key={index}>
                                        <TableCell className="font-medium">
                                            {source.batch_name}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            {source.expected_quantity}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            {source.actual_quantity}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            {source.has_discrepancy ? (
                                                <span className="font-medium text-destructive">
                                                    -
                                                    {source.expected_quantity -
                                                        source.actual_quantity}
                                                </span>
                                            ) : (
                                                <span className="text-muted-foreground">
                                                    —
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {source.has_discrepancy ? (
                                                <div className="space-y-1">
                                                    <Badge
                                                        variant="destructive"
                                                        className="text-xs"
                                                    >
                                                        {source.discrepancy_reason?.replace(
                                                            /_/g,
                                                            ' ',
                                                        )}
                                                    </Badge>
                                                    {source.discrepancy_notes && (
                                                        <p className="text-xs text-muted-foreground">
                                                            {
                                                                source.discrepancy_notes
                                                            }
                                                        </p>
                                                    )}
                                                </div>
                                            ) : (
                                                <span className="text-xs text-muted-foreground">
                                                    No discrepancy
                                                </span>
                                            )}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                {/* Product Yields */}
                <Card>
                    <CardHeader>
                        <CardTitle>Product Yields</CardTitle>
                        <CardDescription>
                            Products generated from this slaughter
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Product</TableHead>
                                    <TableHead className="text-right">
                                        Estimated
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Actual
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Household
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Difference
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {yields.map((yieldItem, index) => {
                                    const difference =
                                        yieldItem.estimated_quantity -
                                        yieldItem.actual_quantity -
                                        yieldItem.household_consumed;
                                    return (
                                        <TableRow key={index}>
                                            <TableCell className="font-medium">
                                                {yieldItem.product_name}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                {yieldItem.estimated_quantity}
                                            </TableCell>
                                            <TableCell className="text-right font-medium">
                                                {yieldItem.actual_quantity}
                                            </TableCell>
                                            <TableCell className="text-right text-muted-foreground">
                                                {yieldItem.household_consumed}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                {difference !== 0 ? (
                                                    <span
                                                        className={
                                                            difference > 0
                                                                ? 'text-muted-foreground'
                                                                : 'text-destructive'
                                                        }
                                                    >
                                                        {difference > 0
                                                            ? '+'
                                                            : ''}
                                                        {difference}
                                                    </span>
                                                ) : (
                                                    <span className="text-muted-foreground">
                                                        —
                                                    </span>
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    );
                                })}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                {/* Additional Notes */}
                {(slaughterRecord.household_consumption_notes ||
                    slaughterRecord.notes) && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Additional Notes</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {slaughterRecord.household_consumption_notes && (
                                <div>
                                    <h4 className="mb-1 text-sm font-medium">
                                        Household Consumption
                                    </h4>
                                    <p className="text-sm text-muted-foreground">
                                        {
                                            slaughterRecord.household_consumption_notes
                                        }
                                    </p>
                                </div>
                            )}
                            {slaughterRecord.notes && (
                                <div>
                                    <h4 className="mb-1 text-sm font-medium">
                                        General Notes
                                    </h4>
                                    <p className="text-sm text-muted-foreground">
                                        {slaughterRecord.notes}
                                    </p>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}

SlaughterShow.layout = (page: React.ReactNode) => page;
