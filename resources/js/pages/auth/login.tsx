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
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function Login({
    status,
    canResetPassword,
}: {
    status?: string;
    canResetPassword: boolean;
}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post('/login', {
            onFinish: () => reset('password'),
        });
    };

    return (
        <>
            <Head title="Log in" />

            <div className="flex min-h-screen items-center justify-center bg-muted/40 p-4">
                <Card className="w-full max-w-md">
                    <CardHeader className="space-y-1">
                        <div className="mb-4 flex items-center justify-center">
                            <AppLogo />
                        </div>
                        <CardTitle className="text-center text-2xl font-bold">
                            Welcome back
                        </CardTitle>
                        <CardDescription className="text-center">
                            Enter your credentials to access your account
                        </CardDescription>
                    </CardHeader>

                    <CardContent>
                        {status && (
                            <div className="mb-4 text-sm font-medium text-green-600 dark:text-green-400">
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

                            <div className="space-y-2">
                                <div className="flex items-center justify-between">
                                    <Label htmlFor="password">Password</Label>
                                    {canResetPassword && (
                                        <a
                                            href="/forgot-password"
                                            className="text-sm text-muted-foreground underline hover:text-foreground"
                                        >
                                            Forgot password?
                                        </a>
                                    )}
                                </div>
                                <Input
                                    id="password"
                                    type="password"
                                    name="password"
                                    value={data.password}
                                    autoComplete="current-password"
                                    onChange={(e) =>
                                        setData('password', e.target.value)
                                    }
                                    placeholder="••••••••"
                                    required
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="flex items-center space-x-2">
                                <Checkbox
                                    id="remember"
                                    checked={data.remember}
                                    onCheckedChange={(checked) =>
                                        setData('remember', checked === true)
                                    }
                                />
                                <Label
                                    htmlFor="remember"
                                    className="cursor-pointer text-sm font-normal"
                                >
                                    Remember me
                                </Label>
                            </div>

                            <Button
                                type="submit"
                                className="w-full"
                                disabled={processing}
                            >
                                {processing ? 'Signing in...' : 'Sign in'}
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

Login.layout = (page: React.ReactNode) => page;
