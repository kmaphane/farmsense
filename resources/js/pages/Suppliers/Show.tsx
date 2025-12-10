import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Calendar,
    CheckCircle2,
    DollarSign,
    Edit,
    FileText,
    Mail,
    Package,
    Phone,
    Star,
    XCircle,
} from 'lucide-react';

interface Supplier {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    category: string;
    category_label: string;
    performance_rating: number | null;
    rating_stars: string;
    current_price_per_unit: number | null;
    notes: string | null;
    is_active: boolean;
    created_at: string;
}

interface Props {
    supplier: Supplier;
}

const categoryConfig: Record<
    string,
    { variant: 'default' | 'secondary' | 'outline' }
> = {
    feed: { variant: 'default' },
    chicks: { variant: 'secondary' },
    medication: { variant: 'outline' },
    equipment: { variant: 'outline' },
};

export default function SuppliersShow({ supplier }: Props) {
    const config = categoryConfig[supplier.category] || { variant: 'default' };

    const renderRating = (rating: number | null) => {
        if (!rating)
            return <span className="text-muted-foreground">Not rated</span>;

        return (
            <div className="flex items-center gap-2">
                <div className="flex items-center gap-1">
                    {Array.from({ length: 5 }, (_, i) => (
                        <Star
                            key={i}
                            className={`h-4 w-4 ${i < rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'}`}
                        />
                    ))}
                </div>
                <span className="text-sm font-medium">
                    {rating.toFixed(1)} / 5.0
                </span>
            </div>
        );
    };

    return (
        <AppLayout>
            <Head title={`Supplier - ${supplier.name}`} />

            <div className="mx-auto max-w-4xl space-y-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={route('suppliers.index')}>
                                <ArrowLeft className="h-5 w-5" />
                            </Link>
                        </Button>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">
                                {supplier.name}
                            </h1>
                            <p className="mt-1 text-sm text-muted-foreground">
                                <Badge variant={config.variant}>
                                    {supplier.category_label}
                                </Badge>
                            </p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        {supplier.is_active ? (
                            <Badge variant="default" className="gap-1">
                                <CheckCircle2 className="h-3 w-3" />
                                Active
                            </Badge>
                        ) : (
                            <Badge variant="secondary" className="gap-1">
                                <XCircle className="h-3 w-3" />
                                Inactive
                            </Badge>
                        )}
                        <Button asChild>
                            <Link href={route('suppliers.edit', supplier.id)}>
                                <Edit className="mr-2 h-4 w-4" />
                                Edit
                            </Link>
                        </Button>
                    </div>
                </div>

                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Performance Rating
                            </CardTitle>
                            <Star className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            {renderRating(supplier.performance_rating)}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Current Price per Unit
                            </CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            {supplier.current_price_per_unit ? (
                                <>
                                    <div className="text-2xl font-bold">
                                        P{' '}
                                        {supplier.current_price_per_unit.toFixed(
                                            2,
                                        )}
                                    </div>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        per unit
                                    </p>
                                </>
                            ) : (
                                <div className="text-lg text-muted-foreground">
                                    Not set
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Supplier Details */}
                <Card>
                    <CardHeader>
                        <CardTitle>Supplier Information</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        {/* Basic Info */}
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                    <Package className="h-4 w-4 text-muted-foreground" />
                                    Name
                                </div>
                                <div className="text-base">{supplier.name}</div>
                            </div>

                            <div>
                                <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                    <Badge
                                        variant={config.variant}
                                        className="gap-1"
                                    >
                                        {supplier.category_label}
                                    </Badge>
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Supplier Category
                                </div>
                            </div>
                        </div>

                        {/* Contact Info */}
                        <div className="border-t pt-4">
                            <div className="mb-3 text-sm font-medium">
                                Contact Information
                            </div>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                        <Mail className="h-4 w-4 text-muted-foreground" />
                                        Email
                                    </div>
                                    <div className="text-base">
                                        {supplier.email || (
                                            <span className="text-muted-foreground">
                                                Not provided
                                            </span>
                                        )}
                                    </div>
                                </div>

                                <div>
                                    <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                        <Phone className="h-4 w-4 text-muted-foreground" />
                                        Phone
                                    </div>
                                    <div className="text-base">
                                        {supplier.phone || (
                                            <span className="text-muted-foreground">
                                                Not provided
                                            </span>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Performance & Pricing */}
                        <div className="border-t pt-4">
                            <div className="mb-3 text-sm font-medium">
                                Performance & Pricing
                            </div>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                        <Star className="h-4 w-4 text-muted-foreground" />
                                        Performance Rating
                                    </div>
                                    <div className="text-base">
                                        {renderRating(
                                            supplier.performance_rating,
                                        )}
                                    </div>
                                </div>

                                <div>
                                    <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                        <DollarSign className="h-4 w-4 text-muted-foreground" />
                                        Current Price per Unit
                                    </div>
                                    <div className="text-base">
                                        {supplier.current_price_per_unit ? (
                                            `P ${supplier.current_price_per_unit.toFixed(2)}`
                                        ) : (
                                            <span className="text-muted-foreground">
                                                Not set
                                            </span>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Notes */}
                        {supplier.notes && (
                            <div className="border-t pt-4">
                                <div className="mb-2 flex items-center gap-2 text-sm font-medium">
                                    <FileText className="h-4 w-4 text-muted-foreground" />
                                    Notes
                                </div>
                                <p className="text-sm whitespace-pre-wrap text-muted-foreground">
                                    {supplier.notes}
                                </p>
                            </div>
                        )}

                        {/* Metadata */}
                        <div className="border-t pt-4">
                            <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                <Calendar className="h-4 w-4 text-muted-foreground" />
                                Created
                            </div>
                            <div className="text-sm text-muted-foreground">
                                {supplier.created_at}
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

SuppliersShow.layout = (page: React.ReactNode) => page;
