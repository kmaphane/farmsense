import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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

interface Customer {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    type: string;
    credit_limit: number | null;
    payment_terms: string | null;
    notes: string | null;
}

interface CustomerType {
    value: string;
    label: string;
}

interface Props {
    customer: Customer;
    customerTypes: CustomerType[];
}

export default function CustomersEdit({ customer, customerTypes }: Props) {
    const { data, setData, patch, processing, errors } = useForm({
        name: customer.name,
        email: customer.email || '',
        phone: customer.phone || '',
        type: customer.type,
        credit_limit: customer.credit_limit || '',
        payment_terms: customer.payment_terms || '',
        notes: customer.notes || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        patch(route('customers.update', customer.id));
    };

    return (
        <AppLayout>
            <Head title={`Edit Customer - ${customer.name}`} />

            <div className="mx-auto max-w-3xl space-y-6 p-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={route('customers.show', customer.id)}>
                            <ArrowLeft className="h-5 w-5" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            Edit Customer
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Update customer information
                        </p>
                    </div>
                </div>

                {/* Form */}
                <form onSubmit={handleSubmit}>
                    <Card>
                        <CardHeader>
                            <CardTitle>Customer Information</CardTitle>
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
                                        placeholder="Customer name"
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
                                    <Label htmlFor="type">
                                        Customer Type{' '}
                                        <span className="text-destructive">
                                            *
                                        </span>
                                    </Label>
                                    <Select
                                        value={data.type}
                                        onValueChange={(value) =>
                                            setData('type', value)
                                        }
                                    >
                                        <SelectTrigger
                                            id="type"
                                            className={
                                                errors.type
                                                    ? 'border-destructive'
                                                    : ''
                                            }
                                        >
                                            <SelectValue placeholder="Select customer type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {customerTypes.map((type) => (
                                                <SelectItem
                                                    key={type.value}
                                                    value={type.value}
                                                >
                                                    {type.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.type && (
                                        <p className="text-sm text-destructive">
                                            {errors.type}
                                        </p>
                                    )}
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
                                        placeholder="customer@example.com"
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

                            {/* Financial Information */}
                            <div className="space-y-4 border-t pt-4">
                                <div className="text-sm font-medium">
                                    Financial Information
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="credit_limit">
                                        Credit Limit (BWP)
                                    </Label>
                                    <Input
                                        id="credit_limit"
                                        type="number"
                                        step="0.01"
                                        value={data.credit_limit}
                                        onChange={(e) =>
                                            setData(
                                                'credit_limit',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="0.00"
                                        className={
                                            errors.credit_limit
                                                ? 'border-destructive'
                                                : ''
                                        }
                                    />
                                    {errors.credit_limit && (
                                        <p className="text-sm text-destructive">
                                            {errors.credit_limit}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="payment_terms">
                                        Payment Terms
                                    </Label>
                                    <Input
                                        id="payment_terms"
                                        value={data.payment_terms}
                                        onChange={(e) =>
                                            setData(
                                                'payment_terms',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="e.g., Net 30"
                                        className={
                                            errors.payment_terms
                                                ? 'border-destructive'
                                                : ''
                                        }
                                    />
                                    {errors.payment_terms && (
                                        <p className="text-sm text-destructive">
                                            {errors.payment_terms}
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
                                        placeholder="Any additional notes about this customer..."
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
                                            'customers.show',
                                            customer.id,
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

CustomersEdit.layout = (page: React.ReactNode) => page;
