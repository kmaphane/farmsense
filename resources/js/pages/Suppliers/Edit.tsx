import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
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
import AppLayout from '@/layouts/app-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

interface Supplier {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    category: string;
    performance_rating: number | null;
    current_price_per_unit: number | null;
    notes: string | null;
    is_active: boolean;
}

interface SupplierCategory {
    value: string;
    label: string;
}

interface Props {
    supplier: Supplier;
    supplierCategories: SupplierCategory[];
}

export default function SuppliersEdit({ supplier, supplierCategories }: Props) {
    const { data, setData, patch, processing, errors } = useForm({
        name: supplier.name,
        email: supplier.email || '',
        phone: supplier.phone || '',
        category: supplier.category,
        performance_rating: supplier.performance_rating || '',
        current_price_per_unit: supplier.current_price_per_unit || '',
        notes: supplier.notes || '',
        is_active: supplier.is_active,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        patch(route('suppliers.update', supplier.id));
    };

    return (
        <AppLayout>
            <Head title={`Edit Supplier - ${supplier.name}`} />

            <div className="mx-auto max-w-3xl space-y-6 p-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={route('suppliers.show', supplier.id)}>
                            <ArrowLeft className="h-5 w-5" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            Edit Supplier
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Update supplier information
                        </p>
                    </div>
                </div>

                {/* Form */}
                <form onSubmit={handleSubmit}>
                    <Card>
                        <CardHeader>
                            <CardTitle>Supplier Information</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            {/* Basic Information */}
                            <div className="space-y-4">
                                <div className="text-sm font-medium">
                                    Basic Information
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="name">
                                        Name{' '}
                                        <span className="text-destructive">
                                            *
                                        </span>
                                    </Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) =>
                                            setData('name', e.target.value)
                                        }
                                        placeholder="Supplier name"
                                        className={
                                            errors.name
                                                ? 'border-destructive'
                                                : ''
                                        }
                                    />
                                    {errors.name && (
                                        <p className="text-sm text-destructive">
                                            {errors.name}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="category">
                                        Category{' '}
                                        <span className="text-destructive">
                                            *
                                        </span>
                                    </Label>
                                    <Select
                                        value={data.category}
                                        onValueChange={(value) =>
                                            setData('category', value)
                                        }
                                    >
                                        <SelectTrigger
                                            id="category"
                                            className={
                                                errors.category
                                                    ? 'border-destructive'
                                                    : ''
                                            }
                                        >
                                            <SelectValue placeholder="Select category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {supplierCategories.map(
                                                (category) => (
                                                    <SelectItem
                                                        key={category.value}
                                                        value={category.value}
                                                    >
                                                        {category.label}
                                                    </SelectItem>
                                                ),
                                            )}
                                        </SelectContent>
                                    </Select>
                                    {errors.category && (
                                        <p className="text-sm text-destructive">
                                            {errors.category}
                                        </p>
                                    )}
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_active"
                                        checked={data.is_active}
                                        onCheckedChange={(checked) =>
                                            setData(
                                                'is_active',
                                                checked as boolean,
                                            )
                                        }
                                    />
                                    <Label
                                        htmlFor="is_active"
                                        className="cursor-pointer"
                                    >
                                        Active supplier
                                    </Label>
                                </div>
                            </div>

                            {/* Contact Information */}
                            <div className="space-y-4 border-t pt-4">
                                <div className="text-sm font-medium">
                                    Contact Information
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        onChange={(e) =>
                                            setData('email', e.target.value)
                                        }
                                        placeholder="supplier@example.com"
                                        className={
                                            errors.email
                                                ? 'border-destructive'
                                                : ''
                                        }
                                    />
                                    {errors.email && (
                                        <p className="text-sm text-destructive">
                                            {errors.email}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="phone">Phone</Label>
                                    <Input
                                        id="phone"
                                        value={data.phone}
                                        onChange={(e) =>
                                            setData('phone', e.target.value)
                                        }
                                        placeholder="+267 71 234 567"
                                        className={
                                            errors.phone
                                                ? 'border-destructive'
                                                : ''
                                        }
                                    />
                                    {errors.phone && (
                                        <p className="text-sm text-destructive">
                                            {errors.phone}
                                        </p>
                                    )}
                                </div>
                            </div>

                            {/* Performance & Pricing */}
                            <div className="space-y-4 border-t pt-4">
                                <div className="text-sm font-medium">
                                    Performance & Pricing
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="performance_rating">
                                        Performance Rating (1-5)
                                    </Label>
                                    <Input
                                        id="performance_rating"
                                        type="number"
                                        step="0.1"
                                        min="1"
                                        max="5"
                                        value={data.performance_rating}
                                        onChange={(e) =>
                                            setData(
                                                'performance_rating',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="3.5"
                                        className={
                                            errors.performance_rating
                                                ? 'border-destructive'
                                                : ''
                                        }
                                    />
                                    {errors.performance_rating && (
                                        <p className="text-sm text-destructive">
                                            {errors.performance_rating}
                                        </p>
                                    )}
                                    <p className="text-xs text-muted-foreground">
                                        Rate from 1 (poor) to 5 (excellent)
                                    </p>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="current_price_per_unit">
                                        Current Price per Unit (BWP)
                                    </Label>
                                    <Input
                                        id="current_price_per_unit"
                                        type="number"
                                        step="0.01"
                                        value={data.current_price_per_unit}
                                        onChange={(e) =>
                                            setData(
                                                'current_price_per_unit',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="0.00"
                                        className={
                                            errors.current_price_per_unit
                                                ? 'border-destructive'
                                                : ''
                                        }
                                    />
                                    {errors.current_price_per_unit && (
                                        <p className="text-sm text-destructive">
                                            {errors.current_price_per_unit}
                                        </p>
                                    )}
                                </div>
                            </div>

                            {/* Notes */}
                            <div className="space-y-4 border-t pt-4">
                                <div className="text-sm font-medium">
                                    Additional Notes
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="notes">Notes</Label>
                                    <Textarea
                                        id="notes"
                                        value={data.notes}
                                        onChange={(e) =>
                                            setData('notes', e.target.value)
                                        }
                                        placeholder="Any additional notes about this supplier..."
                                        rows={4}
                                        className={
                                            errors.notes
                                                ? 'border-destructive'
                                                : ''
                                        }
                                    />
                                    {errors.notes && (
                                        <p className="text-sm text-destructive">
                                            {errors.notes}
                                        </p>
                                    )}
                                </div>
                            </div>

                            {/* Actions */}
                            <div className="flex gap-3 border-t pt-4">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Saving...' : 'Save Changes'}
                                </Button>
                                <Button type="button" variant="outline" asChild>
                                    <Link
                                        href={route(
                                            'suppliers.show',
                                            supplier.id,
                                        )}
                                    >
                                        Cancel
                                    </Link>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </form>
            </div>
        </AppLayout>
    );
}

SuppliersEdit.layout = (page: React.ReactNode) => page;
