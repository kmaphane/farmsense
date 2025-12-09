import { Button } from '@/components/ui/button';
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
import { router } from '@inertiajs/react';
import { Loader2 } from 'lucide-react';
import { useState } from 'react';

interface CustomerType {
    value: string;
    label: string;
}

interface CustomerFormProps {
    customerTypes: CustomerType[];
    compact?: boolean;
}

export function CustomerForm({
    customerTypes,
    compact = false,
}: CustomerFormProps) {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [phone, setPhone] = useState('');
    const [type, setType] = useState<string>('retail');
    const [creditLimit, setCreditLimit] = useState<number | ''>('');
    const [paymentTerms, setPaymentTerms] = useState('');
    const [notes, setNotes] = useState('');
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [processing, setProcessing] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        if (processing) return;

        // Clear previous errors
        setErrors({});

        // Client-side validation
        const newErrors: Record<string, string> = {};
        if (!name.trim()) newErrors.name = 'Name is required';
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            newErrors.email = 'Invalid email format';
        }
        if (!type) newErrors.type = 'Customer type is required';

        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            return;
        }

        setProcessing(true);

        const formData = {
            name: name.trim(),
            email: email.trim() || null,
            phone: phone.trim() || null,
            type,
            credit_limit: creditLimit || null,
            payment_terms: paymentTerms.trim() || null,
            notes: notes.trim() || null,
        };

        try {
            const response = await fetch('/api/customers/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || '',
                },
                body: JSON.stringify(formData),
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    setErrors(data.errors);
                } else {
                    setErrors({
                        form: data.message || 'Failed to create customer',
                    });
                }
                setProcessing(false);
                return;
            }

            // Success - redirect to customers list
            router.visit('/admin/customers');
        } catch (error) {
            console.error('Error creating customer:', error);
            setErrors({ form: 'An unexpected error occurred' });
            setProcessing(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4 p-4">
            {errors.form && (
                <div className="rounded-md bg-red-50 p-3 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400">
                    {errors.form}
                </div>
            )}

            {/* Name */}
            <div className="space-y-2">
                <Label htmlFor="name" className="text-xs">
                    Customer Name <span className="text-red-500">*</span>
                </Label>
                <Input
                    id="name"
                    type="text"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    placeholder="Enter customer name"
                    className={`h-9 text-sm ${errors.name ? 'border-red-500' : ''}`}
                    autoFocus
                />
                {errors.name && (
                    <p className="text-xs text-red-500">{errors.name}</p>
                )}
            </div>

            {/* Email */}
            <div className="space-y-2">
                <Label htmlFor="email" className="text-xs">
                    Email
                </Label>
                <Input
                    id="email"
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="customer@example.com"
                    className={`h-9 text-sm ${errors.email ? 'border-red-500' : ''}`}
                />
                {errors.email && (
                    <p className="text-xs text-red-500">{errors.email}</p>
                )}
            </div>

            {/* Phone */}
            <div className="space-y-2">
                <Label htmlFor="phone" className="text-xs">
                    Phone
                </Label>
                <Input
                    id="phone"
                    type="tel"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    placeholder="+267 1234 5678"
                    className={`h-9 text-sm ${errors.phone ? 'border-red-500' : ''}`}
                />
                {errors.phone && (
                    <p className="text-xs text-red-500">{errors.phone}</p>
                )}
            </div>

            {/* Customer Type */}
            <div className="space-y-2">
                <Label htmlFor="type" className="text-xs">
                    Customer Type <span className="text-red-500">*</span>
                </Label>
                <Select value={type} onValueChange={setType}>
                    <SelectTrigger
                        id="type"
                        className={`h-9 text-sm ${errors.type ? 'border-red-500' : ''}`}
                    >
                        <SelectValue placeholder="Select type" />
                    </SelectTrigger>
                    <SelectContent>
                        {customerTypes.map((ct) => (
                            <SelectItem key={ct.value} value={ct.value}>
                                {ct.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {errors.type && (
                    <p className="text-xs text-red-500">{errors.type}</p>
                )}
            </div>

            {/* Credit Limit */}
            <div className="space-y-2">
                <Label htmlFor="credit_limit" className="text-xs">
                    Credit Limit (BWP)
                </Label>
                <Input
                    id="credit_limit"
                    type="number"
                    min={0}
                    value={creditLimit}
                    onChange={(e) =>
                        setCreditLimit(
                            e.target.value ? parseFloat(e.target.value) : '',
                        )
                    }
                    placeholder="0.00"
                    className={`h-9 text-sm ${errors.credit_limit ? 'border-red-500' : ''}`}
                />
                {errors.credit_limit && (
                    <p className="text-xs text-red-500">
                        {errors.credit_limit}
                    </p>
                )}
                <p className="text-xs text-gray-500">
                    Maximum outstanding balance allowed
                </p>
            </div>

            {/* Payment Terms */}
            <div className="space-y-2">
                <Label htmlFor="payment_terms" className="text-xs">
                    Payment Terms
                </Label>
                <Input
                    id="payment_terms"
                    type="text"
                    value={paymentTerms}
                    onChange={(e) => setPaymentTerms(e.target.value)}
                    placeholder="e.g., Net 30, COD, 50% deposit"
                    className={`h-9 text-sm ${errors.payment_terms ? 'border-red-500' : ''}`}
                />
                {errors.payment_terms && (
                    <p className="text-xs text-red-500">
                        {errors.payment_terms}
                    </p>
                )}
            </div>

            {/* Notes */}
            <div className="space-y-2">
                <Label htmlFor="notes" className="text-xs">
                    Notes
                </Label>
                <Textarea
                    id="notes"
                    value={notes}
                    onChange={(e) => setNotes(e.target.value)}
                    placeholder="Additional information about the customer"
                    rows={3}
                    className={`text-sm ${errors.notes ? 'border-red-500' : ''}`}
                />
                {errors.notes && (
                    <p className="text-xs text-red-500">{errors.notes}</p>
                )}
            </div>

            {/* Submit Button */}
            <div className="flex justify-end gap-2 pt-2">
                <Button
                    type="submit"
                    disabled={processing}
                    className="w-full"
                    size={compact ? 'sm' : 'default'}
                >
                    {processing ? (
                        <>
                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            Creating...
                        </>
                    ) : (
                        'Create Customer'
                    )}
                </Button>
            </div>
        </form>
    );
}
