import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Calendar,
    FileText,
    Package,
    Scale,
    User,
} from 'lucide-react';

interface PortioningRecord {
    id: number;
    portioning_date: string;
    portioning_date_formatted: string;
    whole_birds_used: number;
    packs_produced: number;
    pack_weight_kg: number;
    total_weight: number;
    notes: string | null;
    recorded_by: string;
    created_at: string;
}

interface Props {
    portioningRecord: PortioningRecord;
}

export default function PortioningShow({ portioningRecord }: Props) {
    const yieldPercentage =
        portioningRecord.whole_birds_used > 0
            ? (
                  (portioningRecord.packs_produced /
                      portioningRecord.whole_birds_used) *
                  100
              ).toFixed(1)
            : 0;

    return (
        <AppLayout>
            <Head
                title={`Portioning Record - ${portioningRecord.portioning_date}`}
            />

            <div className="mx-auto max-w-4xl space-y-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={route('portioning.index')}>
                                <ArrowLeft className="h-5 w-5" />
                            </Link>
                        </Button>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">
                                Portioning Record
                            </h1>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {portioningRecord.portioning_date_formatted}
                            </p>
                        </div>
                    </div>
                </div>

                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Whole Birds Used
                            </CardTitle>
                            <Package className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {portioningRecord.whole_birds_used}
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
                                {portioningRecord.packs_produced}
                            </div>
                            <p className="mt-1 text-xs text-muted-foreground">
                                {yieldPercentage}% yield
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Pack Weight
                            </CardTitle>
                            <Scale className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {portioningRecord.pack_weight_kg} kg
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
                                {portioningRecord.total_weight.toFixed(1)} kg
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Details Card */}
                <Card>
                    <CardHeader>
                        <CardTitle>Portioning Details</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                    Portioning Date
                                </div>
                                <div className="text-base">
                                    {portioningRecord.portioning_date_formatted}
                                </div>
                            </div>

                            <div>
                                <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                    <User className="h-4 w-4 text-muted-foreground" />
                                    Recorded By
                                </div>
                                <div className="text-base">
                                    {portioningRecord.recorded_by}
                                </div>
                            </div>
                        </div>

                        <div className="border-t pt-4">
                            <div className="mb-2 text-sm font-medium">
                                Conversion Summary
                            </div>
                            <div className="grid gap-2 text-sm">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Input:
                                    </span>
                                    <span className="font-medium">
                                        {portioningRecord.whole_birds_used}{' '}
                                        whole birds
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Output:
                                    </span>
                                    <span className="font-medium">
                                        {portioningRecord.packs_produced} packs
                                        × {portioningRecord.pack_weight_kg} kg
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Total Output Weight:
                                    </span>
                                    <span className="font-medium">
                                        {portioningRecord.total_weight.toFixed(
                                            1,
                                        )}{' '}
                                        kg
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Avg per Bird:
                                    </span>
                                    <span className="font-medium">
                                        {(
                                            portioningRecord.packs_produced /
                                            portioningRecord.whole_birds_used
                                        ).toFixed(2)}{' '}
                                        packs
                                    </span>
                                </div>
                            </div>
                        </div>

                        {portioningRecord.notes && (
                            <div className="border-t pt-4">
                                <div className="mb-2 flex items-center gap-2 text-sm font-medium">
                                    <FileText className="h-4 w-4 text-muted-foreground" />
                                    Notes
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    {portioningRecord.notes}
                                </p>
                            </div>
                        )}

                        <div className="border-t pt-4 text-xs text-muted-foreground">
                            Recorded on {portioningRecord.created_at}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

PortioningShow.layout = (page: React.ReactNode) => page;
