import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Bird, Calendar, DollarSign } from 'lucide-react';
import { Link } from '@inertiajs/react';

interface PlannedBatch {
    id: number;
    name: string;
    batch_number: string;
    start_date: string;
    days_until_start: number;
    initial_quantity: number;
    supplier_name: string | null;
    estimated_feed_cost: number;
    status_color: 'red' | 'yellow' | 'green';
}

interface Props {
    batches: PlannedBatch[];
}

function formatCurrency(thebe: number): string {
    return `P${(thebe / 100).toLocaleString('en-BW', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

function getStatusBadge(batch: PlannedBatch) {
    if (batch.days_until_start < 0) {
        return (
            <Badge variant="destructive">
                Overdue ({Math.abs(batch.days_until_start)} days)
            </Badge>
        );
    }

    if (batch.days_until_start <= 7) {
        return (
            <Badge className="bg-yellow-500 text-white hover:bg-yellow-600">
                Starting in {batch.days_until_start} days
            </Badge>
        );
    }

    return (
        <Badge variant="outline" className="text-green-600">
            {batch.days_until_start} days away
        </Badge>
    );
}

export function PlannedBatchTimelineWidget({ batches }: Props) {
    if (batches.length === 0) {
        return (
            <Card>
                <CardHeader>
                    <CardTitle>Planned Batches</CardTitle>
                    <CardDescription>
                        Upcoming batch schedule & budget
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="flex flex-col items-center justify-center py-8">
                        <Bird className="h-12 w-12 text-gray-400" />
                        <p className="mt-4 text-sm font-medium text-gray-600 dark:text-gray-400">
                            No batches planned
                        </p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Create a planned batch to see it here
                        </p>
                    </div>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card>
            <CardHeader>
                <div className="flex items-center justify-between">
                    <div>
                        <CardTitle>Planned Batches</CardTitle>
                        <CardDescription>
                            Next {batches.length} batch
                            {batches.length !== 1 ? 'es' : ''} & feed budget
                        </CardDescription>
                    </div>
                    <Link
                        href="/batches/history"
                        className="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    >
                        View All
                    </Link>
                </div>
            </CardHeader>
            <CardContent>
                <div className="space-y-4">
                    {batches.map((batch) => (
                        <div
                            key={batch.id}
                            className="rounded-lg border p-4 hover:bg-muted/50"
                        >
                            <div className="flex items-start justify-between">
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2">
                                        <h4 className="text-sm font-semibold">
                                            {batch.name}
                                        </h4>
                                        {getStatusBadge(batch)}
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        {batch.batch_number}
                                    </p>
                                </div>
                            </div>

                            <div className="mt-3 grid grid-cols-2 gap-3 text-sm">
                                <div className="flex items-center gap-2">
                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <p className="text-xs text-muted-foreground">
                                            Start Date
                                        </p>
                                        <p className="font-medium">
                                            {batch.start_date}
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-center gap-2">
                                    <Bird className="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <p className="text-xs text-muted-foreground">
                                            Chicks
                                        </p>
                                        <p className="font-medium">
                                            {batch.initial_quantity.toLocaleString()}
                                        </p>
                                    </div>
                                </div>

                                {batch.supplier_name && (
                                    <div className="col-span-2">
                                        <p className="text-xs text-muted-foreground">
                                            Supplier
                                        </p>
                                        <p className="font-medium">
                                            {batch.supplier_name}
                                        </p>
                                    </div>
                                )}

                                <div className="col-span-2 mt-2 flex items-center gap-2 rounded-md bg-blue-50 p-2 dark:bg-blue-950">
                                    <DollarSign className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    <div>
                                        <p className="text-xs text-blue-600 dark:text-blue-400">
                                            Est. Feed Budget
                                        </p>
                                        <p className="text-sm font-bold text-blue-700 dark:text-blue-300">
                                            {formatCurrency(
                                                batch.estimated_feed_cost,
                                            )}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
