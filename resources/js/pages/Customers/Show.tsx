import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Calendar,
    CreditCard,
    DollarSign,
    Edit,
    FileText,
    Mail,
    Phone,
    User,
} from 'lucide-react';

interface Customer {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    type: string;
    type_label: string;
    credit_limit: number | null;
    payment_terms: string | null;
    notes: string | null;
    created_at: string;
}

interface Props {
    customer: Customer;
}

const typeConfig: Record<
    string,
    { variant: 'default' | 'secondary' | 'outline' }
> = {
    wholesale: { variant: 'default' },
    retail: { variant: 'secondary' },
    distributor: { variant: 'outline' },
};

export default function CustomersShow({ customer }: Props) {
    const config = typeConfig[customer.type] || { variant: 'default' };

    return (
        <AppLayout>
            <Head title={`Customer - ${customer.name}`} />

            <div className="mx-auto max-w-4xl space-y-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={route('customers.index')}>
                                <ArrowLeft className="h-5 w-5" />
                            </Link>
                        </Button>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">
                                {customer.name}
                            </h1>
                            <p className="mt-1 text-sm text-muted-foreground">
                                <Badge variant={config.variant}>
                                    {customer.type_label}
                                </Badge>
                            </p>
                        </div>
                    </div>
                    <Button asChild>
                        <Link href={route('customers.edit', customer.id)}>
                            <Edit className="mr-2 h-4 w-4" />
                            Edit
                        </Link>
                    </Button>
                </div>

                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Credit Limit
                            </CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            {customer.credit_limit ? (
                                <>
                                    <div className="text-2xl font-bold">
                                        P {customer.credit_limit.toFixed(2)}
                                    </div>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        available credit
                                    </p>
                                </>
                            ) : (
                                <div className="text-lg text-muted-foreground">
                                    Not set
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Payment Terms
                            </CardTitle>
                            <CreditCard className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            {customer.payment_terms ? (
                                <>
                                    <div className="text-2xl font-bold">
                                        {customer.payment_terms}
                                    </div>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        days
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

                {/* Customer Details */}
                <Card>
                    <CardHeader>
                        <CardTitle>Customer Information</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        {/* Basic Info */}
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                    <User className="h-4 w-4 text-muted-foreground" />
                                    Name
                                </div>
                                <div className="text-base">{customer.name}</div>
                            </div>

                            <div>
                                <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                    <Badge
                                        variant={config.variant}
                                        className="gap-1"
                                    >
                                        {customer.type_label}
                                    </Badge>
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Customer Type
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
                                        {customer.email || (
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
                                        {customer.phone || (
                                            <span className="text-muted-foreground">
                                                Not provided
                                            </span>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Financial Info */}
                        <div className="border-t pt-4">
                            <div className="mb-3 text-sm font-medium">
                                Financial Information
                            </div>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                        <DollarSign className="h-4 w-4 text-muted-foreground" />
                                        Credit Limit
                                    </div>
                                    <div className="text-base">
                                        {customer.credit_limit ? (
                                            `P ${customer.credit_limit.toFixed(2)}`
                                        ) : (
                                            <span className="text-muted-foreground">
                                                Not set
                                            </span>
                                        )}
                                    </div>
                                </div>

                                <div>
                                    <div className="mb-1 flex items-center gap-2 text-sm font-medium">
                                        <CreditCard className="h-4 w-4 text-muted-foreground" />
                                        Payment Terms
                                    </div>
                                    <div className="text-base">
                                        {customer.payment_terms || (
                                            <span className="text-muted-foreground">
                                                Not set
                                            </span>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Notes */}
                        {customer.notes && (
                            <div className="border-t pt-4">
                                <div className="mb-2 flex items-center gap-2 text-sm font-medium">
                                    <FileText className="h-4 w-4 text-muted-foreground" />
                                    Notes
                                </div>
                                <p className="text-sm whitespace-pre-wrap text-muted-foreground">
                                    {customer.notes}
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
                                {customer.created_at}
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

CustomersShow.layout = (page: React.ReactNode) => page;
