<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Configure Fortify views.
     *
     * Use Inertia for frontend authentication.
     * Filament admin panel has separate auth at /admin/login
     */
    private function configureViews(): void
    {
        Fortify::loginView(function () {
            return inertia('auth/login', [
                'canResetPassword' => \Laravel\Fortify\Features::enabled(\Laravel\Fortify\Features::resetPasswords()),
                'status' => session('status'),
            ]);
        });

        Fortify::requestPasswordResetLinkView(function () {
            return inertia('auth/forgot-password', [
                'status' => session('status'),
            ]);
        });

        Fortify::resetPasswordView(function ($request) {
            return inertia('auth/reset-password', [
                'email' => $request->email,
                'token' => $request->route('token'),
            ]);
        });
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
