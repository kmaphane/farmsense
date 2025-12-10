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
import { FormEventHandler } from 'react';

export default function ResetPassword({
    token,
    email,
}: {
    token: string;
    email: string;
}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token: token,
        email: email,
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post('/reset-password', {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <>
            <Head title="Reset Password" />

            <div className="flex min-h-screen items-center justify-center bg-muted/40 p-4">
                <Card className="w-full max-w-md">
                    <CardHeader className="space-y-1">
                        <div className="mb-4 flex items-center justify-center">
                            <AppLogo />
                        </div>
                        <CardTitle className="text-center text-2xl font-bold">
                            Reset password
                        </CardTitle>
                        <CardDescription className="text-center">
                            Enter your new password below
                        </CardDescription>
                    </CardHeader>

                    <CardContent>
                        <form onSubmit={submit} className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value={data.email}
                                    autoComplete="username"
                                    onChange={(e) =>
                                        setData('email', e.target.value)
                                    }
                                    placeholder="name@example.com"
                                    required
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="password">Password</Label>
                                <Input
                                    id="password"
                                    type="password"
                                    name="password"
                                    value={data.password}
                                    autoComplete="new-password"
                                    autoFocus
                                    onChange={(e) =>
                                        setData('password', e.target.value)
                                    }
                                    placeholder="••••••••"
                                    required
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="password_confirmation">
                                    Confirm Password
                                </Label>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    value={data.password_confirmation}
                                    autoComplete="new-password"
                                    onChange={(e) =>
                                        setData(
                                            'password_confirmation',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="••••••••"
                                    required
                                />
                                <InputError
                                    message={errors.password_confirmation}
                                />
                            </div>

                            <Button
                                type="submit"
                                className="w-full"
                                disabled={processing}
                            >
                                {processing ? 'Resetting...' : 'Reset Password'}
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

ResetPassword.layout = (page: React.ReactNode) => page;
