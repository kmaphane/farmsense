import AppLogo from '@/components/app-logo';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { FormEventHandler } from 'react';

export default function ForgotPassword({ status }: { status?: string }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/forgot-password');
    };

    return (
        <>
            <Head title="Forgot Password" />

            <div className="flex min-h-screen items-center justify-center bg-muted/40 p-4">
                <Card className="w-full max-w-md">
                    <CardHeader className="space-y-1">
                        <div className="mb-4 flex items-center justify-center">
                            <AppLogo />
                        </div>
                        <CardTitle className="text-center text-2xl font-bold">
                            Forgot password?
                        </CardTitle>
                        <CardDescription className="text-center">
                            No problem. Just let us know your email address and
                            we will email you a password reset link that will
                            allow you to choose a new one.
                        </CardDescription>
                    </CardHeader>

                    <CardContent>
                        {status && (
                            <div className="mb-4 rounded-md bg-green-50 p-3 text-sm font-medium text-green-600 dark:bg-green-900/20 dark:text-green-400">
                                {status}
                            </div>
                        )}

                        <form onSubmit={submit} className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value={data.email}
                                    autoComplete="username"
                                    autoFocus
                                    onChange={(e) =>
                                        setData('email', e.target.value)
                                    }
                                    placeholder="name@example.com"
                                    required
                                />
                                <InputError message={errors.email} />
                            </div>

                            <Button
                                type="submit"
                                className="w-full"
                                disabled={processing}
                            >
                                {processing
                                    ? 'Sending...'
                                    : 'Email Password Reset Link'}
                            </Button>

                            <div className="text-center">
                                <a
                                    href="/login"
                                    className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                                >
                                    <ArrowLeft className="h-4 w-4" />
                                    Back to login
                                </a>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

ForgotPassword.layout = (page: React.ReactNode) => page;
