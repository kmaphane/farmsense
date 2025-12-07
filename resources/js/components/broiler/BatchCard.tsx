import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Link } from '@inertiajs/react';
import { show } from '@/actions/App/Http/Controllers/Batches/BatchController';
import { Activity, AlertTriangle, Bird, ChevronRight, TrendingUp } from 'lucide-react';

export interface BatchCardData {
    id: number;
    name: string;
    age_in_days: number;
    current_bird_count: number;
    status: string;
    statusLabel: string;
    statusColor?: string;
    fcr: number | null;
    mortality_rate: number;
}

interface BatchCardProps {
    batch: BatchCardData;
    showLink?: boolean;
}

function getStatusVariant(status: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'active':
            return 'default';
        case 'harvesting':
            return 'secondary';
        case 'completed':
            return 'outline';
        default:
            return 'outline';
    }
}

export function BatchCard({ batch, showLink = true }: BatchCardProps) {
    const isAlert = batch.mortality_rate > 5;

    const content = (
        <Card className={showLink ? 'transition-shadow hover:shadow-md' : ''}>
            <CardHeader className="pb-2">
                <div className="flex items-start justify-between">
                    <div className="space-y-1">
                        <CardTitle className="text-lg">{batch.name}</CardTitle>
                        <CardDescription>Day {batch.age_in_days}</CardDescription>
                    </div>
                    <Badge variant={getStatusVariant(batch.status)}>
                        {batch.statusLabel}
                    </Badge>
                </div>
            </CardHeader>
            <CardContent>
                <div className="grid grid-cols-3 gap-4 text-sm">
                    <div className="flex items-center gap-2">
                        <Bird className="h-4 w-4 text-gray-500" />
                        <div>
                            <p className="text-xs text-gray-500 dark:text-gray-400">Birds</p>
                            <p className="font-medium">{batch.current_bird_count.toLocaleString()}</p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <TrendingUp className="h-4 w-4 text-gray-500" />
                        <div>
                            <p className="text-xs text-gray-500 dark:text-gray-400">FCR</p>
                            <p className="font-medium">{batch.fcr?.toFixed(2) ?? '-'}</p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        {isAlert ? (
                            <AlertTriangle className="h-4 w-4 text-red-500" />
                        ) : (
                            <Activity className="h-4 w-4 text-gray-500" />
                        )}
                        <div>
                            <p className="text-xs text-gray-500 dark:text-gray-400">Mortality</p>
                            <p className={`font-medium ${isAlert ? 'text-red-600' : ''}`}>
                                {batch.mortality_rate.toFixed(1)}%
                            </p>
                        </div>
                    </div>
                </div>
                {showLink && (
                    <div className="mt-4 flex items-center justify-end text-sm text-gray-500">
                        <span>View details</span>
                        <ChevronRight className="h-4 w-4" />
                    </div>
                )}
            </CardContent>
        </Card>
    );

    if (showLink) {
        return (
            <Link href={show.url(batch.id)} className="block">
                {content}
            </Link>
        );
    }

    return content;
}
