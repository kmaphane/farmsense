import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { Calendar, Eye, Package, Scale } from 'lucide-react';

interface PortioningRecord {
    id: number;
    portioning_date: string;
    portioning_date_formatted: string;
    whole_birds_used: number;
    packs_produced: number;
    pack_weight_kg: number;
    total_weight: number;
    recorded_by: string;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    portioningRecords: PortioningRecord[];
    pagination: Pagination;
}

export default function PortioningIndex({
    portioningRecords,
    pagination,
}: Props) {
    const handlePageChange = (page: number) => {
        router.get(
            route('portioning.index'),
            { page },
            { preserveState: true },
        );
    };

    // Ensure portioningRecords is an array
    const recordList = Array.isArray(portioningRecords)
        ? portioningRecords
        : [];
    const totalBirdsUsed = recordList.reduce(
        (sum, r) => sum + r.whole_birds_used,
        0,
    );
    const totalPacksProduced = recordList.reduce(
        (sum, r) => sum + r.packs_produced,
        0,
    );
    const totalWeight = recordList.reduce((sum, r) => sum + r.total_weight, 0);

    return (
        <AppLayout>
            <Head title="Portioning Records" />

            <div className="mx-auto max-w-7xl space-y-6 p-6">
                {/* Header */}
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">
                        Portioning Records
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Track whole bird to pieces conversion
                    </p>
                </div>

                {/* Stats */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Whole Birds Used
                            </CardTitle>
                            <Package className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {totalBirdsUsed.toLocaleString()}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Packs Produced
                            </CardTitle>
                            <Package className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {totalPacksProduced.toLocaleString()}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total Weight
                            </CardTitle>
                            <Scale className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {totalWeight.toFixed(1)} kg
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Portioning Sessions</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {recordList.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <Package className="mb-4 h-12 w-12 text-muted-foreground/50" />
                                <h3 className="mb-2 text-lg font-semibold">
                                    No portioning records yet
                                </h3>
                                <p className="max-w-sm text-sm text-muted-foreground">
                                    Portioning records will appear here after
                                    you convert whole birds into pieces through
                                    the Quick Actions menu.
                                </p>
                            </div>
                        ) : (
                            <>
                                <div className="overflow-x-auto">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Date</TableHead>
                                                <TableHead className="text-right">
                                                    Whole Birds
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    Packs Produced
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    Pack Weight
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    Total Weight
                                                </TableHead>
                                                <TableHead>
                                                    Recorded By
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    Actions
                                                </TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {recordList.map((record) => (
                                                <TableRow
                                                    key={record.id}
                                                    className="cursor-pointer hover:bg-muted/50"
                                                >
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'portioning.show',
                                                                    record.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <div className="flex items-center gap-2">
                                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                                            <span className="font-medium">
                                                                {
                                                                    record.portioning_date_formatted
                                                                }
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell
                                                        className="text-right"
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'portioning.show',
                                                                    record.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <span className="font-medium">
                                                            {
                                                                record.whole_birds_used
                                                            }
                                                        </span>
                                                    </TableCell>
                                                    <TableCell
                                                        className="text-right"
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'portioning.show',
                                                                    record.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <span>
                                                            {
                                                                record.packs_produced
                                                            }
                                                        </span>
                                                    </TableCell>
                                                    <TableCell
                                                        className="text-right"
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'portioning.show',
                                                                    record.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <span className="text-muted-foreground">
                                                            {
                                                                record.pack_weight_kg
                                                            }{' '}
                                                            kg
                                                        </span>
                                                    </TableCell>
                                                    <TableCell
                                                        className="text-right"
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'portioning.show',
                                                                    record.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <span className="font-medium">
                                                            {record.total_weight.toFixed(
                                                                1,
                                                            )}{' '}
                                                            kg
                                                        </span>
                                                    </TableCell>
                                                    <TableCell
                                                        onClick={() =>
                                                            router.visit(
                                                                route(
                                                                    'portioning.show',
                                                                    record.id,
                                                                ),
                                                            )
                                                        }
                                                    >
                                                        <span className="text-sm text-muted-foreground">
                                                            {record.recorded_by}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() =>
                                                                router.visit(
                                                                    route(
                                                                        'portioning.show',
                                                                        record.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            <Eye className="mr-1 h-4 w-4" />
                                                            View
                                                        </Button>
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>

                                {/* Pagination */}
                                {pagination.last_page > 1 && (
                                    <div className="mt-4 flex items-center justify-between border-t pt-4">
                                        <div className="text-sm text-muted-foreground">
                                            Showing page{' '}
                                            {pagination.current_page} of{' '}
                                            {pagination.last_page}(
                                            {pagination.total} total records)
                                        </div>
                                        <div className="flex gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                disabled={
                                                    pagination.current_page ===
                                                    1
                                                }
                                                onClick={() =>
                                                    handlePageChange(
                                                        pagination.current_page -
                                                            1,
                                                    )
                                                }
                                            >
                                                Previous
                                            </Button>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                disabled={
                                                    pagination.current_page ===
                                                    pagination.last_page
                                                }
                                                onClick={() =>
                                                    handlePageChange(
                                                        pagination.current_page +
                                                            1,
                                                    )
                                                }
                                            >
                                                Next
                                            </Button>
                                        </div>
                                    </div>
                                )}
                            </>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

PortioningIndex.layout = (page: React.ReactNode) => page;
