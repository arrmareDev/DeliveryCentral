<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        // El link de "recuperar contraseña" debe abrir el SPA de Vue
        // (no una vista Blade), así que sobreescribimos la URL que
        // arma la notificación por defecto de Laravel.
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $frontend = rtrim(config('app.frontend_url'), '/');

            return "{$frontend}/reset-password?token={$token}&email=" . urlencode($notifiable->getEmailForPasswordReset());
        });
    }
}
